<?php
namespace CargoTruckingSpace\Kits\Traits\ControlsGroups;

use CargoTruckingSpace\Kits\Controls\Controls_Manager as CmsmastersControls;
use CargoTruckingSpace\Kits\Settings\Base\Settings_Tab_Base;

use Elementor\Controls_Manager;


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


/**
 * Quotes trait.
 *
 * Allows to use a group of controls for quotes.
 */
trait Quotes {

	/**
	 * Group of controls for quotes.
	 *
	 * @param string $key Controls key.
	 * @param array $args Controls args.
	 */
	protected function controls_group_quotes( $key = '', $args = array() ) {
		list(
			$typography_group,
			$condition,
			$conditions
		) = $this->get_controls_group_required_args( $args, array(
			'typography_group' => true, // Typography group controls
			'condition' => array(), // Controls condition
			'conditions' => array(), // Controls conditions
		) );

		$default_args = array(
			'condition' => $condition,
			'conditions' => $conditions,
		);

		if ( $typography_group ) {
			$this->add_var_group_control(
				$this->get_control_name_parameter( $key ),
				Settings_Tab_Base::VAR_TYPOGRAPHY,
				$default_args
			);
		}

		$this->add_control(
			$this->get_control_name_parameter( $key, 'colors_heading_control' ),
			array_merge_recursive(
				$default_args,
				array(
					'label' => esc_html__( 'Colors', 'cargo-trucking' ),
					'type' => Controls_Manager::HEADING,
				)
			)
		);

		$this->add_control(
			$this->get_control_name_parameter( $key, 'colors_text' ),
			array_merge_recursive(
				$default_args,
				array(
					'label' => esc_html__( 'Text', 'cargo-trucking' ),
					'type' => Controls_Manager::COLOR,
					'dynamic' => array(),
					'selectors' => array(
						':root' => '--' . $this->get_control_prefix_parameter( $key, 'colors_text' ) . ': {{VALUE}};',
					),
				)
			)
		);

		$this->add_control(
			$this->get_control_name_parameter( $key, 'colors_link' ),
			array_merge_recursive(
				$default_args,
				array(
					'label' => esc_html__( 'Link', 'cargo-trucking' ),
					'type' => Controls_Manager::COLOR,
					'dynamic' => array(),
					'selectors' => array(
						':root' => '--' . $this->get_control_prefix_parameter( $key, 'colors_link' ) . ': {{VALUE}};',
					),
				)
			)
		);

		$this->add_control(
			$this->get_control_name_parameter( $key, 'colors_hover' ),
			array_merge_recursive(
				$default_args,
				array(
					'label' => esc_html__( 'Link Hover', 'cargo-trucking' ),
					'type' => Controls_Manager::COLOR,
					'dynamic' => array(),
					'selectors' => array(
						':root' => '--' . $this->get_control_prefix_parameter( $key, 'colors_hover' ) . ': {{VALUE}};',
					),
				)
			)
		);

		$this->add_control(
			$this->get_control_name_parameter( $key, 'colors_bg' ),
			array_merge_recursive(
				$default_args,
				array(
					'label' => esc_html__( 'Background', 'cargo-trucking' ),
					'type' => Controls_Manager::COLOR,
					'dynamic' => array(),
					'selectors' => array(
						':root' => '--' . $this->get_control_prefix_parameter( $key, 'colors_bg' ) . ': {{VALUE}};',
					),
				)
			)
		);

		$this->add_control(
			$this->get_control_name_parameter( $key, 'colors_bd' ),
			array_merge_recursive(
				$default_args,
				array(
					'label' => esc_html__( 'Border', 'cargo-trucking' ),
					'type' => Controls_Manager::COLOR,
					'dynamic' => array(),
					'selectors' => array(
						':root' => '--' . $this->get_control_prefix_parameter( $key, 'colors_bd' ) . ': {{VALUE}};',
					),
					'condition' => array(
						$this->get_control_id_parameter( $key, 'border_border!' ) => 'none',
					),
				)
			)
		);

		$this->add_var_group_control(
			$this->get_control_name_parameter( $key ),
			Settings_Tab_Base::VAR_BORDER,
			array_merge_recursive(
				$default_args,
				array(
					'fields_options' => array(
						'width' => array(
							'label' => esc_html__( 'Border Width', 'cargo-trucking' ),
						),
					),
					'exclude' => array( 'color' ),
					'separator' => 'before',
				)
			)
		);

		$this->add_control(
			$this->get_control_name_parameter( $key, 'bd_radius' ),
			array_merge_recursive(
				$default_args,
				array(
					'label' => esc_html__( 'Border Radius', 'cargo-trucking' ),
					'type' => Controls_Manager::DIMENSIONS,
					'size_units' => array(
						'px',
						'%',
					),
					'selectors' => array(
						':root' => '--' . $this->get_control_prefix_parameter( $key, 'bd_radius' ) . ': {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			)
		);

		$this->add_var_group_control(
			$this->get_control_name_parameter( $key ),
			Settings_Tab_Base::VAR_BOX_SHADOW,
			$default_args
		);

		$this->add_responsive_control(
			$this->get_control_name_parameter( $key, 'padding' ),
			array_merge_recursive(
				$default_args,
				array(
					'label' => esc_html__( 'Padding', 'cargo-trucking' ),
					'type' => Controls_Manager::DIMENSIONS,
					'size_units' => array(
						'px',
						'em',
						'%',
					),
					'selectors' => array(
						':root' => '--' . $this->get_control_prefix_parameter( $key, 'padding_top' ) . ': {{TOP}}{{UNIT}};' .
							'--' . $this->get_control_prefix_parameter( $key, 'padding_right' ) . ': {{RIGHT}}{{UNIT}};' .
							'--' . $this->get_control_prefix_parameter( $key, 'padding_bottom' ) . ': {{BOTTOM}}{{UNIT}};' .
							'--' . $this->get_control_prefix_parameter( $key, 'padding_left' ) . ': {{LEFT}}{{UNIT}};',
					),
				)
			)
		);

		$this->add_control(
			$this->get_control_name_parameter( $key, 'cite_heading_control' ),
			array_merge_recursive(
				$default_args,
				array(
					'label' => esc_html__( 'Cite', 'cargo-trucking' ),
					'type' => Controls_Manager::HEADING,
					'separator' => 'before',
				)
			)
		);

		$this->add_var_group_control(
			$this->get_control_name_parameter( $key, 'cite' ),
			Settings_Tab_Base::VAR_TYPOGRAPHY,
			$default_args
		);

		$this->add_control(
			$this->get_control_name_parameter( $key, 'cite_color' ),
			array_merge_recursive(
				$default_args,
				array(
					'label' => esc_html__( 'Color', 'cargo-trucking' ),
					'type' => Controls_Manager::COLOR,
					'dynamic' => array(),
					'selectors' => array(
						':root' => '--' . $this->get_control_prefix_parameter( $key, 'cite_color' ) . ': {{VALUE}};',
					),
				)
			)
		);

		$this->add_responsive_control(
			$this->get_control_name_parameter( $key, 'cite_gap' ),
			array_merge_recursive(
				$default_args,
				array(
					'label' => esc_html__( 'Gap Between', 'cargo-trucking' ),
					'type' => Controls_Manager::SLIDER,
					'range' => array(
						'px' => array(
							'min' => 0,
							'max' => 50,
						),
						'%' => array(
							'min' => 0,
							'max' => 100,
						),
					),
					'size_units' => array(
						'px',
						'%',
					),
					'selectors' => array(
						':root' => '--' . $this->get_control_prefix_parameter( $key, 'cite_gap' ) . ': {{SIZE}}{{UNIT}};',
					),
				)
			)
		);

		$this->add_control(
			$this->get_control_name_parameter( $key, 'icon_visibility' ),
			array_merge_recursive(
				$default_args,
				array(
					'label' => esc_html__( 'Icon', 'cargo-trucking' ),
					'label_block' => false,
					'toggle' => true,
					'type' => CmsmastersControls::CHOOSE_TEXT,
					'separator' => 'before',
					'options' => array(
						'none' => esc_html__( 'Hide', 'cargo-trucking' ),
						'flex' => esc_html__( 'Show', 'cargo-trucking' ),
					),
					'selectors' => array(
						':root' => '--' . $this->get_control_prefix_parameter( $key, 'icon_visibility' ) . ': {{VALUE}};',
					),
				)
			)
		);

		$this->add_var_group_control(
			$this->get_control_name_parameter( $key, 'icon' ),
			Settings_Tab_Base::VAR_TYPOGRAPHY,
			array_merge_recursive(
				$default_args,
				array(
					'exclude' => array(
						'text_transform',
						'font_style',
						'text_decoration',
						'line_height',
						'letter_spacing',
						'word_spacing',
					),
					'condition' => array(
						$this->get_control_id_parameter( $key, 'icon_visibility' ) => 'flex',
					),
				)
			)
		);

		$this->add_control(
			$this->get_control_name_parameter( $key, 'icon_color' ),
			array_merge_recursive(
				$default_args,
				array(
					'label' => esc_html__( 'Color', 'cargo-trucking' ),
					'type' => Controls_Manager::COLOR,
					'dynamic' => array(),
					'selectors' => array(
						':root' => '--' . $this->get_control_prefix_parameter( $key, 'icon_color' ) . ': {{VALUE}};',
					),
					'condition' => array(
						$this->get_control_id_parameter( $key, 'icon_visibility' ) => 'flex',
					),
				)
			)
		);

		$this->add_responsive_control(
			$this->get_control_name_parameter( $key, 'icon_horizontal_alignment' ),
			array_merge_recursive(
				$default_args,
				array(
					'label' => esc_html__( 'Horizontal Alignment', 'cargo-trucking' ),
					'label_block' => false,
					'type' => Controls_Manager::CHOOSE,
					'options' => array(
						'flex-start' => array(
							'title' => esc_html__( 'Start', 'cargo-trucking' ),
							'icon' => 'eicon-h-align-left',
						),
						'center' => array(
							'title' => esc_html__( 'Center', 'cargo-trucking' ),
							'icon' => 'eicon-h-align-center',
						),
						'flex-end' => array(
							'title' => esc_html__( 'End', 'cargo-trucking' ),
							'icon' => 'eicon-h-align-right',
						),
					),
					'toggle' => true,
					'selectors' => array(
						':root' => '--' . $this->get_control_prefix_parameter( $key, 'icon_horizontal_alignment' ) . ': {{VALUE}};',
					),
					'condition' => array(
						$this->get_control_id_parameter( $key, 'icon_visibility' ) => 'flex',
					),
				)
			)
		);

		$this->add_responsive_control(
			$this->get_control_name_parameter( $key, 'icon_horizontal_offset' ),
			array_merge_recursive(
				$default_args,
				array(
					'label' => esc_html__( 'Horizontal Offset', 'cargo-trucking' ),
					'type' => Controls_Manager::SLIDER,
					'size_units' => array(
						'px',
					),
					'range' => array(
						'px' => array(
							'min' => -100,
							'max' => 100,
						),
					),
					'selectors' => array(
						':root' => '--' . $this->get_control_prefix_parameter( $key, 'icon_horizontal_offset' ) . ': {{SIZE}}{{UNIT}};',
					),
					'condition' => array(
						$this->get_control_id_parameter( $key, 'icon_visibility' ) => 'flex',
					),
				)
			)
		);

		$this->add_responsive_control(
			$this->get_control_name_parameter( $key, 'icon_vertical_alignment' ),
			array_merge_recursive(
				$default_args,
				array(
					'label' => esc_html__( 'Vertical Alignment', 'cargo-trucking' ),
					'label_block' => false,
					'type' => Controls_Manager::CHOOSE,
					'options' => array(
						'flex-start' => array(
							'title' => esc_html__( 'Start', 'cargo-trucking' ),
							'icon' => 'eicon-v-align-top',
						),
						'center' => array(
							'title' => esc_html__( 'Center', 'cargo-trucking' ),
							'icon' => ' eicon-v-align-middle',
						),
						'flex-end' => array(
							'title' => esc_html__( 'End', 'cargo-trucking' ),
							'icon' => 'eicon-v-align-bottom',
						),
					),
					'toggle' => true,
					'selectors' => array(
						':root' => '--' . $this->get_control_prefix_parameter( $key, 'icon_vertical_alignment' ) . ': {{VALUE}};',
					),
					'condition' => array(
						$this->get_control_id_parameter( $key, 'icon_visibility' ) => 'flex',
					),
				)
			)
		);

		$this->add_responsive_control(
			$this->get_control_name_parameter( $key, 'icon_vertical_offset' ),
			array_merge_recursive(
				$default_args,
				array(
					'label' => esc_html__( 'Vertical Offset', 'cargo-trucking' ),
					'type' => Controls_Manager::SLIDER,
					'size_units' => array(
						'px',
					),
					'range' => array(
						'px' => array(
							'min' => -1000,
							'max' => 1000,
						),
					),
					'selectors' => array(
						':root' => '--' . $this->get_control_prefix_parameter( $key, 'icon_vertical_offset' ) . ': {{SIZE}}{{UNIT}};',
					),
					'condition' => array(
						$this->get_control_id_parameter( $key, 'icon_visibility' ) => 'flex',
					),
				)
			)
		);
	}

}
