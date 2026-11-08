<?php
/**
 * Restaurant Partners page template fields and assets.
 *
 * @package GeneratePress
 */

if ( ! false && defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', 'cal_rp_template_assets' );
/**
 * Load restaurant partners template assets only where needed.
 */
function cal_rp_template_assets() {
	if ( ! is_page_template( 'template-restaurant-partners.php' ) ) {
		return;
	}

	wp_enqueue_style(
		'cal-restaurant-partners-template',
		get_template_directory_uri() . '/assets/css/cal-restaurant-partners-template.css',
		array(),
		GENERATE_VERSION
	);
}

add_filter( 'body_class', 'cal_rp_template_body_class', 20 );
/**
 * Add template-specific body classes and normalize layout classes.
 *
 * @param array $classes Existing body classes.
 * @return array
 */
function cal_rp_template_body_class( $classes ) {
	if ( ! is_page_template( 'template-restaurant-partners.php' ) ) {
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
	$classes[] = 'cal-rp-template-page';

	if ( ! in_array( 'no-sidebar', $classes, true ) ) {
		$classes[] = 'no-sidebar';
	}

	if ( ! in_array( 'full-width-content', $classes, true ) ) {
		$classes[] = 'full-width-content';
	}

	return array_unique( $classes );
}

add_filter( 'generate_sidebar_layout', 'cal_rp_sidebar_layout' );
/**
 * Force the restaurant partners template to render without sidebars.
 *
 * @param string $layout Current sidebar layout.
 * @return string
 */
function cal_rp_sidebar_layout( $layout ) {
	if ( is_page_template( 'template-restaurant-partners.php' ) ) {
		return 'no-sidebar';
	}

	return $layout;
}

add_action( 'acf/init', 'cal_register_rp_template_fields' );
/**
 * Register ACF fields for the restaurant partners page template.
 */
function cal_register_rp_template_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_cal_rp_template',
			'title'    => 'Restaurant Partners Page',
			'fields'   => array(
				array(
					'key'   => 'field_cal_rp_hero_tab',
					'label' => 'Hero Section',
					'name'  => '',
					'type'  => 'tab',
				),
				array(
					'key'           => 'field_cal_rp_hero_title',
					'label'         => 'Hero Title',
					'name'          => 'cal_rp_hero_title',
					'type'          => 'text',
				),
				array(
					'key'   => 'field_cal_rp_restaurants_tab',
					'label' => 'Restaurants',
					'name'  => '',
					'type'  => 'tab',
				),
				array(
					'key'          => 'field_cal_rp_restaurants',
					'label'        => 'Restaurant Cards',
					'name'         => 'cal_rp_restaurants',
					'type'         => 'repeater',
					'layout'       => 'block',
					'button_label' => 'Add restaurant',
					'sub_fields'   => array(
						array(
							'key'   => 'field_cal_rp_name',
							'label' => 'Name',
							'name'  => 'name',
							'type'  => 'text',
						),
						array(
							'key'           => 'field_cal_rp_image',
							'label'         => 'Image',
							'name'          => 'image',
							'type'          => 'image',
							'return_format' => 'array',
							'preview_size'  => 'medium',
						),
						array(
							'key'   => 'field_cal_rp_address',
							'label' => 'Address & contact',
							'name'  => 'address',
							'type'  => 'textarea',
							'rows'  => 5,
						),
						array(
							'key'           => 'field_cal_rp_michelin',
							'label'         => 'Michelin stars',
							'name'          => 'michelin',
							'type'          => 'number',
							'min'           => 0,
							'max'           => 3,
							'default_value' => 0,
						),
						array(
							'key'           => 'field_cal_rp_aa',
							'label'         => 'AA rosettes',
							'name'          => 'aa',
							'type'          => 'number',
							'min'           => 0,
							'max'           => 5,
							'default_value' => 0,
						),
						array(
							'key'   => 'field_cal_rp_map_url',
							'label' => 'Map URL',
							'name'  => 'map_url',
							'type'  => 'url',
						),
						array(
							'key'   => 'field_cal_rp_website_url',
							'label' => 'Website URL',
							'name'  => 'website_url',
							'type'  => 'url',
						),
						array(
							'key'   => 'field_cal_rp_website_label',
							'label' => 'Website label',
							'name'  => 'website_label',
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
						'value'    => 'template-restaurant-partners.php',
					),
				),
			),
		)
	);
}

/**
 * Get a restaurant partners template field value.
 *
 * @param string $name Field name.
 * @return mixed
 */
function cal_rp_field( $name ) {
	if ( function_exists( 'get_field' ) ) {
		$value = get_field( $name );

		if ( '' !== $value && null !== $value ) {
			return $value;
		}
	}

	return '';
}

/**
 * Normalize ACF restaurant rows for rendering.
 *
 * @return array<int, array<string, mixed>>
 */
function cal_rp_get_restaurants() {
	$acf_rows = cal_rp_field( 'cal_rp_restaurants' );

	if ( ! empty( $acf_rows ) && is_array( $acf_rows ) ) {
		$restaurants = array();

		foreach ( $acf_rows as $row ) {
			$restaurants[] = array(
				'name'          => trim( (string) ( $row['name'] ?? '' ) ),
				'image'         => cal_rp_get_image_url_from_field( $row['image'] ?? '' ),
				'address'       => trim( (string) ( $row['address'] ?? '' ) ),
				'michelin'      => (int) ( $row['michelin'] ?? 0 ),
				'aa'            => (int) ( $row['aa'] ?? 0 ),
				'map_url'       => trim( (string) ( $row['map_url'] ?? '' ) ),
				'website_url'   => trim( (string) ( $row['website_url'] ?? '' ) ),
				'website_label' => trim( (string) ( $row['website_label'] ?? '' ) ),
			);
		}

		return array_values(
			array_filter(
				$restaurants,
				static function ( $restaurant ) {
					return '' !== $restaurant['name'];
				}
			)
		);
	}

	return array();
}

