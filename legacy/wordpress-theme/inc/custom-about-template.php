<?php
/**
 * About Us page template functionality.
 *
 * @package GeneratePress
 */

if ( ! false && defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', 'cal_about_template_assets' );
/**
 * Load about template assets only where needed.
 */
function cal_about_template_assets() {
	if ( ! is_page_template( 'template-about.php' ) ) {
		return;
	}

	wp_enqueue_style(
		'cal-about-template',
		get_template_directory_uri() . '/assets/css/cal-about-template.css',
		array(),
		GENERATE_VERSION
	);

	wp_enqueue_script(
		'cal-about-template',
		get_template_directory_uri() . '/assets/js/cal-about-template.js',
		array(),
		GENERATE_VERSION,
		true
	);
}

add_filter( 'body_class', 'cal_about_template_body_class', 20 );
/**
 * Add template-specific body classes and normalize layout classes.
 */
function cal_about_template_body_class( $classes ) {
	if ( ! is_page_template( 'template-about.php' ) ) {
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
	$classes[] = 'cal-about-template-page';

	if ( ! in_array( 'no-sidebar', $classes, true ) ) {
		$classes[] = 'no-sidebar';
	}

	if ( ! in_array( 'full-width-content', $classes, true ) ) {
		$classes[] = 'full-width-content';
	}

	return array_unique( $classes );
}

add_filter( 'generate_sidebar_layout', 'cal_about_sidebar_layout' );
/**
 * Force the about template to render without sidebars.
 */
function cal_about_sidebar_layout( $layout ) {
	if ( is_page_template( 'template-about.php' ) ) {
		return 'no-sidebar';
	}

	return $layout;
}

add_action( 'acf/init', 'cal_register_about_template_fields' );
/**
 * Register ACF fields for the about page template.
 */
function cal_register_about_template_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_cal_about_template',
			'title'    => 'About Page Sections',
			'fields'   => array(
				array(
					'key'   => 'field_cal_about_hero_tab',
					'label' => 'Hero Section',
					'name'  => '',
					'type'  => 'tab',
				),
				array(
					'key'   => 'field_cal_about_hero_title',
					'label' => 'Hero Title',
					'name'  => 'cal_about_hero_title',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_cal_about_hero_subtitle',
					'label' => 'Hero Subtitle',
					'name'  => 'cal_about_hero_subtitle',
					'type'  => 'text',
				),
				array(
					'key'           => 'field_cal_about_hero_image',
					'label'         => 'Hero Background Image',
					'name'          => 'cal_about_hero_image',
					'type'          => 'image',
					'return_format' => 'url',
					'preview_size'  => 'large',
				),
				array(
					'key'   => 'field_cal_about_why_tab',
					'label' => 'Why Choose Section',
					'name'  => '',
					'type'  => 'tab',
				),
				array(
					'key'   => 'field_cal_about_why_heading',
					'label' => 'Why Choose Heading',
					'name'  => 'cal_about_why_heading',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_cal_about_why_text',
					'label' => 'Why Choose Text',
					'name'  => 'cal_about_why_text',
					'type'  => 'textarea',
					'rows'  => 5,
				),
				array(
					'key'   => 'field_cal_about_features_tab',
					'label' => 'Feature Cards',
					'name'  => '',
					'type'  => 'tab',
				),
				array(
					'key'          => 'field_cal_about_features',
					'label'        => 'Feature Cards',
					'name'         => 'cal_about_features',
					'type'         => 'repeater',
					'layout'       => 'block',
					'button_label' => 'Add card',
					'sub_fields'   => array(
						array(
							'key'           => 'field_cal_about_feature_icon',
							'label'         => 'Icon',
							'name'          => 'icon',
							'type'          => 'image',
							'return_format' => 'array',
							'preview_size'  => 'thumbnail',
						),
						array(
							'key'   => 'field_cal_about_feature_title',
							'label' => 'Title',
							'name'  => 'title',
							'type'  => 'text',
						),
						array(
							'key'   => 'field_cal_about_feature_text',
							'label' => 'Text',
							'name'  => 'text',
							'type'  => 'textarea',
							'rows'  => 6,
						),
					),
				),
				array(
					'key'   => 'field_cal_about_story_tab',
					'label' => 'About Story Section',
					'name'  => '',
					'type'  => 'tab',
				),
				array(
					'key'   => 'field_cal_about_story_heading',
					'label' => 'Story Heading',
					'name'  => 'cal_about_story_heading',
					'type'  => 'text',
				),
				array(
					'key'          => 'field_cal_about_story_paragraphs',
					'label'        => 'Story Paragraphs',
					'name'         => 'cal_about_story_paragraphs',
					'type'         => 'repeater',
					'layout'       => 'table',
					'button_label' => 'Add paragraph',
					'sub_fields'   => array(
						array(
							'key'   => 'field_cal_about_story_paragraph',
							'label' => 'Paragraph Text',
							'name'  => 'paragraph',
							'type'  => 'textarea',
							'rows'  => 3,
						),
					),
				),
				array(
					'key'   => 'field_cal_about_story_video',
					'label' => 'Story Video URL',
					'name'  => 'cal_about_story_video',
					'type'  => 'oembed',
				),
				array(
					'key'   => 'field_cal_about_philosophy_tab',
					'label' => 'Culinary Philosophy',
					'name'  => '',
					'type'  => 'tab',
				),
				array(
					'key'           => 'field_cal_about_philosophy_icon',
					'label'         => 'Icon',
					'name'          => 'cal_about_philosophy_icon',
					'type'          => 'image',
					'return_format' => 'array',
					'preview_size'  => 'thumbnail',
				),
				array(
					'key'   => 'field_cal_about_philosophy_heading',
					'label' => 'Heading',
					'name'  => 'cal_about_philosophy_heading',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_cal_about_philosophy_text',
					'label' => 'Text',
					'name'  => 'cal_about_philosophy_text',
					'type'  => 'textarea',
					'rows'  => 6,
				),
				array(
					'key'   => 'field_cal_about_training_tab',
					'label' => 'Training Method',
					'name'  => '',
					'type'  => 'tab',
				),
				array(
					'key'           => 'field_cal_about_training_icon',
					'label'         => 'Icon',
					'name'          => 'cal_about_training_icon',
					'type'          => 'image',
					'return_format' => 'array',
					'preview_size'  => 'thumbnail',
				),
				array(
					'key'   => 'field_cal_about_training_heading',
					'label' => 'Heading',
					'name'  => 'cal_about_training_heading',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_cal_about_training_text',
					'label' => 'Text',
					'name'  => 'cal_about_training_text',
					'type'  => 'textarea',
					'rows'  => 6,
				),
				array(
					'key'   => 'field_cal_about_pathways_tab',
					'label' => 'Culinary Pathways',
					'name'  => '',
					'type'  => 'tab',
				),
				array(
					'key'           => 'field_cal_about_pathways_icon',
					'label'         => 'Icon',
					'name'          => 'cal_about_pathways_icon',
					'type'          => 'image',
					'return_format' => 'array',
					'preview_size'  => 'thumbnail',
				),
				array(
					'key'   => 'field_cal_about_pathways_heading',
					'label' => 'Heading',
					'name'  => 'cal_about_pathways_heading',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_cal_about_pathways_text',
					'label' => 'Text',
					'name'  => 'cal_about_pathways_text',
					'type'  => 'textarea',
					'rows'  => 6,
				),
				array(
					'key'   => 'field_cal_about_cta_tab',
					'label' => 'CTA Section',
					'name'  => '',
					'type'  => 'tab',
				),
				array(
					'key'   => 'field_cal_about_cta_heading',
					'label' => 'CTA Heading',
					'name'  => 'cal_about_cta_heading',
					'type'  => 'text',
				),
				array(
					'key'           => 'field_cal_about_cta_button_text',
					'label'         => 'Button Text',
					'name'          => 'cal_about_cta_button_text',
					'type'          => 'text',
				),
				array(
					'key'           => 'field_cal_about_cta_button_url',
					'label'         => 'Button URL',
					'name'          => 'cal_about_cta_button_url',
					'type'          => 'text',
				),
				array(
					'key'   => 'field_cal_about_reviews_tab',
					'label' => 'Reviews Section',
					'name'  => '',
					'type'  => 'tab',
				),
				array(
					'key'   => 'field_cal_about_reviews_heading',
					'label' => 'Reviews Heading',
					'name'  => 'cal_about_reviews_heading',
					'type'  => 'text',
				),
				array(
					'key'          => 'field_cal_about_reviews',
					'label'        => 'Reviews',
					'name'         => 'cal_about_reviews',
					'type'         => 'repeater',
					'layout'       => 'block',
					'button_label' => 'Add review',
					'sub_fields'   => array(
						array(
							'key'   => 'field_cal_about_review_name',
							'label' => 'Name',
							'name'  => 'name',
							'type'  => 'text',
						),
						array(
							'key'   => 'field_cal_about_review_description',
							'label' => 'Description',
							'name'  => 'description',
							'type'  => 'text',
						),
						array(
							'key'   => 'field_cal_about_review_image_url',
							'label' => 'Image URL',
							'name'  => 'image_url',
							'type'  => 'url',
						),
						array(
							'key'   => 'field_cal_about_review_video_url',
							'label' => 'Video URL',
							'name'  => 'video_url',
							'type'  => 'url',
						),
					),
				),
				array(
					'key'   => 'field_cal_about_reviews_button_text',
					'label' => 'Reviews Button Text',
					'name'  => 'cal_about_reviews_button_text',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_cal_about_reviews_button_url',
					'label' => 'Reviews Button URL',
					'name'  => 'cal_about_reviews_button_url',
					'type'  => 'text',
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'template-about.php',
					),
				),
			),
		)
	);
}

