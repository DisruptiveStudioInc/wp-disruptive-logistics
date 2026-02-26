<?php
namespace CmsmastersElementor\Modules\Wpml;

use CmsmastersElementor\Base\Base_Module;
use CmsmastersElementor\Plugin;

use Elementor\Controls_Manager;


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


/**
 * WPML module.
 *
 * @since 1.3.3
 */
class Module extends Base_Module {

	/**
	 * Get name.
	 *
	 * Retrieve the module name.
	 *
	 * @since 1.3.3
	 *
	 * @return string Module name.
	 */
	public function get_name() {
		return 'wpml';
	}

	/**
	 * Module activation.
	 *
	 * Check if module is active.
	 *
	 * @since 1.3.3
	 *
	 * @return bool
	 */
	public static function is_active() {
		return did_action( 'wpml_loaded' );
	}

	/**
	 * Init filters.
	 *
	 * Initialize module filters.
	 *
	 * @since 1.3.3
	 */
	protected function init_filters() {
		add_filter( 'wpml_elementor_widgets_to_translate', array( $this, 'get_translatable_widgets' ) );

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
	 * Get translatable widgets.
	 *
	 * @since 1.3.3
	 *
	 * @param array $widgets Translatable widgets.
	 *
	 * @return array Filtered translatable widgets.
	 */
	public function get_translatable_widgets( $widgets ) {
		foreach ( Plugin::elementor()->widgets_manager->get_widget_types() as $widget_key => $widget_obj ) {
			if ( false === strpos( $widget_key, 'cmsmasters' ) ) {
				continue;
			}

			$fields = $widget_obj::get_wpml_fields();
			$fields_in_item = $widget_obj::get_wpml_fields_in_item();

			if ( empty( $fields ) && empty( $fields_in_item ) ) {
				continue;
			}

			if ( ! empty( $fields ) ) {
				foreach ( $fields as $index => $field ) {
					$fields[ $index ]['type'] = $field['type'] . ' (' . $widget_obj->get_title() . ')';
				}
			}

			if ( ! empty( $fields_in_item ) ) {
				foreach ( $fields_in_item as $item_key => $item_fields ) {
					foreach ( $item_fields as $item_field_index => $item_field ) {
						$fields_in_item[ $item_key ][ $item_field_index ]['type'] = $item_field['type'] . ' (' . $widget_obj->get_title() . ')';
					}
				}
			}

			$widgets[ $widget_key ] = array(
				'conditions' => array(
					'widgetType' => $widget_key,
				),
				'fields' => $fields,
				'fields_in_item' => $fields_in_item,
			);
		}

		return $widgets;
	}

	/**
	 * Get translated template id.
	 *
	 * @since 1.3.3
	 *
	 * @param int $template_id Template id.
	 *
	 * @return int Translated template id.
	 */
	public function get_translated_template_id( $template_id ) {
		if ( empty( $template_id ) ) {
			return $template_id;
		}

		$post_type = get_post_type( $template_id );

		return apply_filters( 'wpml_object_id', $template_id, $post_type, true );
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

		$translated_args = array();

		foreach ( $args as $post_id ) {
			$post_type = get_post_type( $post_id );
			$translated_id = apply_filters( 'wpml_object_id', $post_id, $post_type, true );

			$translated_args[] = $translated_id ? (int) $translated_id : $post_id;
		}

		return $translated_args;
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
	 * Filter locations for translated templates.
	 *
	 * Removes locations from settings when saving a translated template
	 * to prevent location duplication.
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

		$post_id = get_the_ID();

		if ( ! $post_id ) {
			return $settings;
		}

		if ( ! $this->is_translation( $post_id ) ) {
			return $settings;
		}

		// For translated templates, remove locations to prevent duplication
		unset( $settings['locations'] );

		return $settings;
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

		// Remove only this specific template from global storage
		$this->remove_template_from_storage( $post_id );
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
		$default_language = apply_filters( 'wpml_default_language', null );

		if ( ! $default_language ) {
			return false;
		}

		$post_language = apply_filters( 'wpml_element_language_code', null, array(
			'element_id' => $post_id,
			'element_type' => get_post_type( $post_id ),
		) );

		return $post_language && $post_language !== $default_language;
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
		$default_language = apply_filters( 'wpml_default_language', null );

		if ( ! $default_language ) {
			return false;
		}

		$post_type = get_post_type( $post_id );

		return apply_filters( 'wpml_object_id', $post_id, $post_type, true, $default_language );
	}

	/**
	 * Remove specific template from locations storage.
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
