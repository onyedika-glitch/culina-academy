<?php
/**
 * Custom Cooking Courses page template fields and assets.
 *
 * @package GeneratePress
 */

if ( ! false && defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', 'cal_cooking_courses_template_assets' );
/**
 * Load cooking courses template assets only where needed.
 */
function cal_cooking_courses_template_assets() {
	if ( ! is_page_template( 'template-cooking-courses.php' ) ) {
		return;
	}

	wp_enqueue_style(
		'cal-cooking-courses-template',
		get_template_directory_uri() . '/assets/css/cal-cooking-courses-template.css',
		array(),
		GENERATE_VERSION
	);
}

add_filter( 'body_class', 'cal_cooking_courses_template_body_class', 20 );
/**
 * Add template-specific body classes and normalize layout classes.
 *
 * @param array $classes Existing body classes.
 * @return array
 */
function cal_cooking_courses_template_body_class( $classes ) {
	if ( ! is_page_template( 'template-cooking-courses.php' ) ) {
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
	$classes[] = 'cal-cooking-courses-template-page';

	if ( ! in_array( 'no-sidebar', $classes, true ) ) {
		$classes[] = 'no-sidebar';
	}

	if ( ! in_array( 'full-width-content', $classes, true ) ) {
		$classes[] = 'full-width-content';
	}

	return array_unique( $classes );
}

add_filter( 'generate_sidebar_layout', 'cal_cooking_courses_template_sidebar_layout' );
/**
 * Force the cooking courses template to render without sidebars.
 *
 * @param string $layout Current sidebar layout.
 * @return string
 */
function cal_cooking_courses_template_sidebar_layout( $layout ) {
	if ( is_page_template( 'template-cooking-courses.php' ) ) {
		return 'no-sidebar';
	}

	return $layout;
}

add_action( 'acf/init', 'cal_register_cooking_courses_template_fields' );
/**
 * Register ACF fields for the cooking courses page template.
 */
function cal_register_cooking_courses_template_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_cal_cooking_courses_template',
			'title'    => 'Cooking Courses Page Sections',
			'fields'   => array(
				array(
					'key'   => 'field_cal_cooking_courses_hero_tab',
					'label' => 'Hero Section',
					'name'  => '',
					'type'  => 'tab',
				),
				array(
					'key'           => 'field_cal_cooking_courses_hero_title',
					'label'         => 'Hero Title',
					'name'          => 'cal_cooking_courses_hero_title',
					'type'          => 'text',
					'default_value' => '',
				),
				array(
					'key'   => 'field_cal_cooking_courses_hero_text',
					'label' => 'Hero Text',
					'name'  => 'cal_cooking_courses_hero_text',
					'type'  => 'textarea',
					'rows'  => 3,
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'template-cooking-courses.php',
					),
				),
			),
		)
	);
}

/**
 * Get a cooking courses template field value.
 *
 * @param string $name Field name.
 * @return mixed
 */
function cal_cooking_courses_field( $name ) {
	if ( function_exists( 'get_field' ) ) {
		$value = get_field( $name );

		if ( '' !== $value && null !== $value ) {
			return $value;
		}
	}

	return '';
}