add_action( 'init', 'cal_register_about_ui_strings' );
/**
 * Register small About template UI labels for multilingual plugins.
 */
function cal_register_about_ui_strings() {
	$strings = array(
		'Read More',
		'Read Less',
		'Previous review',
		'Next review',
		'Culina Academy',
		'The Culina Academy',
	);

	foreach ( $strings as $string ) {
		if ( function_exists( 'pll_register_string' ) ) {
			pll_register_string( 'about_ui_' . md5( $string ), $string, 'Culina Academy About UI' );
		}
	}
}

/**
 * Translate small About template UI labels.
 *
 * @param string $string Source UI string.
 * @return string
 */
function cal_about_ui_string( $string ) {
	if ( function_exists( 'pll__' ) ) {
		return pll__( $string );
	}

	return __( $string, 'generatepress' );
}

/**
 * Get an about template field value.
 */
function cal_about_field( $name ) {
	if ( function_exists( 'get_field' ) ) {
		$value = get_field( $name );

		if ( cal_about_value_has_content( $value ) ) {
			return $value;
		}

		$source_id = cal_about_source_page_id();
		if ( $source_id ) {
			$source_value = get_field( $name, $source_id );

			if ( cal_about_value_has_content( $source_value ) ) {
				return $source_value;
			}
		}
	}

	return '';
}

