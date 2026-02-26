<?php
namespace CmsmastersElementor\Modules\Polylang;

use CmsmastersElementor\Base\Base_Module;
use CmsmastersElementor\Plugin;

use Elementor\Controls_Manager;


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


/**
 * Polylang module.
 *
 * @since 1.19.4
 */
class Module extends Base_Module {

	/**
	 * Get name.
	 *
	 * Retrieve the module name.
	 *
	 * @since 1.19.4
	 *
	 * @return string Module name.
	 */
	public function get_name() {
		return 'polylang';
	}

	/**
	 * Module activation.
	 *
	 * Check if module is active.
	 *
	 * @since 1.19.4
	 *
	 * @return bool
	 */
	public static function is_active() {
		return defined( 'POLYLANG_VERSION' ) || function_exists( 'pll_current_language' );
	}

	/**
	 * Init filters.
	 *
	 * Initialize module filters.
	 *
	 * @since 1.19.4
	 */
	protected function init_filters() {
		add_filter( 'cmsmasters_translated_template_id', array( $this, 'get_translated_template_id' ) );

		add_filter( 'cmsmasters_translated_location_args', array( $this, 'translate_location_args' ) );

		// Remove locations for translated templates to prevent duplication
		add_filter( 'cmsmasters_elementor/documents/before_save_settings', array( $this, 'filter_translated_template_locations' ), 5 );
	}

	/**
	 * Init actions.
	 *
	 * Initialize module actions.
	 *
	 * @since 1.19.4
	 */
	protected function init_actions() {
		// Delete locations meta for translated templates after document save
		add_action( 'elementor/document/after_save', array( $this, 'delete_translated_template_locations' ), 10, 2 );

		// Hide locations section for translated templates
		add_action( 'cmsmasters_elementor/documents/header_footer/register_controls', array( $this, 'hide_locations_for_translated_templates' ), 20 );
		add_action( 'cmsmasters_elementor/documents/archive_singular/register_controls', array( $this, 'hide_locations_for_translated_templates' ), 20 );
	}

	/**
	 * Hide locations section for translated templates.
	 *
	 * Replaces the locations control with an info notice
	 * for translated templates since they inherit locations
	 * from the original template.
	 *
	 * @since 1.19.4
	 *
	 * @param \CmsmastersElementor\Base\Base_Document $document Document instance.
	 */
	public function hide_locations_for_translated_templates( $document ) {
		$post_id = $document->get_main_id();

		if ( ! $this->is_translation( $post_id ) ) {
			return;
		}

		// Get the original template URL for the link
		$original_id = $this->get_original_template_id( $post_id );
		$original_edit_url = '';

		if ( $original_id ) {
			$original_edit_url = admin_url( 'post.php?post=' . $original_id . '&action=elementor' );
		}

		$description = __( 'Location settings are managed in the original (default language) template. Translated templates automatically inherit locations from the original.', 'cmsmasters-elementor' );

		// Replace the locations control with info notice
		$document->update_control(
			'locations',
			array(
				'label' => '',
				'type' => Controls_Manager::RAW_HTML,
				'raw' => $description,
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
			)
		);

		// Add button to edit original template as separate control
		if ( $original_edit_url ) {
			$document->start_injection( array(
				'of' => 'locations',
				'at' => 'after',
			) );

			$document->add_control(
				'locations_edit_original_button',
				array(
					'label' => '',
					'type' => Controls_Manager::RAW_HTML,
					'raw' => '<a href="' . esc_url( $original_edit_url ) . '" target="_blank" class="elementor-button elementor-button-default">' . __( 'Edit Original Template', 'cmsmasters-elementor' ) . '</a>',
				)
			);

			$document->end_injection();
		}
	}

	/**
	 * Get original (default language) template ID.
	 *
	 * @since 1.19.4
	 *
	 * @param int $post_id Translated post ID.
	 *
	 * @return int|false Original post ID or false.
	 */
	private function get_original_template_id( $post_id ) {
		if ( ! function_exists( 'pll_get_post' ) || ! function_exists( 'pll_default_language' ) ) {
			return false;
		}

		$default_language = pll_default_language();

		return pll_get_post( $post_id, $default_language );
	}

