<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cws3_status_html = '';
if ( defined( 'CWS3_VERSION' ) ) {
	$cws3_status_html = '<p style="color:#00794d;"><strong>' . esc_html__( 'Module loaded', 'codeweber' ) . '</strong> v' . esc_html( CWS3_VERSION ) . '</p>';
} else {
	$cws3_status_html = '<p style="color:#b32d2e;">' . esc_html__( 'Module is not loaded yet. Toggle the switch above and save.', 'codeweber' ) . '</p>';
}

$cws3_links_html  = '<p>';
$cws3_links_html .= '<a href="' . esc_url( admin_url( 'options-general.php?page=cws3-settings' ) ) . '" class="button">' . esc_html__( 'Open Settings', 'codeweber' ) . '</a> ';
$cws3_links_html .= '<a href="' . esc_url( admin_url( 'tools.php?page=cws3-tools' ) ) . '" class="button">' . esc_html__( 'Open Tools', 'codeweber' ) . '</a> ';
$cws3_links_html .= '<a href="' . esc_url( admin_url( 'tools.php?page=cws3-logs' ) ) . '" class="button">' . esc_html__( 'Open Logs', 'codeweber' ) . '</a>';
$cws3_links_html .= '</p>';

Redux::set_section(
	$opt_name,
	array(
		'title'            => esc_html__( 'S3 Storage', 'codeweber' ),
		'id'               => 's3_storage',
		'desc'             => esc_html__( 'Offload WordPress media to a custom S3-compatible server (MinIO, Ceph, Garage).', 'codeweber' ),
		'customizer_width' => '300px',
		'icon'             => 'el el-hdd',
		'fields'           => array(
			array(
				'id'       => 's3_storage_enabled',
				'type'     => 'switch',
				'title'    => esc_html__( 'Enable S3 Storage integration', 'codeweber' ),
				'subtitle' => esc_html__( 'When enabled, media uploads, URL rewriting and tools pages become active. Disable to turn off the module without removing any data.', 'codeweber' ),
				'default'  => false,
			),
			array(
				'id'       => 's3_storage_status',
				'type'     => 'raw',
				'title'    => esc_html__( 'Status', 'codeweber' ),
				'content'  => $cws3_status_html,
				'required' => array( 's3_storage_enabled', '=', true ),
			),
			array(
				'id'       => 's3_storage_links',
				'type'     => 'raw',
				'title'    => esc_html__( 'Quick links', 'codeweber' ),
				'content'  => $cws3_links_html,
				'required' => array( 's3_storage_enabled', '=', true ),
			),
			array(
				'id'       => 's3_storage_info',
				'type'     => 'info',
				'style'    => 'info',
				'title'    => esc_html__( 'How it works', 'codeweber' ),
				'desc'     => esc_html__( 'Configure endpoint, bucket and credentials under Settings → S3 Storage. Manage bulk operations under Tools → S3 Storage. Logs are stored in the module folder and viewable under Tools → S3 Storage Logs.', 'codeweber' ),
				'required' => array( 's3_storage_enabled', '=', true ),
			),
		),
	)
);