/**
 * Check whether a field value contains visible/editable content.
 *
 * @param mixed $value Field value.
 * @return bool
 */
function cal_about_value_has_content( $value ) {
	if ( false === $value || null === $value ) {
		return false;
	}

	if ( is_array( $value ) ) {
		foreach ( $value as $item ) {
			if ( cal_about_value_has_content( $item ) ) {
				return true;
			}
		}

		return false;
	}

	return '' !== trim( (string) $value );
}

/**
 * Get the default-language source page for the current translated About page.
 *
 * @return int
 */
function cal_about_source_page_id() {
	if ( ! function_exists( 'pll_get_post_language' ) ) {
		return 0;
	}

	$post_id = get_queried_object_id();
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	if ( ! $post_id ) {
		return 0;
	}

	$lang         = pll_get_post_language( $post_id, 'slug' );
	$default_lang = function_exists( 'pll_default_language' ) ? pll_default_language( 'slug' ) : '';

	if ( ! $lang || ( $default_lang && $lang === $default_lang ) ) {
		return 0;
	}

	$translations = function_exists( 'pll_get_post_translations' ) ? pll_get_post_translations( $post_id ) : array();
	if ( ! empty( $translations ) && is_array( $translations ) && $default_lang && ! empty( $translations[ $default_lang ] ) && (int) $translations[ $default_lang ] !== (int) $post_id ) {
		return (int) $translations[ $default_lang ];
	}

	if ( ! empty( $translations ) && is_array( $translations ) ) {
		foreach ( $translations as $translation_id ) {
			if ( (int) $translation_id !== (int) $post_id ) {
				return (int) $translation_id;
			}
		}
	}

	if ( function_exists( 'cal_default_language_page_source_for_template' ) ) {
		return cal_default_language_page_source_for_template( $post_id );
	}

	return cal_about_default_source_page_id( $post_id, $default_lang );
}

/**
 * Fallback source lookup when the shared helper is unavailable.
 *
 * @param int    $post_id      Current page ID.
 * @param string $default_lang Default language slug.
 * @return int
 */
function cal_about_default_source_page_id( $post_id, $default_lang ) {
	$template = get_page_template_slug( $post_id );
	if ( ! $template || 'default' === $template || ! $default_lang ) {
		return 0;
	}

	$page_ids = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'meta_key'       => '_wp_page_template',
			'meta_value'     => $template,
		)
	);

	foreach ( $page_ids as $page_id ) {
		if ( (int) $page_id !== (int) $post_id && $default_lang === pll_get_post_language( $page_id, 'slug' ) ) {
			return (int) $page_id;
		}
	}

	return 0;
}

/**
 * Normalize image values to URLs.
 */
function cal_about_image_url( $image ) {
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

/**
 * Render an info section block (icon + heading + text).
 */
function cal_about_render_info_section( $icon_field, $heading_field, $text_field ) {
	$icon    = cal_about_image_url( cal_about_field( $icon_field ) );
	$heading = cal_about_field( $heading_field );
	$text    = cal_about_field( $text_field );

	?>
	<article class="cal-about-info-card">
		<?php if ( $icon ) : ?>
			<div class="cal-about-info-icon">
				<img src="<?php echo esc_attr( $icon ); ?>" alt="" loading="lazy">
			</div>
		<?php endif; ?>
		<?php if ( $heading ) : ?>
			<h3><?php echo esc_html( $heading ); ?></h3>
		<?php endif; ?>
		<?php if ( $text ) : ?>
			<p><?php echo esc_html( $text ); ?></p>
		<?php endif; ?>
	</article>
	<?php
}
