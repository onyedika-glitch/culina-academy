<?php
/**
 * Custom home page template fields and assets.
 *
 * @package GeneratePress
 */

if ( ! false && defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', 'cal_home_template_assets' );
/**
 * Load home template assets only where needed.
 */
function cal_home_template_assets() {
	if ( ! is_page_template( 'template-home.php' ) ) {
		return;
	}

	$css_path = get_template_directory() . '/assets/css/cal-home-template.css';
	$js_path  = get_template_directory() . '/assets/js/cal-home-template.js';

	wp_enqueue_style(
		'cal-home-fonts',
		'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@700&family=Montserrat:wght@400;500;600;700;800;900&family=Outfit:wght@600&family=Playfair+Display:ital,wght@0,700;0,800;0,900;1,700&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'cal-home-template',
		get_template_directory_uri() . '/assets/css/cal-home-template.css',
		array( 'cal-home-fonts' ),
		file_exists( $css_path ) ? (string) filemtime( $css_path ) : GENERATE_VERSION
	);

	wp_enqueue_script(
		'cal-home-template',
		get_template_directory_uri() . '/assets/js/cal-home-template.js',
		array(),
		file_exists( $js_path ) ? (string) filemtime( $js_path ) : GENERATE_VERSION,
		true
	);

	wp_localize_script(
		'cal-home-template',
		'calHomeAjax',
		array(
			'ajaxurl'       => admin_url( 'admin-ajax.php' ),
			'nonce'         => wp_create_nonce( 'cal_home_enquiry' ),
			'language'      => function_exists( 'pll_current_language' ) ? pll_current_language( 'slug' ) : '',
			'requiredText'  => cal_home_ui_string( 'Please fill in all required fields.' ),
			'sendingText'   => cal_home_ui_string( 'Sending...' ),
			'errorText'     => cal_home_ui_string( 'Something went wrong. Please try again.' ),
			'enquireText'   => cal_home_ui_string( 'Enquire now' ),
			'bannerLabel'   => cal_home_ui_string( 'Go to banner slide' ),
			'reviewLabel'   => cal_home_ui_string( 'Go to review' ),
		)
	);
}

add_action( 'init', 'cal_register_home_ui_strings', 20 );
function cal_register_home_ui_strings() {
	$strings = array(
		'Please fill in all required fields.',
		'Please enter a valid email address.',
		'Could not send your enquiry. Please try again later.',
		'Sending...',
		'Something went wrong. Please try again.',
		'Enquire now',
		'Go to banner slide',
		'Go to review',
		'Previous slide',
		'Next slide',
		'Your Name',
		'Email',
		'Phone / WhatsApp',
		'Submit',
		'New Course Enquiry from %s',
		"Name: %s\nEmail: %s\nPhone: %s\n",
		'Thank you. We will get back to you shortly.',
		'Read More Reviews',
	);

	foreach ( $strings as $string ) {
		if ( function_exists( 'pll_register_string' ) ) {
			pll_register_string( 'home_ui_' . md5( $string ), $string, 'Culina Academy Home UI' );
		}
	}
}

function cal_home_ui_string( $string ) {
	if ( function_exists( 'pll__' ) ) {
		$translated = pll__( $string );
		if ($translated === $string && function_exists('cal_i18n_text')) {
			return cal_i18n_text($string);
		}
		return $translated;
	}

	$translated = __( $string, 'generatepress' );
	if ($translated === $string && function_exists('cal_i18n_text')) {
		return cal_i18n_text($string);
	}
	return $translated;
}

function cal_home_language_page_url( $slug ) {
	$path = '/' . trim( (string) $slug, '/' ) . '/';

	return apply_filters( 'culina_academy_migration_url', home_url( $path ) );
}

function cal_language_page_url_by_template( $template, $default_slug ) {
	$page_id = 0;
	$pages   = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_wp_page_template',
			'meta_value'     => (string) $template,
			'no_found_rows'  => true,
		)
	);

	if ( ! empty( $pages ) ) {
		$page_id = (int) $pages[0];

		if ( function_exists( 'pll_get_post' ) && function_exists( 'pll_current_language' ) ) {
			$translated_id = (int) pll_get_post( $page_id, pll_current_language() );

			if ( $translated_id ) {
				$page_id = $translated_id;
			}
		}
	}

	$url = $page_id ? get_permalink( $page_id ) : cal_home_language_page_url( $default_slug );

	return apply_filters( 'culina_academy_migration_url', $url );
}