	/**
	 * Get translated template id.
	 *
	 * @since 1.19.4
	 *
	 * @param int $template_id Template id.
	 *
	 * @return int Translated template id.
	 */
	public function get_translated_template_id( $template_id ) {
		if ( empty( $template_id ) ) {
			return $template_id;
		}

		if ( ! function_exists( 'pll_get_post' ) ) {
			return $template_id;
		}

		$translated_id = pll_get_post( $template_id );

		return $translated_id ? $translated_id : $template_id;
	}

	/**
	 * Translate location args (post IDs) to current language.
	 *
	 * Used for template display conditions with specific pages/posts.
	 *
	 * @since 1.19.4
	 *
	 * @param array $args Array of post IDs from location condition.
	 *
	 * @return array Translated post IDs.
	 */
	public function translate_location_args( $args ) {
		if ( empty( $args ) || ! is_array( $args ) ) {
			return $args;
		}

		if ( ! function_exists( 'pll_get_post' ) ) {
			return $args;
		}

		$translated_args = array();

		foreach ( $args as $post_id ) {
			$translated_id = pll_get_post( $post_id );

			$translated_args[] = $translated_id ? (int) $translated_id : $post_id;
		}

		return $translated_args;
	}

	/**
	 * Filter locations for translated templates.
	 *
	 * Removes locations from settings when saving a translated template
	 * to prevent location duplication. Only the original (default language)
	 * template should have locations stored.
	 *
	 * @since 1.19.4
	 *
	 * @param array $settings Document settings being saved.
	 *
	 * @return array Filtered settings.
	 */
	public function filter_translated_template_locations( $settings ) {
		if ( ! isset( $settings['locations'] ) ) {
			return $settings;
		}

		if ( ! function_exists( 'pll_get_post_language' ) || ! function_exists( 'pll_default_language' ) ) {
			return $settings;
		}

		$post_id = get_the_ID();

		if ( ! $post_id ) {
			return $settings;
		}

		// Check if this template is a translation (not in default language)
		$post_language = pll_get_post_language( $post_id );
		$default_language = pll_default_language();

		// If template is in default language, keep locations as is
		if ( $post_language === $default_language ) {
			return $settings;
		}

		// For translated templates, remove locations to prevent duplication
		unset( $settings['locations'] );

		return $settings;
	}

	/**
	 * Check if a post is a translation (not in default language).
	 *
	 * @since 1.19.4
	 *
	 * @param int $post_id Post ID to check.
	 *
	 * @return bool True if post is a translation, false if original.
	 */
	public function is_translation( $post_id ) {
		if ( ! function_exists( 'pll_get_post_language' ) || ! function_exists( 'pll_default_language' ) ) {
			return false;
		}

		$post_language = pll_get_post_language( $post_id );
		$default_language = pll_default_language();

		return $post_language !== $default_language;
	}

	/**
	 * Delete locations meta for translated templates.
	 *
	 * Called after document save to ensure translated templates
	 * don't have their own locations stored.
	 *
	 * @since 1.19.4
	 *
	 * @param \Elementor\Core\Base\Document $document The document instance.
	 * @param array $data The document data.
	 */
	public function delete_translated_template_locations( $document, $data ) {
		$post_id = $document->get_main_id();

		if ( ! $this->is_translation( $post_id ) ) {
			return;
		}

		// Delete locations meta for translated template
		$document->delete_main_meta( '_cmsmasters_locations' );

		// Remove only this specific template from global storage (without full regeneration)
		$this->remove_template_from_storage( $post_id );
	}

	/**
	 * Remove specific template from locations storage.
	 *
	 * Surgically removes only the translated template from global option
	 * without affecting other templates.
	 *
	 * @since 1.19.4
	 *
	 * @param int $post_id Template post ID to remove.
	 */
	private function remove_template_from_storage( $post_id ) {
		if ( ! class_exists( '\CmsmastersElementor\Modules\TemplateLocations\Module' ) ) {
			return;
		}

		$locations_module = \CmsmastersElementor\Modules\TemplateLocations\Module::instance();

		if ( $locations_module && method_exists( $locations_module, 'get_rules_manager' ) ) {
			$rules_manager = $locations_module->get_rules_manager();

			if ( $rules_manager && method_exists( $rules_manager, 'remove_post_from_storage' ) ) {
				$rules_manager->remove_post_from_storage( $post_id );
			}
		}
	}

}




