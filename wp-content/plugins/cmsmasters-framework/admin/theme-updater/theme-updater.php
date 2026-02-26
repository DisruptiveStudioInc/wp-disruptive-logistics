<?php
namespace CmsmastersFramework\Admin\ThemeUpdater;

use CmsmastersFramework\Core\Utils\API_Requests;
use CmsmastersFramework\Core\Utils\Logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Theme_Updater module.
 *
 * Main class for Theme_Updater module.
 *
 * @since 1.0.9
 */
class Theme_Updater {

	/**
	 * Theme module constructor.
	 *
	 * @since 1.0.9
	 */
	public function __construct() {
		add_filter( 'pre_set_site_transient_update_themes', array( $this, 'update_theme' ) );
		
		$this->maybe_run_theme_update_check();
	}

	/**
	 * Check for theme updates via API.
	 *
	 * @since 1.0.9
	 *
	 * @param object $transient
	 *
	 * @return object
	 */
	public function update_theme( $transient ) {
		if (
			empty( $transient->checked ) ||
			API_Requests::is_empty_token_status()
		) {
			return $transient;
		}

		$data = API_Requests::post_request( 'get-theme-data' );

		if ( is_wp_error( $data ) ) {
			Logger::error( 'Update Theme: ' . $data->get_error_message() );

			return $transient;
		}

		if (
			empty( $data ) ||
			! is_array( $data ) ||
			empty( $data['theme_version'] ) ||
			empty( $data['theme_path'] )
		) {
			Logger::error( 'Update Theme: Empty data from get-theme-data route' );

			return $transient;
		}

		$theme = wp_get_theme( CMSMASTERS_THEME_NAME );

		if ( version_compare( $data['theme_version'], $theme->get( 'Version' ), '>' ) ) {
			$transient->response[ CMSMASTERS_THEME_NAME ] = [
				'theme' => CMSMASTERS_THEME_NAME,
				'new_version' => $data['theme_version'],
				'url' => $data['theme_path'],
				'package' => $data['theme_path'],
			];
		}

		return $transient;
	}

	/**
	 * Maybe run theme update check.
	 *
	 * @since 1.0.9
	 */
	private function maybe_run_theme_update_check() {
		$last_check = get_option( 'cmsmasters_last_theme_update_check', 0 );

		if ( ( time() - $last_check ) > DAY_IN_SECONDS ) {
			update_option( 'cmsmasters_last_theme_update_check', time() );

			delete_site_transient( 'update_themes' );

			wp_update_themes();
		}
	}

}