add_filter( 'body_class', 'cal_home_template_body_class' );
/**
 * Add a template-specific body class.
 */
function cal_home_template_body_class( $classes ) {
	if ( is_page_template( 'template-home.php' ) ) {
		$classes[] = 'cal-home-template-page';
	}

	return $classes;
}

add_action( 'acf/init', 'cal_register_home_template_fields' );
/**
 * Register ACF fields for the custom home template.
 */
function cal_register_home_template_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_cal_home_template',
			'title'    => 'Home Page Sections',
			'fields'   => array(
				array(
					'key'   => 'field_cal_home_slider_tab',
					'label' => 'Banner Slider',
					'name'  => '',
					'type'  => 'tab',
				),
				array(
					'key'          => 'field_cal_home_slides',
					'label'        => 'Slides',
					'name'         => 'cal_home_slides',
					'type'         => 'repeater',
					'layout'       => 'block',
					'button_label' => 'Add slide',
					'sub_fields'   => array(
						array(
							'key'           => 'field_cal_home_slide_image',
							'label'         => 'Banner Image',
							'name'          => 'image',
							'type'          => 'image',
							'return_format' => 'array',
							'preview_size'  => 'medium',
						),
						array(
							'key'   => 'field_cal_home_slide_eyebrow',
							'label' => 'Small Title',
							'name'  => 'eyebrow',
							'type'  => 'text',
						),
						array(
							'key'   => 'field_cal_home_slide_title',
							'label' => 'Title',
							'name'  => 'title',
							'type'  => 'textarea',
							'rows'  => 2,
						),
						array(
							'key'   => 'field_cal_home_slide_text',
							'label' => 'Description',
							'name'  => 'text',
							'type'  => 'textarea',
							'rows'  => 3,
						),
						array(
							'key'           => 'field_cal_home_slide_button',
							'label'         => 'Button',
							'name'          => 'button',
							'type'          => 'link',
							'return_format' => 'array',
						),
					),
				),
				array(
					'key'   => 'field_cal_home_shortcode_tab',
					'label' => 'Carousel Section',
					'name'  => '',
					'type'  => 'tab',
				),
				array(
					'key'   => 'field_cal_home_carousel_shortcode',
					'label' => 'Carousel Shortcode',
					'name'  => 'cal_home_carousel_shortcode',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_cal_home_why_tab',
					'label' => 'Why Choose Section',
					'name'  => '',
					'type'  => 'tab',
				),
				array(
					'key'   => 'field_cal_home_why_title',
					'label' => 'Section Title',
					'name'  => 'cal_home_why_title',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_cal_home_why_subtitle',
					'label' => 'Section Subtitle',
					'name'  => 'cal_home_why_subtitle',
					'type'  => 'text',
				),
				array(
					'key'          => 'field_cal_home_why_cards',
					'label'        => 'Cards',
					'name'         => 'cal_home_why_cards',
					'type'         => 'repeater',
					'layout'       => 'block',
					'button_label' => 'Add card',
					'sub_fields'   => array(
						array(
							'key'           => 'field_cal_home_why_card_icon',
							'label'         => 'Icon Image',
							'name'          => 'icon',
							'type'          => 'image',
							'return_format' => 'array',
							'preview_size'  => 'thumbnail',
						),
						array(
							'key'   => 'field_cal_home_why_card_title',
							'label' => 'Card Title',
							'name'  => 'title',
							'type'  => 'text',
						),
						array(
							'key'   => 'field_cal_home_why_card_text',
							'label' => 'Card Text',
							'name'  => 'text',
							'type'  => 'textarea',
							'rows'  => 4,
						),
					),
				),
				array(
					'key'           => 'field_cal_home_why_button',
					'label'         => 'Section Button',
					'name'          => 'cal_home_why_button',
					'type'          => 'link',
					'return_format' => 'array',
				),
				array(
					'key'   => 'field_cal_home_enquiry_tab',
					'label' => 'Course Enquiry Section',
					'name'  => '',
					'type'  => 'tab',
				),
				array(
					'key'   => 'field_cal_home_enquiry_bg',
					'label' => 'Background Image',
					'name'  => 'cal_home_enquiry_bg',
					'type'  => 'image',
					'return_format' => 'url',
					'preview_size'  => 'large',
				),
				array(
					'key'   => 'field_cal_home_enquiry_eyebrow',
					'label' => 'Eyebrow Text',
					'name'  => 'cal_home_enquiry_eyebrow',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_cal_home_enquiry_heading',
					'label' => 'Heading',
					'name'  => 'cal_home_enquiry_heading',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_cal_home_enquiry_form_text',
					'label' => 'Form Header Text',
					'name'  => 'cal_home_enquiry_form_text',
					'type'  => 'textarea',
					'rows'  => 2,
				),
				array(
					'key'   => 'field_cal_home_enquiry_btn_text',
					'label' => 'Submit Button Text',
					'name'  => 'cal_home_enquiry_btn_text',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_cal_home_testimonials_tab',
					'label' => 'Testimonials Section',
					'name'  => '',
					'type'  => 'tab',
				),
				array(
					'key'   => 'field_cal_home_testimonials_heading',
					'label' => 'Section Heading',
					'name'  => 'cal_home_testimonials_heading',
					'type'  => 'text',
				),
				array(
					'key'          => 'field_cal_home_testimonials_repeater',
					'label'        => 'Reviews',
					'name'         => 'cal_home_testimonials',
					'type'         => 'repeater',
					'layout'       => 'block',
					'button_label' => 'Add review',
					'sub_fields'   => array(
						array(
							'key'   => 'field_cal_home_testimonial_quote',
							'label' => 'Quote',
							'name'  => 'quote',
							'type'  => 'textarea',
							'rows'  => 3,
						),
						array(
							'key'   => 'field_cal_home_testimonial_name',
							'label' => 'Name',
							'name'  => 'name',
							'type'  => 'text',
						),
						array(
							'key'   => 'field_cal_home_testimonial_location',
							'label' => 'Location',
							'name'  => 'location',
							'type'  => 'text',
						),
					),
				),
				array(
					'key'           => 'field_cal_home_testimonials_cta',
					'label'         => 'CTA Button',
					'name'          => 'cal_home_testimonials_cta',
					'type'          => 'link',
					'return_format' => 'array',
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'template-home.php',
					),
				),
			),
		)
	);
}

