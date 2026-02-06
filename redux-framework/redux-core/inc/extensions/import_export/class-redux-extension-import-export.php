<?php
/**
 * Redux Import/Export Extension Class
 *
 * @class   Redux_Extension_Import_Export
 * @version 4.0.0
 * @package Redux Framework
 *
 * @noinspection PhpIgnoredClassAliasDeclaration
 */

defined( 'ABSPATH' ) || exit;

// Don't duplicate me!
if ( ! class_exists( 'Redux_Extension_Import_Export', false ) ) {

	/**
	 * Main ReduxFramework import_export extension class
	 *
	 * @since       3.1.6
	 */
	class Redux_Extension_Import_Export extends Redux_Extension_Abstract {

		/**
		 * Ext version.
		 *
		 * @var string
		 */
		public static string $version = '4.0.0';

		/**
		 * Is field bit.
		 *
		 * @var bool
		 */
		public bool $is_field = false;

		/**
		 * Class Constructor. Defines the args for the extensions class
		 *
		 * @param object $redux ReduxFramework object.
		 *
		 * @return      void
		 * @since       1.0.0
		 * @access      public
		 */
		public function __construct( $redux ) {
			parent::__construct( $redux, __FILE__ );

			$this->add_field( 'import_export' );

			add_action( 'wp_ajax_redux_download_options-' . $this->parent->args['opt_name'], array( $this, 'download_options' ) );
			add_action( 'wp_ajax_nopriv_redux_download_options-' . $this->parent->args['opt_name'], array( $this, 'download_options' ) );

			add_action( 'wp_ajax_redux_save_to_theme-' . $this->parent->args['opt_name'], array( $this, 'save_to_theme' ) );
			add_action( 'wp_ajax_redux_load_from_theme-' . $this->parent->args['opt_name'], array( $this, 'load_from_theme' ) );

			// phpcs:ignore WordPress.NamingConventions.ValidHookName
			do_action( 'redux/options/' . $this->parent->args['opt_name'] . '/import', array( $this, 'remove_cookie' ) );

			$this->is_field = Redux_Helpers::is_field_in_use( $redux, 'import_export' );

			if ( ! $this->is_field && $this->parent->args['show_import_export'] ) {
				$this->add_section();
			}

			add_filter( 'upload_mimes', array( $this, 'custom_upload_mimes' ) );
		}

		/**
		 * Adds the appropriate mime types to WordPress
		 *
		 * @param array|null $existing_mimes .
		 *
		 * @return array
		 */
		public function custom_upload_mimes( ?array $existing_mimes = array() ): array {
			$existing_mimes['redux'] = 'application/redux';

			return $existing_mimes;
		}

		/**
		 * Add section to panel.
		 */
		public function add_section() {
			$this->parent->sections[] = array(
				'id'         => 'import/export',
				'title'      => esc_html__( 'Import / Export', 'redux-framework' ),
				'heading'    => '',
				'icon'       => 'el el-refresh',
				'customizer' => false,
				'fields'     => array(
					array(
						'id'         => 'redux_import_export',
						'type'       => 'import_export',
						'full_width' => true,
					),
				),
			);
		}

		/**
		 * Import download options.
		 */
		public function download_options() {
			if ( ! isset( $_GET['secret'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['secret'] ) ), 'redux_io_' . $this->parent->args['opt_name'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				wp_die( 'Invalid Secret for options use.' );
			}

			$this->parent->options_class->get();
			$backup_options                 = $this->parent->options;
			$backup_options['redux-backup'] = 1;

			if ( isset( $backup_options['REDUX_imported'] ) ) {
				unset( $backup_options['REDUX_imported'] );
			}

			// No need to escape this, as it's been properly escaped previously and through json_encode.
			$content = wp_json_encode( $backup_options );

			if ( isset( $_GET['action'] ) && 'redux_download_options-' . $this->parent->args['opt_name'] === $_GET['action'] ) { // phpcs:ignore WordPress.Security.NonceVerification
				header( 'Content-Description: File Transfer' );
				header( 'Content-type: application/txt' );
				header( 'Content-Disposition: attachment; filename="redux_options_"' . $this->parent->args['opt_name'] . '_backup_' . gmdate( 'm-d-Y' ) . '.json' );
				header( 'Content-Transfer-Encoding: binary' );
				header( 'Expires: 0' );
				header( 'Cache-Control: must-revalidate' );
				header( 'Pragma: public' );
			} else {
				header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
				header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . 'GMT' );
				header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' );
				header( 'Cache-Control: no-store, no-cache, must-revalidate' );
				header( 'Cache-Control: post-check=0, pre-check=0', false );
				header( 'Pragma: no-cache' );

				// Can't include the type. Thanks old Firefox and IE. BAH.
				// header('Content-type: application/json');.
			}

			// phpcs:ignore WordPress.Security.EscapeOutput
			echo( $content );
			exit;
		}

		/**
		 * Get path to redux-settings.json in active theme.
		 *
		 * @return string
		 */
		private function get_theme_settings_path(): string {
			return get_stylesheet_directory() . '/redux-settings.json';
		}

		/**
		 * Save options to theme redux-settings.json file.
		 */
		public function save_to_theme() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( array( 'message' => __( 'Permission denied.', 'redux-framework' ) ) );
			}

			// phpcs:ignore WordPress.Security.NonceVerification
			if ( ! isset( $_POST['secret'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['secret'] ) ), 'redux_io_' . $this->parent->args['opt_name'] ) ) {
				wp_send_json_error( array( 'message' => __( 'Invalid security token.', 'redux-framework' ) ) );
			}

			$this->parent->options_class->get();
			$backup_options                 = $this->parent->options;
			$backup_options['redux-backup'] = 1;

			if ( isset( $backup_options['REDUX_imported'] ) ) {
				unset( $backup_options['REDUX_imported'] );
			}

			$content  = wp_json_encode( $backup_options, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
			$filepath = $this->get_theme_settings_path();

			if ( ! is_writable( dirname( $filepath ) ) ) {
				wp_send_json_error( array( 'message' => __( 'Theme directory is not writable.', 'redux-framework' ) ) );
			}

			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
			if ( false === file_put_contents( $filepath, $content ) ) {
				wp_send_json_error( array( 'message' => __( 'Failed to save file.', 'redux-framework' ) ) );
			}

			wp_send_json_success( array( 'message' => __( 'Settings saved to theme successfully.', 'redux-framework' ) ) );
		}

		/**
		 * Load options from theme redux-settings.json file.
		 */
		public function load_from_theme() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( array( 'message' => __( 'Permission denied.', 'redux-framework' ) ) );
			}

			// phpcs:ignore WordPress.Security.NonceVerification
			if ( ! isset( $_GET['secret'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['secret'] ) ), 'redux_io_' . $this->parent->args['opt_name'] ) ) {
				wp_send_json_error( array( 'message' => __( 'Invalid security token.', 'redux-framework' ) ) );
			}

			$filepath = $this->get_theme_settings_path();

			if ( ! file_exists( $filepath ) ) {
				wp_send_json_error( array( 'message' => __( 'Settings file not found in theme.', 'redux-framework' ) ) );
			}

			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$content = file_get_contents( $filepath );
			$data   = json_decode( $content, true );

			if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $data ) ) {
				wp_send_json_error( array( 'message' => __( 'Invalid settings file format.', 'redux-framework' ) ) );
			}

			wp_send_json_success( array( 'data' => $content, 'message' => __( 'Settings loaded from theme.', 'redux-framework' ) ) );
		}

		/**
		 * Remove current tab cookie.
		 */
		public function remove_cookie() {
			// Remove the import/export tab cookie.
			if ( isset( $_COOKIE[ 'redux_current_tab_' . $this->parent->args['opt_name'] ] ) && 'import_export_default' === $_COOKIE[ 'redux_current_tab_' . $this->parent->args['opt_name'] ] ) {
				setcookie( 'redux_current_tab_' . $this->parent->args['opt_name'], '', 1, '/' );
				$_COOKIE[ 'redux_current_tab_' . $this->parent->args['opt_name'] ] = 1;
			}
		}
	}
}

if ( ! class_exists( 'ReduxFramework_extension_import_export' ) ) {
	class_alias( 'Redux_Extension_Import_Export', 'ReduxFramework_extension_import_export' );
}
