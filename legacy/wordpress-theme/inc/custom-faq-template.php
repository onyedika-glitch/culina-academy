<?php
/**
 * Custom FAQ page template assets and layout.
 *
 * @package GeneratePress
 */

if ( ! false && defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', 'cal_faq_template_assets' );
function cal_faq_template_assets() {
	if ( ! is_page_template( 'template-faq.php' ) ) {
		return;
	}

	wp_enqueue_style(
		'cal-faq-template',
		get_template_directory_uri() . '/assets/css/cal-faq-template.css',
		array(),
		GENERATE_VERSION
	);
}

add_filter( 'body_class', 'cal_faq_template_body_class', 20 );
function cal_faq_template_body_class( $classes ) {
	if ( ! is_page_template( 'template-faq.php' ) ) {
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
	$classes[] = 'cal-faq-template-page';

	if ( ! in_array( 'no-sidebar', $classes, true ) ) {
		$classes[] = 'no-sidebar';
	}

	if ( ! in_array( 'full-width-content', $classes, true ) ) {
		$classes[] = 'full-width-content';
	}

	return array_unique( $classes );
}

add_filter( 'generate_sidebar_layout', 'cal_faq_sidebar_layout' );
function cal_faq_sidebar_layout( $layout ) {
	if ( is_page_template( 'template-faq.php' ) ) {
		return 'no-sidebar';
	}

	return $layout;
}

add_action( 'acf/init', 'cal_register_faq_template_fields' );
function cal_register_faq_template_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_cal_faq_template',
			'title'    => 'FAQ Page Sections',
			'fields'   => array(
				array(
					'key'   => 'field_cal_faq_hero_title',
					'label' => 'Hero Title',
					'name'  => 'cal_faq_hero_title',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_cal_faq_heading',
					'label' => 'FAQ Heading',
					'name'  => 'cal_faq_heading',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_cal_faq_intro',
					'label' => 'Intro Text',
					'name'  => 'cal_faq_intro',
					'type'  => 'textarea',
					'rows'  => 4,
				),
				array(
					'key'          => 'field_cal_faq_categories',
					'label'        => 'FAQ Categories',
					'name'         => 'cal_faq_categories',
					'type'         => 'repeater',
					'layout'       => 'block',
					'button_label' => 'Add category',
					'sub_fields'   => array(
						array(
							'key'   => 'field_cal_faq_category_title',
							'label' => 'Category Title',
							'name'  => 'title',
							'type'  => 'text',
						),
						array(
							'key'          => 'field_cal_faq_questions',
							'label'        => 'Questions',
							'name'         => 'questions',
							'type'         => 'repeater',
							'layout'       => 'block',
							'button_label' => 'Add question',
							'sub_fields'   => array(
								array(
									'key'   => 'field_cal_faq_question',
									'label' => 'Question',
									'name'  => 'question',
									'type'  => 'text',
								),
								array(
									'key'          => 'field_cal_faq_answer',
									'label'        => 'Answer',
									'name'         => 'answer',
									'type'         => 'wysiwyg',
									'tabs'         => 'visual',
									'toolbar'      => 'basic',
									'media_upload' => false,
								),
							),
						),
					),
				),
				array(
					'key'   => 'field_cal_faq_cta_heading',
					'label' => 'CTA Heading',
					'name'  => 'cal_faq_cta_heading',
					'type'  => 'text',
				),
				array(
					'key'          => 'field_cal_faq_cta_text',
					'label'        => 'CTA Text',
					'name'         => 'cal_faq_cta_text',
					'type'         => 'wysiwyg',
					'tabs'         => 'visual',
					'toolbar'      => 'basic',
					'media_upload' => false,
				),
				array(
					'key'   => 'field_cal_faq_cta_primary_link',
					'label' => 'CTA Primary Link',
					'name'  => 'cal_faq_cta_primary_link',
					'type'  => 'link',
				),
				array(
					'key'   => 'field_cal_faq_cta_secondary_link',
					'label' => 'CTA Secondary Link',
					'name'  => 'cal_faq_cta_secondary_link',
					'type'  => 'link',
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'template-faq.php',
					),
				),
			),
		)
	);
}

function cal_faq_field( $name ) {
	if ( function_exists( 'get_field' ) ) {
		$value = get_field( $name );

		if ( '' !== $value && null !== $value ) {
			return $value;
		}
	}

	return '';
}