/**
 * Get a page ACF field value.
 */
function cal_page_field( $name ) {
	if ( function_exists( 'get_field' ) ) {
		$value = get_field( $name );

		if ( ! empty( $value ) ) {
			return $value;
		}
	}

	return '';
}

/**
 * Return image size attributes for Media Library images used by URL/ID ACF fields.
 *
 * @param mixed $image ACF image value.
 * @param string $size Image size.
 * @return string
 */
function cal_home_image_size_attrs( $image, $size = 'full' ) {
	$image_id = 0;

	if ( is_numeric( $image ) ) {
		$image_id = (int) $image;
	} elseif ( is_array( $image ) && ! empty( $image['ID'] ) ) {
		$image_id = (int) $image['ID'];
	} elseif ( is_array( $image ) && ! empty( $image['id'] ) ) {
		$image_id = (int) $image['id'];
	} elseif ( is_string( $image ) ) {
		$image_id = attachment_url_to_postid( $image );
	}

	if ( ! $image_id ) {
		return '';
	}

	if ( function_exists( 'pll_get_post' ) && function_exists( 'pll_current_language' ) ) {
		$translated_id = (int) pll_get_post( $image_id, pll_current_language( 'slug' ) );
		if ( $translated_id ) {
			$image_id = $translated_id;
		}
	}

	$meta = wp_get_attachment_image_src( $image_id, $size );
	if ( ! $meta ) {
		return '';
	}

	return ' width="' . esc_attr( (string) $meta[1] ) . '" height="' . esc_attr( (string) $meta[2] ) . '"';
}

/**
 * Render the home banner slider.
 */