/**
 * Get image URL from an ACF image field value.
 *
 * @param mixed $image_field ACF image field (ID, array, or URL string).
 * @return string
 */
function cal_rp_get_image_url_from_field( $image_field ) {
	if ( function_exists( 'cal_image_url' ) ) {
		return cal_image_url( $image_field );
	}

	if ( is_numeric( $image_field ) ) {
		$url = wp_get_attachment_url( (int) $image_field );
		return $url ? $url : '';
	}

	if ( is_array( $image_field ) ) {
		if ( ! empty( $image_field['url'] ) ) {
			return (string) $image_field['url'];
		}

		if ( ! empty( $image_field['ID'] ) ) {
			$url = wp_get_attachment_url( (int) $image_field['ID'] );
			return $url ? $url : '';
		}
	}

	if ( is_string( $image_field ) && '' !== trim( $image_field ) ) {
		return trim( $image_field );
	}

	return '';
}

/**
 * Render Michelin or AA award icons.
 *
 * @param int    $count Number of icons.
 * @param string $type  Award type: michelin|aa.
 */
function cal_rp_render_award_icons( $count, $type ) {
	$count = max( 0, (int) $count );

	if ( $count < 1 ) {
		return;
	}

	$local      = get_option( 'cal_rp_award_icons_local', array() );
	$local_id   = is_array( $local ) && isset( $local[ $type ] ) ? (int) $local[ $type ] : 0;
	$local_url  = $local_id ? wp_get_attachment_url( $local_id ) : '';
	$url        = $local_url ? $local_url : '';
	for ( $i = 0; $i < $count; $i++ ) {
		if ( ! $url ) {
			return;
		}

		printf(
			'<img src="%s" alt="" width="15" height="15" loading="lazy" decoding="async" aria-hidden="true">',
			esc_url( $url )
		);
	}
}

/**
 * Render a single restaurant partner card.
 *
 * @param array<string, mixed> $restaurant Restaurant data.
 */
function cal_rp_render_card( $restaurant ) {
	$name          = trim( (string) ( $restaurant['name'] ?? '' ) );
	$image         = trim( (string) ( $restaurant['image'] ?? '' ) );
	$address       = trim( (string) ( $restaurant['address'] ?? '' ) );
	$michelin      = (int) ( $restaurant['michelin'] ?? 0 );
	$aa            = (int) ( $restaurant['aa'] ?? 0 );
	$map_url       = trim( (string) ( $restaurant['map_url'] ?? '' ) );
	$website_url   = trim( (string) ( $restaurant['website_url'] ?? '' ) );
	$website_label = trim( (string) ( $restaurant['website_label'] ?? '' ) );

	if ( '' === $name ) {
		return;
	}

	if ( '' !== $address ) {
		$address_lines = preg_split( '/\R/u', $address );
		if ( $address_lines ) {
			$first_line = trim( (string) $address_lines[0] );
			$is_name    = 0 === strcasecmp( $first_line, $name );
			$is_label   = 0 === stripos( $first_line, $name . ' Restaurant' );
			if ( $is_name || $is_label ) {
				array_shift( $address_lines );
				$address = trim( implode( "\n", $address_lines ) );
			}
		}
	}

	if ( '' === $website_label && '' !== $website_url ) {
		$website_label = preg_replace( '#^https?://#', '', $website_url );
		$website_label = rtrim( (string) $website_label, '/' );
	}
	?>
	<article class="cal-rp-card">
		<header class="cal-rp-card-header">
			<h2 class="cal-rp-card-title"><?php echo esc_html( $name ); ?></h2>
		</header>
		<div class="cal-rp-card-body">
			<?php if ( $image ) : ?>
				<div class="cal-rp-card-media">
					<img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $name ); ?>" loading="lazy" decoding="async">
				</div>
			<?php endif; ?>
			<div class="cal-rp-card-details">
				<?php if ( $address ) : ?>
					<p class="cal-rp-address"><?php echo nl2br( esc_html( $address ) ); ?></p>
				<?php endif; ?>
				<?php if ( $michelin > 0 ) : ?>
					<p class="cal-rp-awards">
						<span class="cal-rp-awards-label"><?php echo esc_html( cal_i18n_text( 'Michelin Stars:' ) ); ?></span>
						<span class="cal-rp-awards-icons"><?php cal_rp_render_award_icons( $michelin, 'michelin' ); ?></span>
					</p>
				<?php endif; ?>
				<?php if ( $aa > 0 ) : ?>
					<p class="cal-rp-awards">
						<span class="cal-rp-awards-label"><?php echo esc_html( cal_i18n_text( 'AA Rosettes:' ) ); ?></span>
						<span class="cal-rp-awards-icons"><?php cal_rp_render_award_icons( $aa, 'aa' ); ?></span>
					</p>
				<?php endif; ?>
				<?php if ( $map_url ) : ?>
					<p class="cal-rp-map-link">
						<a href="<?php echo esc_url( $map_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( cal_i18n_text( 'View map' ) ); ?></a>
					</p>
				<?php endif; ?>
			</div>
			<?php if ( $website_url ) : ?>
				<p class="cal-rp-website">
					<a href="<?php echo esc_url( $website_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $website_label ? $website_label : $website_url ); ?></a>
				</p>
			<?php endif; ?>
		</div>
	</article>
	<?php
}
