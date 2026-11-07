<?php
/**
 * Custom Careers page template fields and assets.
 *
 * @package GeneratePress
 */

if ( ! false && defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', 'cal_careers_template_assets' );
/**
 * Load careers template assets only where needed.
 */
function cal_careers_template_assets() {
	if ( ! is_page_template( 'template-careers.php' ) ) {
		return;
	}

	wp_enqueue_style(
		'cal-careers-template',
		get_template_directory_uri() . '/assets/css/cal-careers-template.css',
		array(),
		GENERATE_VERSION
	);
}

add_filter( 'body_class', 'cal_careers_template_body_class', 20 );
/**
 * Add template-specific body classes and normalize layout classes.
 *
 * @param array $classes Existing body classes.
 * @return array
 */
function cal_careers_template_body_class( $classes ) {
	if ( ! is_page_template( 'template-careers.php' ) ) {
		return $classes;
	}

	$remove = array(
		'right-sidebar',
		'left-sidebar',
		'both-sidebars',
		'both-right',
		'both-left',
		'contained-content',
	);

	$classes = array_values( array_diff( $classes, $remove ) );
	$classes[] = 'cal-careers-template-page';

	if ( ! in_array( 'no-sidebar', $classes, true ) ) {
		$classes[] = 'no-sidebar';
	}

	if ( ! in_array( 'full-width-content', $classes, true ) ) {
		$classes[] = 'full-width-content';
	}

	return array_unique( $classes );
}

add_filter( 'generate_sidebar_layout', 'cal_careers_sidebar_layout' );
/**
 * Force the careers template to render without sidebars.
 *
 * @param string $layout Current sidebar layout.
 * @return string
 */
function cal_careers_sidebar_layout( $layout ) {
	if ( is_page_template( 'template-careers.php' ) ) {
		return 'no-sidebar';
	}

	return $layout;
}

add_action( 'acf/init', 'cal_register_careers_template_fields' );
/**
 * Register ACF fields for the careers page template.
 */
function cal_register_careers_template_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_cal_careers_template',
			'title'    => 'Careers Page Sections',
			'fields'   => array(
				array(
					'key'   => 'field_cal_careers_hero_tab',
					'label' => 'Hero Section',
					'name'  => '',
					'type'  => 'tab',
				),
				array(
					'key'   => 'field_cal_careers_hero_title',
					'label' => 'Hero Title',
					'name'  => 'cal_careers_hero_title',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_cal_careers_intro_heading',
					'label' => 'Intro Heading',
					'name'  => 'cal_careers_intro_heading',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_cal_careers_intro_text',
					'label' => 'Intro Text',
					'name'  => 'cal_careers_intro_text',
					'type'  => 'textarea',
					'rows'  => 5,
				),
				array(
					'key'   => 'field_cal_careers_features_tab',
					'label' => 'Feature Section',
					'name'  => '',
					'type'  => 'tab',
				),
				array(
					'key'   => 'field_cal_careers_features_heading',
					'label' => 'Features Heading',
					'name'  => 'cal_careers_features_heading',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_cal_careers_features_text',
					'label' => 'Features Intro Text',
					'name'  => 'cal_careers_features_text',
					'type'  => 'textarea',
					'rows'  => 4,
				),
				array(
					'key'          => 'field_cal_careers_cards',
					'label'        => 'Career Cards',
					'name'         => 'cal_careers_cards',
					'type'         => 'repeater',
					'layout'       => 'block',
					'button_label' => 'Add card',
					'sub_fields'   => array(
						array(
							'key'           => 'field_cal_careers_card_image',
							'label'         => 'Image',
							'name'          => 'image',
							'type'          => 'image',
							'return_format' => 'array',
							'preview_size'  => 'medium',
						),
						array(
							'key'   => 'field_cal_careers_card_title',
							'label' => 'Title',
							'name'  => 'title',
							'type'  => 'text',
						),
						array(
							'key'   => 'field_cal_careers_card_text',
							'label' => 'Text',
							'name'  => 'text',
							'type'  => 'textarea',
							'rows'  => 3,
						),
					),
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'template-careers.php',
					),
				),
			),
		)
	);
}

/**
 * Get a careers template field value.
 *
 * @param string $name Field name.
 * @return mixed
 */
function cal_careers_field( $name ) {
	if ( function_exists( 'get_field' ) ) {
		$value = get_field( $name );

		if ( '' !== $value && null !== $value ) {
			return $value;
		}
	}

	return '';
}