function cal_render_home_slider() {
	$slides = cal_page_field( 'cal_home_slides' );
	$slides = is_array( $slides ) ? $slides : array();

	if ( empty( $slides ) ) {
		return;
	}

	?>
	<section class="wpcul-slider">
		<div class="wpcul-track">
			<?php foreach ( $slides as $index => $slide ) : ?>
				<?php
				$image_value = $slide['image'] ?? '';
				$image       = cal_image_url( $image_value );
				$button      = $slide['button'] ?? array();
				$is_first    = 0 === (int) $index;
				?>
				<?php if ( $image ) : ?>
					<div class="wpcul-slide">
						<img class="wpcul-slide-bg" src="<?php echo esc_url( $image ); ?>" alt=""<?php echo cal_home_image_size_attrs( $image_value ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> loading="<?php echo $is_first ? 'eager' : 'lazy'; ?>"<?php echo $is_first ? ' fetchpriority="high" decoding="async"' : ' decoding="async"'; ?>>
						<div class="wpcul-content">
							<?php if ( ! empty( $slide['eyebrow'] ) || ! empty( $slide['title'] ) ) : ?>
								<?php echo $is_first ? '<h1>' : '<h2>'; ?>
									<?php if ( ! empty( $slide['eyebrow'] ) ) : ?>
										<span><?php echo esc_html( $slide['eyebrow'] ); ?></span>
									<?php endif; ?>
									<?php echo esc_html( $slide['title'] ?? '' ); ?>
								<?php echo $is_first ? '</h1>' : '</h2>'; ?>
							<?php endif; ?>
							<?php if ( ! empty( $slide['text'] ) ) : ?>
								<p><?php echo esc_html( $slide['text'] ); ?></p>
							<?php endif; ?>
							<?php cal_render_link( $button, 'wpcul-btn' ); ?>
						</div>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>

		<?php if ( count( $slides ) > 1 ) : ?>
			<div class="wpcul-nav">
				<button class="wpcul-prev" type="button" aria-label="<?php echo esc_attr( cal_home_ui_string( 'Previous slide' ) ); ?>"></button>
				<button class="wpcul-next" type="button" aria-label="<?php echo esc_attr( cal_home_ui_string( 'Next slide' ) ); ?>"></button>
			</div>
			<div class="wpcul-dots"></div>
		<?php endif; ?>
	</section>
	<?php
}

/**
 * Render the home shortcode section.
 */
function cal_render_home_carousel_section() {
	$shortcode = cal_page_field( 'cal_home_carousel_shortcode' );

	if ( ! $shortcode ) {
		return;
	}
	?>
	<section class="cal-home-carousel-section">
		<?php echo do_shortcode( $shortcode ); ?>
	</section>
	<?php
}

/**
 * Render the home enquiry section (split-screen form).
 */
function cal_render_home_enquiry_section() {
	$bg      = cal_page_field( 'cal_home_enquiry_bg' );
	$eyebrow = cal_page_field( 'cal_home_enquiry_eyebrow' );
	$heading = cal_page_field( 'cal_home_enquiry_heading' );
	$formtxt = cal_page_field( 'cal_home_enquiry_form_text' );
	$btntext = cal_page_field( 'cal_home_enquiry_btn_text' );
	?>
	<section class="cal-enquiry-section">
		<div class="cal-enquiry-bg">
			<?php if ( $bg ) : ?>
				<img class="cal-enquiry-bg-img" src="<?php echo esc_url( $bg ); ?>" alt=""<?php echo cal_home_image_size_attrs( $bg ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> loading="lazy" decoding="async">
			<?php endif; ?>
			<div class="cal-enquiry-overlay"></div>
			<div class="cal-enquiry-inner">
				<div class="cal-enquiry-text">
					<?php if ( $eyebrow ) : ?>
						<span class="cal-enquiry-eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
					<?php endif; ?>
					<?php if ( $heading ) : ?>
						<h2 class="cal-enquiry-heading"><?php echo esc_html( $heading ); ?></h2>
					<?php endif; ?>
				</div>
				<div class="cal-enquiry-form-wrap">
					<div class="cal-enquiry-form-header">
						<?php if ( $formtxt ) : ?>
							<p><?php echo esc_html( $formtxt ); ?></p>
						<?php endif; ?>
					</div>
					<div class="cal-enquiry-form-body">
						<form class="cal-enquiry-form" novalidate>
							<?php if ( function_exists( 'cal_public_form_fields' ) ) { cal_public_form_fields(); } ?>
							<div class="cal-enquiry-field">
								<label for="cal-enquiry-name"><?php echo esc_html( cal_home_ui_string( 'Your Name' ) ); ?></label>
								<input id="cal-enquiry-name" type="text" name="name" autocomplete="name" required>
							</div>
							<div class="cal-enquiry-field">
								<label for="cal-enquiry-email"><?php echo esc_html( cal_home_ui_string( 'Email' ) ); ?></label>
								<input id="cal-enquiry-email" type="email" name="email" autocomplete="email" required>
							</div>
							<div class="cal-enquiry-field ca-phone-row">
								<label for="cal-enquiry-phone"><?php echo esc_html( cal_home_ui_string( 'Phone / WhatsApp' ) ); ?></label>
								<input type="hidden" class="ca-intl-code" name="country_code" value="44">
								<input id="cal-enquiry-phone" type="tel" name="phone" autocomplete="tel" required>
							</div>
							<div class="cal-enquiry-field">
								<button type="submit" class="cal-enquiry-submit"><?php echo esc_html( $btntext ); ?></button>
							</div>
							<div class="cal-enquiry-note" role="status" aria-live="polite"></div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</section>
	<?php
}

/**
 * Render the Why Choose Us section (ACF-driven).
 */
function cal_render_home_why_choose_section() {
	$title    = cal_page_field( 'cal_home_why_title' );
	$subtitle = cal_page_field( 'cal_home_why_subtitle' );

	$cards = cal_page_field( 'cal_home_why_cards' );
	$cards = is_array( $cards ) ? $cards : array();

	if ( ! empty( $cards ) && is_array( $cards ) ) {
		foreach ( $cards as $i => $card ) {
			if ( ! empty( $card['icon'] ) ) {
				$card['icon'] = cal_image_url( $card['icon'] ) ?: $card['icon'];
			}
		}
	}

	$button   = cal_page_field( 'cal_home_why_button' );
	$button   = is_array( $button ) ? $button : array();
	$btn_url  = ! empty( $button['url'] ) ? apply_filters( 'culina_academy_migration_url', $button['url'] ) : '';
	$btn_text = ! empty( $button['title'] ) ? $button['title'] : '';
	?>
	<section class="cal-why-choose-section">
		<div class="cal-why-choose-inner">
			<header class="cal-why-choose-header">
				<?php if ( $title ) : ?>
					<h2><?php echo esc_html( $title ); ?></h2>
				<?php endif; ?>
				<?php if ( $subtitle ) : ?>
					<p class="cal-why-choose-subtitle"><?php echo esc_html( $subtitle ); ?></p>
				<?php endif; ?>
			</header>
			<?php if ( ! empty( $cards ) ) : ?>
				<div class="cal-why-choose-grid">
					<?php foreach ( $cards as $card ) : ?>
						<article class="cal-why-choose-card">
							<?php if ( ! empty( $card['icon'] ) ) : ?>
								<?php $icon_url = cal_image_url( $card['icon'] ); ?>
								<?php if ( $icon_url ) : ?>
									<div class="cal-why-choose-icon">
										<img src="<?php echo esc_url( $icon_url ); ?>" alt="" width="48" height="48">
									</div>
								<?php endif; ?>
							<?php endif; ?>
							<?php if ( ! empty( $card['title'] ) ) : ?>
								<h3><?php echo esc_html( $card['title'] ); ?></h3>
							<?php endif; ?>
							<?php if ( ! empty( $card['text'] ) ) : ?>
								<p><?php echo esc_html( $card['text'] ); ?></p>
							<?php endif; ?>
						</article>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<?php if ( $btn_text && $btn_url ) : ?>
				<div class="cal-why-choose-action">
					<a href="<?php echo esc_url( $btn_url ); ?>" class="cal-why-choose-button"><?php echo esc_html( $btn_text ); ?></a>
				</div>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

/**
 * Render the testimonials section (ACF-driven).
 */
function cal_render_home_testimonials_section() {
	if ( ! apply_filters( 'cal_show_testimonial_sections', false ) ) {
		return;
	}

	$heading = cal_page_field( 'cal_home_testimonials_heading' );

	$reviews = cal_page_field( 'cal_home_testimonials' );

	if ( empty( $reviews ) || ! is_array( $reviews ) ) {
		$reviews = array();
	}

	$cta      = cal_page_field( 'cal_home_testimonials_cta' );
	$cta      = is_array( $cta ) ? $cta : array();
	$cta_url  = ! empty( $cta['url'] ) ? apply_filters( 'culina_academy_migration_url', $cta['url'] ) : '';
	$cta_text = ! empty( $cta['title'] ) ? $cta['title'] : '';

	if ( empty( $reviews ) ) {
		return;
	}
	?>
	<section class="reviews-section">
		<div class="reviews-header">
			<h2 class="reviews-heading"><?php echo esc_html( $heading ); ?></h2>
		</div>
		<div class="reviews-slider-wrapper">
			<div class="reviews-slider-track" id="reviewsTrack">
				<?php foreach ( $reviews as $r ) : ?>
					<div class="review-card">
						<div class="review-content">
							<p class="review-text">"<?php echo esc_html( $r['quote'] ); ?>"</p>
							<div class="review-author">
								<span class="author-name"><?php echo esc_html( $r['name'] ); ?></span>
								<span class="author-country"><?php echo esc_html( $r['location'] ); ?></span>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
				<?php if ( count( $reviews ) > 1 ) : ?>
					<?php foreach ( $reviews as $r ) : ?>
						<div class="review-card" aria-hidden="true">
							<div class="review-content">
								<p class="review-text">"<?php echo esc_html( $r['quote'] ); ?>"</p>
								<div class="review-author">
									<span class="author-name"><?php echo esc_html( $r['name'] ); ?></span>
									<span class="author-country"><?php echo esc_html( $r['location'] ); ?></span>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
			<?php if ( count( $reviews ) > 1 ) : ?>
				<div class="slider-dots" id="sliderDots"></div>
			<?php endif; ?>
		</div>
		<?php if ( $cta_url && $cta_text ) : ?>
			<div class="reviews-footer">
				<a href="<?php echo esc_url( $cta_url ); ?>" class="reviews-cta-btn"><?php echo esc_html( $cta_text ); ?></a>
			</div>
		<?php endif; ?>
	</section>
	<?php
}

/**
 * AJAX handler for the enquiry form.
 */
add_action( 'wp_ajax_cal_enquiry_submit', 'cal_handle_enquiry_submit' );
add_action( 'wp_ajax_nopriv_cal_enquiry_submit', 'cal_handle_enquiry_submit' );
function cal_handle_enquiry_submit() {
	check_ajax_referer( 'cal_home_enquiry', 'nonce' );
	if ( function_exists( 'cal_switch_to_request_language' ) ) {
		cal_switch_to_request_language();
	}
	if ( function_exists( 'cal_public_form_guard' ) ) {
		$guard = cal_public_form_guard( 'home_enquiry' );
		if ( is_wp_error( $guard ) ) {
			wp_send_json_error( array( 'message' => $guard->get_error_message() ) );
		}
	}

	if ( empty( $_POST['name'] ) || empty( $_POST['email'] ) || empty( $_POST['phone'] ) ) {
		wp_send_json_error( array( 'message' => cal_home_ui_string( 'Please fill in all required fields.' ) ) );
	}

	$name  = sanitize_text_field( wp_unslash( $_POST['name'] ) );
	$email = sanitize_email( wp_unslash( $_POST['email'] ) );
	$code  = sanitize_text_field( wp_unslash( $_POST['country_code'] ?? '44' ) );
	$phone = sanitize_text_field( wp_unslash( $_POST['phone'] ) );
	$full  = '+' . $code . ' ' . $phone;

	if ( ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => cal_home_ui_string( 'Please enter a valid email address.' ) ) );
	}

	$to      = get_option( 'admin_email', 'info@example.com' );
	$subject = sprintf( cal_home_ui_string( 'New Course Enquiry from %s' ), $name );
	$message = sprintf( cal_home_ui_string( "Name: %s\nEmail: %s\nPhone: %s\n" ), $name, $email, $full );
	$headers = array( 'Reply-To: ' . $email );

	if ( ! wp_mail( $to, $subject, $message, $headers ) ) {
		wp_send_json_error( array( 'message' => cal_home_ui_string( 'Could not send your enquiry. Please try again later.' ) ) );
	}

	wp_send_json_success( array( 'message' => cal_home_ui_string( 'Thank you. We will get back to you shortly.' ) ) );
}
