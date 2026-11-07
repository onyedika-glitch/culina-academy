<?php

if ( ! false && defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Force admin interface to always use English (user's WordPress locale),
 * regardless of which Polylang language is being edited.
 */
add_filter( 'pll_admin_user_lang', '__return_false' );

/**
 * Apply Polylang string translation to ACF option-page text values only.
 * Polylang Pro's own ACF module handles post/page ACF fields natively.
 * Options pages are NOT covered by Polylang's ACF module, so we do it here.
 */
add_filter( 'acf/format_value', 'cal_acf_format_value_polylang', 20, 3 );
function cal_acf_format_value_polylang( $value, $post_id, $field ) {
	if ( 'option' !== $post_id || ! function_exists( 'pll__' ) || ! is_string( $value ) || '' === $value ) {
		return $value;
	}
	$text_types = array( 'text', 'textarea', 'wysiwyg', 'email', 'url' );
	if ( ! in_array( $field['type'], $text_types, true ) ) {
		return $value;
	}
	$translated = pll__( $value );
	return $translated !== $value ? $translated : $value;
}

/**
 * Register ACF options-page text fields as Polylang translatable strings.
 */
add_action( 'init', 'cal_polylang_register_option_strings' );
function cal_polylang_register_option_strings() {
	if ( ! function_exists( 'pll_register_string' ) ) {
		return;
	}

	$strings = array(
		'cal_footer_cta_title'       => array( 'Header & Footer', false ),
		'cal_footer_gallery_heading' => array( 'Header & Footer', false ),
		'cal_footer_about_heading'   => array( 'Header & Footer', false ),
		'cal_footer_about'           => array( 'Header & Footer', true ),
		'cal_footer_contact_heading' => array( 'Header & Footer', false ),
		'cal_footer_address'         => array( 'Header & Footer', true ),
		'cal_footer_policies_heading'=> array( 'Header & Footer', false ),
		'cal_footer_bottom_line'     => array( 'Header & Footer', true ),
	);

	foreach ( $strings as $key => list( $group, $multiline ) ) {
		$value = get_field( $key, 'option' );
		if ( is_string( $value ) && '' !== $value ) {
			pll_register_string( $key, $value, $group, $multiline );
		}
	}

	$policy_links = get_field( 'cal_footer_policy_links', 'option' );
	if ( is_array( $policy_links ) ) {
		foreach ( $policy_links as $i => $item ) {
			$link = isset( $item['link'] ) && is_array( $item['link'] ) ? $item['link'] : $item;
			if ( ! empty( $link['title'] ) && is_string( $link['title'] ) ) {
				pll_register_string( "cal_footer_policy_link_{$i}", $link['title'], 'Footer Policy Links', false );
			}
		}
	}
}

/**
 * Register form placeholder strings and nav menu item titles as Polylang strings.
 */
add_action( 'init', 'cal_polylang_register_form_and_menu_strings', 20 );
function cal_polylang_register_form_and_menu_strings() {
	if ( ! function_exists( 'pll_register_string' ) ) {
		return;
	}

	$form_strings = array(
		'Your Name'        => 'Home Page Enquiry Form',
		'Email'            => 'Home Page Enquiry Form',
		'Phone / WhatsApp' => 'Home Page Enquiry Form',
		'Your Name *'      => 'Contact Page Form',
		'Your Email *'     => 'Contact Page Form',
		'Your Phone'       => 'Contact Page Form',
		'Your Message *'   => 'Contact Page Form',
		'SUBMIT NOW'       => 'Contact Page Form',
	);

	foreach ( $form_strings as $string => $group ) {
		pll_register_string( $string, $string, $group, false );
	}

	$menu_items = wp_get_nav_menu_items( 28 );
	if ( is_array( $menu_items ) ) {
		foreach ( $menu_items as $item ) {
			$title = trim( $item->title );
			if ( '' !== $title ) {
				pll_register_string( 'nav_' . $item->ID, $title, 'Primary Menu', false );
			}
		}
	}
}

/**
 * Register home page section strings as Polylang translatable strings.
 */
add_action( 'init', 'cal_polylang_register_home_page_strings', 22 );
function cal_polylang_register_home_page_strings() {
	if ( ! function_exists( 'pll_register_string' ) ) {
		return;
	}

	// Home slider strings (from ACF fields)
	$home_slider_strings = array(
		'START YOUR CULINARY JOURNEY' => 'Home Page Slider',
		'COURSE ENQUIRY' => 'Home Page Slider',
		'ENQUIRE NOW' => 'Home Page Slider',
	);

	// Home Why Choose section
	$why_choose_strings = array(
		'Why Choose Us?' => 'Home Page Why Choose',
		'A CHOICE THAT MAKES THE DIFFERENCE.' => 'Home Page Why Choose',
		'ABOUT CULINA ACADEMY' => 'Home Page Why Choose',
	);

	// Home Enquiry section
	$enquiry_strings = array(
		'Our admissions team will help you choose the right professional chef programme.' => 'Home Page Enquiry',
	);

	// Combine all strings
	$all_strings = array_merge( $home_slider_strings, $why_choose_strings, $enquiry_strings );

	foreach ( $all_strings as $string => $group ) {
		pll_register_string( $string, $string, $group, false );
	}
}

/**
 * Register course single-page UI labels as Polylang translatable strings.
 */
add_action( 'init', 'cal_polylang_register_course_ui_strings', 25 );
function cal_polylang_register_course_ui_strings() {
	if ( ! function_exists( 'pll_register_string' ) ) {
		return;
	}
	$strings = array(
		'Home'              => 'Breadcrumbs',
		'Breadcrumb'        => 'Breadcrumbs',
		'Cooking Courses'   => 'Course Single Page',
		'Location'          => 'Course Single Page',
		'Duration'          => 'Course Single Page',
		'Price'             => 'Course Single Page',
		'Download programme' => 'Course Single Page',
		'Enquire now'        => 'Course Single Page',
		'Reserve your course now' => 'Course Single Page',
		'Read More Reviews' => 'Home Page',
		'Previous slide' => 'Home Page Slider',
		'Next slide' => 'Home Page Slider',
	);
	foreach ( $strings as $string => $group ) {
		pll_register_string( $string, $string, $group, false );
	}

	// Register dynamic course reserve text strings
	$courses = get_posts( array(
		'post_type'      => 'ca_course',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
	) );

	if ( is_array( $courses ) ) {
		foreach ( $courses as $course ) {
			$reserve_text = get_post_meta( $course->ID, 'ca_reserve_text', true );
			if ( is_string( $reserve_text ) && '' !== trim( $reserve_text ) ) {
				// Register without unique ID - Polylang will match on string content
				pll_register_string( 
					'course-reserve-text', 
					$reserve_text, 
					'Course Reserve Text - ' . $course->post_title, 
					true 
				);
			}
		}
	}
}

/**
 * Register course enrollment form UI labels as Polylang translatable strings.
 */
add_action( 'init', 'cal_register_course_enrollment_strings', 25 );
function cal_register_course_enrollment_strings() {
	if ( ! function_exists( 'pll_register_string' ) ) {
		return;
	}
	$strings = array(
		'Application Form' => 'Course Enrollment',
		'Enroll for Culina Academy Cooking Courses or simply ask for more information about Certificates and Diploma.' => 'Course Enrollment',
		'Complete the Application Form below. Our Educational Department will schedule an informal telephone interview prior to being accepted onto the course. The purpose of the telephone interview is to establish your suitability for the course and to answer any questions you may have regarding the course content, prior to completing the enrollment process.' => 'Course Enrollment',
		'Name *' => 'Course Enrollment',
		'Surname *' => 'Course Enrollment',
		'Email *' => 'Course Enrollment',
		'Phone Number *' => 'Course Enrollment',
		'Address *' => 'Course Enrollment',
		'City *' => 'Course Enrollment',
		'Postcode *' => 'Course Enrollment',
		'Which nationality is your passport from? *' => 'Course Enrollment',
		'Do you require a visa? *' => 'Course Enrollment',
		'Yes' => 'Course Enrollment',
		'No' => 'Course Enrollment',
		'How did you hear about us? *' => 'Course Enrollment',
		'Choose year *' => 'Course Enrollment',
		'Choose year' => 'Course Enrollment',
		'Choose month *' => 'Course Enrollment',
		'Choose month' => 'Course Enrollment',
		'Course Preferences' => 'Course Enrollment',
		'Cookery Ability *' => 'Course Enrollment',
		'Beginner' => 'Course Enrollment',
		'Basic Home Cook' => 'Course Enrollment',
		'Confident Home Cook' => 'Course Enrollment',
		'Professional Cook' => 'Course Enrollment',
		'Which Course/s are you interested in? *' => 'Course Enrollment',
		'Why do you want to join this/these course/s? *' => 'Course Enrollment',
		'I agree consent to Culina Academy collecting my details through this form. Please read our Cookies Policy and our Privacy Policy.' => 'Course Enrollment',
		'Submit Application' => 'Course Enrollment',
	);
	$multiline = array(
		'Enroll for Culina Academy Cooking Courses or simply ask for more information about Certificates and Diploma.',
		'Complete the Application Form below. Our Educational Department will schedule an informal telephone interview prior to being accepted onto the course. The purpose of the telephone interview is to establish your suitability for the course and to answer any questions you may have regarding the course content, prior to completing the enrollment process.',
		'I agree consent to Culina Academy collecting my details through this form. Please read our Cookies Policy and our Privacy Policy.',
	);
	foreach ( $strings as $string => $group ) {
		pll_register_string( $string, $string, $group, in_array( $string, $multiline, true ) );
	}
}

/**
 * Clear Polylang string cache on theme/plugin update to ensure fresh registration.
 */
add_action( 'admin_init', 'cal_clear_polylang_cache' );
function cal_clear_polylang_cache() {
	if ( function_exists( 'pll_cache_clear' ) ) {
		pll_cache_clear();
	}
}

/**
 * Translate an ACF option value via Polylang.
 */
function cal_option_pll( $name, $fallback = '' ) {
	$value = cal_option( $name, $fallback );
	if ( function_exists( 'pll__' ) && is_string( $value ) && '' !== $value ) {
		$translated = pll__( $value );
		if ( $translated !== $value ) {
			return $translated;
		}
	}
	return $value;
}

/**
 * Render a link array with translated title via Polylang.
 */
function cal_render_link_pll( $link, $class = '' ) {
	if ( empty( $link ) || empty( $link['url'] ) ) {
		return;
	}

	$url    = $link['url'];
	$title  = ! empty( $link['title'] ) ? $link['title'] : $url;
	$target = ! empty( $link['target'] ) ? $link['target'] : '';

	if ( function_exists( 'pll__' ) ) {
		$translated = pll__( $title );
		if ( $translated !== $title ) {
			$title = $translated;
		}
	}
	// Translate internal URLs to current language
	if ( function_exists( 'pll_current_language' ) && pll_current_language() !== 'en' && function_exists( 'cal_translate_menu_url' ) ) {
		$translated_url = cal_translate_menu_url( $url, pll_current_language() );
		if ( $translated_url !== $url ) {
			$url = $translated_url;
		}
	}
	?>
	<a href="<?php echo esc_url( $url ); ?>"<?php echo $class ? ' class="' . esc_attr( $class ) . '"' : ''; ?><?php echo $target ? ' target="' . esc_attr( $target ) . '" rel="noopener"' : ''; ?>><?php echo esc_html( $title ); ?></a>
	<?php
}

/**
 * Get Polylang active languages for the header switcher.
 */
function cal_get_polylang_languages() {
	if ( ! function_exists( 'pll_the_languages' ) ) {
		return array();
	}

	$langs = pll_the_languages( array( 'raw' => true ) );

	if ( empty( $langs ) || ! is_array( $langs ) ) {
		return array();
	}

	$languages = array();
	foreach ( $langs as $code => $lang ) {
		$url = $lang['url'] ?? '';
		$url = cal_fix_lang_switcher_url( $url, $code );
		$languages[] = array(
			'url'   => $url,
			'label' => $lang['name'] ?? $code,
			'code'  => $code,
			'flag'  => $lang['flag'] ?? '',
		);
	}

	return $languages;
}

function cal_fix_lang_switcher_url( $url, $slug ) {
	$home_urls = array(
		'fr'    => home_url( '/fr/' ),
		'it'    => home_url( '/it/' ),
		'es'    => home_url( '/es/' ),
		'zh'    => home_url( '/zh/' ),
		'zh-tw' => home_url( '/zh-tw/' ),
	);

	if ( isset( $home_urls[ $slug ] ) && is_front_page() ) {
		return $home_urls[ $slug ];
	}

	return $url;
}

/**
 * Build the header language switcher from Polylang.
 */
function cal_get_polylang_language_output() {
	return cal_render_language_links( cal_get_polylang_languages(), 'crisp-nav-lang-switcher' );
}

/**
 * Get the source post ID in a Polylang translation group.
 */
function cal_polylang_get_source_post_id( $post_id ) {
	if ( ! function_exists( 'pll_get_post' ) ) {
		return 0;
	}

	$translations = pll_get_post_translations( $post_id );
	if ( empty( $translations ) ) {
		return 0;
	}

	return reset( $translations );
}

/**
 * Persist the source page template onto Polylang translations.
 */
add_action( 'save_post_page', 'cal_polylang_sync_page_template_to_translation', 20, 2 );
function cal_polylang_sync_page_template_to_translation( $post_id, $post ) {
	if ( wp_is_post_revision( $post_id ) || 'page' !== $post->post_type ) {
		return;
	}

	$current_template = get_page_template_slug( $post_id );
	if ( $current_template && 'default' !== $current_template ) {
		return;
	}

	$source_id = cal_polylang_get_source_post_id( $post_id );
	if ( ! $source_id || $source_id === $post_id ) {
		return;
	}

	$source_template = get_page_template_slug( $source_id );
	if ( $source_template && 'default' !== $source_template ) {
		update_post_meta( $post_id, '_wp_page_template', $source_template );
	}
}

/**
 * Remove WPML-specific ACF field preferences (handled by Polylang ACF module).
 */
remove_filter( 'acf/load_field', 'cal_wpml_acf_field_preferences' );

/**
 * Set Polylang ACF sync preference on all site ACF fields.
 * Without this, Polylang's ACF Copy strategy won't copy source values
 * to translations, leaving them empty.
 * Covers all field groups registered in the theme: cal_*, ca_*
 */
add_filter( 'acf/load_field', 'cal_acf_set_pll_translations', 5 );
function cal_acf_set_pll_translations( $field ) {
	if ( empty( $field['name'] ) ) {
		return $field;
	}
	$prefixes = array( 'cal_home_', 'cal_faq_', 'cal_reviews_', 'cal_about_', 'cal_rp_', 'cal_contact_', 'cal_howto_', 'cal_online_', 'cal_our_school_', 'cal_careers_', 'cal_international_students_', 'cal_cooking_courses_', 'cal_header_', 'cal_footer_', 'ca_' );
	$matched = false;
	foreach ( $prefixes as $prefix ) {
		if ( strpos( $field['name'], $prefix ) === 0 ) {
			$matched = true;
			break;
		}
	}
	if ( ! $matched ) {
		return $field;
	}
	if ( isset( $field['translations'] ) ) {
		return $field;
	}
	$copy_once_fields = array( 'cal_contact_phone', 'cal_contact_email', 'cal_contact_map_embed' );
	if ( in_array( $field['name'], $copy_once_fields, true ) ) {
		$field['translations'] = 'copy_once';
		return $field;
	}
	switch ( $field['type'] ) {
		case 'text':
		case 'textarea':
		case 'wysiwyg':
		case 'email':
		case 'url':
			$field['translations'] = 'translate';
			break;
		default:
			$field['translations'] = 'copy_once';
	}
	return $field;
}

add_filter( 'pll_rel_hreflang_attributes', 'cal_fix_hreflang_urls' );
function cal_fix_hreflang_urls( $hreflangs ) {
	$home_urls = array(
		'fr'    => home_url( '/fr/' ),
		'it'    => home_url( '/it/' ),
		'es'    => home_url( '/es/' ),
		'zh'    => home_url( '/zh/' ),
		'zh-tw' => home_url( '/zh-tw/' ),
	);
	foreach ( $home_urls as $code => $clean_url ) {
		foreach ( $hreflangs as $lang => $url ) {
			if ( strpos( $url, "/$code/" ) !== false && strpos( $url, '/home/' ) !== false ) {
				$hreflangs[ $lang ] = $clean_url;
			}
		}
	}
	return $hreflangs;
}

/**
 * Translate internal links inside HTML content to the current language.
 * Finds <a href="..."> tags with internal URLs and replaces them via cal_translate_menu_url().
 */
function cal_translate_content_links( $html ) {
	if ( ! $html || ! function_exists( 'pll_current_language' ) || ! function_exists( 'cal_translate_menu_url' ) ) {
		return $html;
	}
	$lang = pll_current_language();
	if ( 'en' === $lang ) {
		return $html;
	}

	$site_url = get_option( 'home' );

	$home_urls = array(
		'fr' => home_url( '/fr/' ),
		'it' => home_url( '/it/' ),
		'es' => home_url( '/es/' ),
	);

	$html = preg_replace_callback(
		'/<a\s[^>]*href=(["\'])(.*?)\1[^>]*>/i',
		function ( $m ) use ( $lang, $site_url, $home_urls ) {
			$url = $m[2];

			if ( isset( $home_urls[ $lang ] ) ) {
				if ( '' === $url || '/' === $url || $url === $site_url || $url === $site_url . '/' ) {
					return str_replace( $m[2], $home_urls[ $lang ], $m[0] );
				}
			}

			$translated = cal_translate_menu_url( $url, $lang );
			if ( $translated !== $url ) {
				return str_replace( $m[2], $translated, $m[0] );
			}

			return $m[0];
		},
		$html
	);

	return $html;
}


