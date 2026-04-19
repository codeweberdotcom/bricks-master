<?php

namespace Codeweber\S3Storage\Queue;

use Codeweber\S3Storage\DB\JobsTable;
use Codeweber\S3Storage\Logger;
use Codeweber\S3Storage\Tools\DeleteLocal;
use Codeweber\S3Storage\Tools\Offload;
use Codeweber\S3Storage\Tools\Restore;
use Codeweber\S3Storage\Tools\Sync;
use Codeweber\S3Storage\Tools\Uninstaller;
use Codeweber\S3Storage\Tools\Wipe;
use Codeweber\S3Storage\Tools\ReapplyCacheHeaders;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BatchRunner {

	const CRON_HOOK = 'cws3_run_batch';

	public function register_cron() {
		add_action( self::CRON_HOOK, [ $this, 'run' ], 10, 1 );
	}

	public function register_ajax() {
		add_action( 'wp_ajax_cws3_start_job', [ $this, 'ajax_start' ] );
		add_action( 'wp_ajax_cws3_job_status', [ $this, 'ajax_status' ] );
		add_action( 'wp_ajax_cws3_control_job', [ $this, 'ajax_control' ] );
	}

	public function ajax_start() {
		$this->guard();
		$type    = isset( $_POST['type'] ) ? sanitize_key( wp_unslash( $_POST['type'] ) ) : '';
		$dry_run = ! empty( $_POST['dry_run'] );

		$runner = $this->runner_for( $type );
		if ( ! $runner ) {
			wp_send_json_error( [ 'message' => 'Unknown job type.' ] );
		}

		$existing = JobsTable::current_of_type( $type );
		if ( $existing && in_array( $existing->status, [ JobsTable::STATUS_PENDING, JobsTable::STATUS_RUNNING ], true ) ) {
			wp_send_json_success( [ 'job_id' => (int) $existing->id, 'resumed' => true ] );
		}

		$total  = $runner->estimate_total();
		$job_id = JobsTable::create( $type, $total, 20, $dry_run );
		JobsTable::set_status( $job_id, JobsTable::STATUS_RUNNING );
		Logger::info( 'job', 'Job started.', [ 'type' => $type, 'total' => $total, 'dry_run' => $dry_run ] );
		$this->schedule_next( $job_id );

		wp_send_json_success( [ 'job_id' => $job_id ] );
	}

	public function ajax_status() {
		$this->guard();
		$job_id = isset( $_GET['job_id'] ) ? (int) $_GET['job_id'] : 0;
		$job    = JobsTable::get( $job_id );
		if ( ! $job ) {
			wp_send_json_error( [ 'message' => 'Job not found.' ] );
		}
		wp_send_json_success( [
			'id'        => (int) $job->id,
			'type'      => $job->job_type,
			'status'    => $job->status,
			'total'     => (int) $job->total,
			'processed' => (int) $job->processed,
			'failed'    => (int) $job->failed,
			'dry_run'   => (int) $job->dry_run,
			'error'     => $job->last_error,
		] );
	}

	public function ajax_control() {
		$this->guard();
		$job_id = isset( $_POST['job_id'] ) ? (int) $_POST['job_id'] : 0;
		$action = isset( $_POST['control'] ) ? sanitize_key( wp_unslash( $_POST['control'] ) ) : '';
		$job    = JobsTable::get( $job_id );
		if ( ! $job ) {
			wp_send_json_error( [ 'message' => 'Job not found.' ] );
		}
		switch ( $action ) {
			case 'pause':
				JobsTable::set_status( $job_id, JobsTable::STATUS_PAUSED );
				break;
			case 'resume':
				JobsTable::set_status( $job_id, JobsTable::STATUS_RUNNING );
				$this->schedule_next( $job_id );
				break;
			case 'cancel':
				JobsTable::set_status( $job_id, JobsTable::STATUS_CANCELLED );
				wp_clear_scheduled_hook( self::CRON_HOOK, [ $job_id ] );
				break;
			default:
				wp_send_json_error( [ 'message' => 'Unknown control.' ] );
		}
		Logger::info( 'job', 'Job control: ' . $action, [ 'job_id' => $job_id ] );
		wp_send_json_success();
	}

	public function run( $job_id ) {
		@ignore_user_abort( true );
		if ( function_exists( 'session_write_close' ) ) {
			@session_write_close();
		}

		$job_id = (int) $job_id;
		$job    = JobsTable::get( $job_id );
		if ( ! $job || $job->status !== JobsTable::STATUS_RUNNING ) {
			return;
		}

		$runner = $this->runner_for( $job->job_type );
		if ( ! $runner ) {
			JobsTable::set_status( $job_id, JobsTable::STATUS_FAILED, 'Unknown job type' );
			return;
		}

		try {
			$result = $runner->run_batch( $job );
		} catch ( \Throwable $e ) {
			Logger::error( 'job_run', $e->getMessage(), [ 'job_id' => $job_id, 'type' => $job->job_type ] );
			JobsTable::set_status( $job_id, JobsTable::STATUS_FAILED, $e->getMessage() );
			return;
		}

		JobsTable::increment( $job_id, (int) ( $result['processed'] ?? 0 ), (int) ( $result['failed'] ?? 0 ), (int) ( $result['cursor'] ?? 0 ) );

		$job = JobsTable::get( $job_id );
		if ( empty( $result['done'] ) && $job->status === JobsTable::STATUS_RUNNING ) {
			$this->schedule_next( $job_id );
		} else {
			JobsTable::set_status( $job_id, JobsTable::STATUS_COMPLETED );
			Logger::info( 'job', 'Job completed.', [ 'job_id' => $job_id, 'type' => $job->job_type ] );
		}
	}

	private function schedule_next( int $job_id ) {
		wp_schedule_single_event( time() + 1, self::CRON_HOOK, [ $job_id ] );
		if ( function_exists( 'spawn_cron' ) ) {
			spawn_cron();
		}
	}

	private function runner_for( string $type ) {
		switch ( $type ) {
			case 'offload':
				return new Offload();
			case 'restore':
				return new Restore();
			case 'delete_local':
				return new DeleteLocal();
			case 'sync':
				return new Sync();
			case 'uninstall':
				return new Uninstaller();
			case 'wipe':
				return new Wipe();
			case 'reapply_cache':
				return new ReapplyCacheHeaders();
		}
		return null;
	}

	private function guard() {
		check_ajax_referer( 'cws3_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Permission denied.' ], 403 );
		}
	}
}
