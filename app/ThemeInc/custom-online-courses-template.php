<?php
/**
 * Online Courses page template functionality.
 *
 * @package GeneratePress
 */

if ( ! false && defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', 'cal_online_template_assets' );
function cal_online_template_assets() {
	if ( ! is_page_template( 'template-online-courses.php' ) ) {
		return;
	}

	wp_enqueue_style(
		'cal-online-courses',
		get_template_directory_uri() . '/assets/css/cal-online-courses.css',
		array(),
		GENERATE_VERSION
	);
}

add_filter( 'body_class', 'cal_online_body_class', 20 );
function cal_online_body_class( $classes ) {
	if ( ! is_page_template( 'template-online-courses.php' ) ) {
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
	$classes[] = 'cal-online-page';

	if ( ! in_array( 'no-sidebar', $classes, true ) ) {
		$classes[] = 'no-sidebar';
	}

	if ( ! in_array( 'full-width-content', $classes, true ) ) {
		$classes[] = 'full-width-content';
	}

	return array_unique( $classes );
}

add_filter( 'generate_sidebar_layout', 'cal_online_sidebar_layout' );
function cal_online_sidebar_layout( $layout ) {
	if ( is_page_template( 'template-online-courses.php' ) ) {
		return 'no-sidebar';
	}

	return $layout;
}

add_action( 'acf/init', 'cal_register_online_courses_fields' );
function cal_register_online_courses_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group( array(
		'key'      => 'group_cal_online_courses',
		'title'    => 'Online Courses Page',
		'fields'   => array(
			array(
				'key'   => 'field_cal_online_heading',
				'label' => 'Heading',
				'name'  => 'cal_online_heading',
				'type'  => 'text',
			),
			array(
				'key'   => 'field_cal_online_subtitle',
				'label' => 'Subtitle',
				'name'  => 'cal_online_subtitle',
				'type'  => 'text',
			),
			array(
				'key'          => 'field_cal_online_courses',
				'label'        => 'Online Courses',
				'name'         => 'cal_online_courses',
				'type'         => 'repeater',
				'layout'       => 'block',
				'button_label' => 'Add course',
				'sub_fields'   => array(
					array(
						'key'           => 'field_cal_online_course_image',
						'label'         => 'Image',
						'name'          => 'image',
						'type'          => 'image',
						'return_format' => 'array',
						'preview_size'  => 'medium',
					),
					array(
						'key'   => 'field_cal_online_course_title',
						'label' => 'Course Title',
						'name'  => 'title',
						'type'  => 'text',
					),
					array(
						'key'   => 'field_cal_online_course_description',
						'label' => 'Description',
						'name'  => 'description',
						'type'  => 'textarea',
						'rows'  => 6,
					),
					array(
						'key'   => 'field_cal_online_course_price',
						'label' => 'Price',
						'name'  => 'price',
						'type'  => 'text',
					),
					array(
						'key'   => 'field_cal_online_course_duration',
						'label' => 'Duration',
						'name'  => 'duration',
						'type'  => 'text',
					),
					array(
						'key'   => 'field_cal_online_course_features',
						'label' => 'Features / Notes',
						'name'  => 'features',
						'type'  => 'textarea',
						'rows'  => 3,
					),
					array(
						'key'   => 'field_cal_online_course_link_url',
						'label' => 'Link URL',
						'name'  => 'link_url',
						'type'  => 'url',
					),
					array(
						'key'   => 'field_cal_online_course_link_text',
						'label' => 'Link Text',
						'name'  => 'link_text',
						'type'  => 'text',
					),
				),
			),
		),
		'location' => array(
			array(
				array(
					'param'    => 'page_template',
					'operator' => '==',
					'value'    => 'template-online-courses.php',
				),
			),
		),
	) );
}

function cal_online_field( $name ) {
	if ( function_exists( 'get_field' ) ) {
		$value = get_field( $name );

		if ( '' !== $value && null !== $value ) {
			return $value;
		}
	}

	return '';
}

function cal_online_image_url( $image ) {
	if ( function_exists( 'cal_image_url' ) ) {
		return cal_image_url( $image );
	}

	if ( is_array( $image ) && ! empty( $image['url'] ) ) {
		return $image['url'];
	}

	if ( is_string( $image ) && '' !== $image ) {
		return $image;
	}

	return '';
}
