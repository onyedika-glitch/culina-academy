<?php
/**
 * Custom International Students page template fields and assets.
 *
 * @package GeneratePress
 */

if ( ! false && defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', 'cal_international_students_template_assets' );
/**
 * Load international students template assets only where needed.
 */
function cal_international_students_template_assets() {
	if ( ! is_page_template( 'template-international-students.php' ) ) {
		return;
	}

	wp_enqueue_style(
		'cal-international-students-template',
		get_template_directory_uri() . '/assets/css/cal-international-students-template.css',
		array(),
		GENERATE_VERSION
	);
}

add_filter( 'body_class', 'cal_international_students_template_body_class', 20 );
/**
 * Add template-specific body classes and normalize layout classes.
 *
 * @param array $classes Existing body classes.
 * @return array
 */
function cal_international_students_template_body_class( $classes ) {
	if ( ! is_page_template( 'template-international-students.php' ) ) {
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
	$classes[] = 'cal-international-students-template-page';

	if ( ! in_array( 'no-sidebar', $classes, true ) ) {
		$classes[] = 'no-sidebar';
	}

	if ( ! in_array( 'full-width-content', $classes, true ) ) {
		$classes[] = 'full-width-content';
	}

	return array_unique( $classes );
}

add_filter( 'generate_sidebar_layout', 'cal_international_students_sidebar_layout' );
/**
 * Force the international students template to render without sidebars.
 *
 * @param string $layout Current sidebar layout.
 * @return string
 */
function cal_international_students_sidebar_layout( $layout ) {
	if ( is_page_template( 'template-international-students.php' ) ) {
		return 'no-sidebar';
	}

	return $layout;
}

add_action( 'acf/init', 'cal_register_international_students_template_fields' );
/**
 * Register ACF fields for the international students page template.
 */
function cal_register_international_students_template_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_cal_international_students_template',
			'title'    => 'International Students Page Sections',
			'fields'   => array(
				array(
					'key'   => 'field_cal_international_students_hero_tab',
					'label' => 'Hero Section',
					'name'  => '',
					'type'  => 'tab',
				),
				array(
					'key'   => 'field_cal_international_students_hero_title',
					'label' => 'Hero Title',
					'name'  => 'cal_international_students_hero_title',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_cal_international_students_logo',
					'label' => 'Top Logo',
					'name'  => 'cal_international_students_logo',
					'type'  => 'image',
					'return_format' => 'array',
					'preview_size' => 'medium',
				),
				array(
					'key'   => 'field_cal_international_students_intro_heading',
					'label' => 'Intro Heading',
					'name'  => 'cal_international_students_intro_heading',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_cal_international_students_intro_highlight',
					'label' => 'Intro Highlight Text',
					'name'  => 'cal_international_students_intro_highlight',
					'type'  => 'text',
				),
				array(
					'key'          => 'field_cal_international_students_intro_paragraphs',
					'label'        => 'Intro Paragraphs',
					'name'         => 'cal_international_students_intro_paragraphs',
					'type'         => 'repeater',
					'layout'       => 'block',
					'button_label' => 'Add intro paragraph',
					'sub_fields'   => array(
						array(
							'key'   => 'field_cal_international_students_intro_paragraph',
							'label' => 'Paragraph',
							'name'  => 'paragraph',
							'type'  => 'textarea',
							'rows'  => 3,
						),
					),
				),
				array(
					'key'   => 'field_cal_international_students_sections_tab',
					'label' => 'Content Sections',
					'name'  => '',
					'type'  => 'tab',
				),
				array(
					'key'          => 'field_cal_international_students_sections',
					'label'        => 'Sections',
					'name'         => 'cal_international_students_sections',
					'type'         => 'repeater',
					'layout'       => 'block',
					'button_label' => 'Add section',
					'sub_fields'   => array(
						array(
							'key'   => 'field_cal_international_students_section_heading',
							'label' => 'Heading',
							'name'  => 'heading',
							'type'  => 'text',
						),
						array(
							'key'          => 'field_cal_international_students_section_paragraphs',
							'label'        => 'Paragraphs',
							'name'         => 'paragraphs',
							'type'         => 'repeater',
							'layout'       => 'table',
							'button_label' => 'Add paragraph',
							'sub_fields'   => array(
								array(
									'key'   => 'field_cal_international_students_section_paragraph',
									'label' => 'Paragraph',
									'name'  => 'paragraph',
									'type'  => 'textarea',
									'rows'  => 3,
								),
							),
						),
						array(
							'key'          => 'field_cal_international_students_section_bullets',
							'label'        => 'Bullet Points',
							'name'         => 'bullets',
							'type'         => 'repeater',
							'layout'       => 'table',
							'button_label' => 'Add bullet',
							'sub_fields'   => array(
								array(
									'key'   => 'field_cal_international_students_section_bullet',
									'label' => 'Bullet',
									'name'  => 'bullet',
									'type'  => 'text',
								),
							),
						),
					),
				),
				array(
					'key'   => 'field_cal_international_students_cta_tab',
					'label' => 'CTA',
					'name'  => '',
					'type'  => 'tab',
				),
				array(
					'key'           => 'field_cal_international_students_cta_label',
					'label'         => 'CTA Label',
					'name'          => 'cal_international_students_cta_label',
					'type'          => 'text',
					'default_value' => '',
				),
				array(
					'key'           => 'field_cal_international_students_cta_url',
					'label'         => 'CTA URL',
					'name'          => 'cal_international_students_cta_url',
					'type'          => 'url',
					'default_value' => '',
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'template-international-students.php',
					),
				),
			),
		)
	);
}

/**
 * Get an international students template field value.
 *
 * @param string $name Field name.
 * @return mixed
 */
function cal_international_students_field( $name ) {
	if ( function_exists( 'get_field' ) ) {
		$value = get_field( $name );

		if ( '' !== $value && null !== $value ) {
			return $value;
		}
	}

	return '';
}
