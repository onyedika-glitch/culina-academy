<?php
$key = $_GET['key'] ?? '';
if ( $key !== 'opencode' ) { http_response_code(403); echo 'forbidden'; exit; }
require_once dirname(__DIR__,4).'/wp-load.php';

if ( ! function_exists( 'PLL' ) || ! PLL() ) { die( 'Polylang not active' ); }

// ── Simulate admin string registration ──────────────────────
// Force PLL_Admin_Strings context
if ( ! defined( 'PLL_ADMIN_STRINGS_LOADED' ) ) {
    // Manually trigger all the init hooks that register strings
    // This replicates what happens during admin page load
    
    // 1. Header & Footer strings
    $footer_keys = array(
        'cal_footer_cta_title', 'cal_footer_gallery_heading', 'cal_footer_about_heading',
        'cal_footer_about', 'cal_footer_contact_heading', 'cal_footer_address',
        'cal_footer_policies_heading', 'cal_footer_bottom_line',
    );
    foreach ( $footer_keys as $k ) {
        $v = get_field( $k, 'option' );
        if ( is_string( $v ) && '' !== $v ) {
            PLL_Admin_Strings::register_string( $k, $v, 'Header & Footer', in_array($k, array('cal_footer_about','cal_footer_address','cal_footer_bottom_line')) );
        }
    }
    $policy_links = get_field( 'cal_footer_policy_links', 'option' );
    if ( is_array( $policy_links ) ) {
        foreach ( $policy_links as $i => $item ) {
            $link = isset( $item['link'] ) && is_array( $item['link'] ) ? $item['link'] : $item;
            if ( ! empty( $link['title'] ) && is_string( $link['title'] ) ) {
                PLL_Admin_Strings::register_string( "cal_footer_policy_link_{$i}", $link['title'], 'Footer Policy Links', false );
            }
        }
    }

    // 2. Form strings
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
    foreach ( $form_strings as $s => $g ) {
        PLL_Admin_Strings::register_string( $s, $s, $g, false );
    }

    // 3. Nav menu items
    $menu_items = wp_get_nav_menu_items( 28 );
    if ( is_array( $menu_items ) ) {
        foreach ( $menu_items as $item ) {
            $title = trim( $item->title );
            if ( '' !== $title ) {
                PLL_Admin_Strings::register_string( 'nav_' . $item->ID, $title, 'Primary Menu', false );
            }
        }
    }

    // 4. Home slider strings
    $slider_strings = array(
        'START YOUR CULINARY JOURNEY' => 'Home Page Slider',
        'COURSE ENQUIRY' => 'Home Page Slider',
        'ENQUIRE NOW' => 'Home Page Slider',
    );
    foreach ( $slider_strings as $s => $g ) {
        PLL_Admin_Strings::register_string( $s, $s, $g, false );
    }

    // 5. Why Choose strings
    $why_choose = array(
        'Why Choose Us?' => 'Home Page Why Choose',
        'A CHOICE THAT MAKES THE DIFFERENCE.' => 'Home Page Why Choose',
        'ABOUT CULINA ACADEMY' => 'Home Page Why Choose',
    );
    foreach ( $why_choose as $s => $g ) {
        PLL_Admin_Strings::register_string( $s, $s, $g, false );
    }

    // 6. Home Enquiry
    PLL_Admin_Strings::register_string( 'Our admissions team will help you choose the right professional chef programme.', 'Our admissions team will help you choose the right professional chef programme.', 'Home Page Enquiry', true );

    // 7. Course UI strings
    $course_ui = array(
        'Home' => 'Breadcrumbs',
        'Breadcrumb' => 'Breadcrumbs',
        'Cooking Courses' => 'Course Single Page',
        'Location' => 'Course Single Page',
        'Duration' => 'Course Single Page',
        'Price' => 'Course Single Page',
        'Download programme' => 'Course Single Page',
        'Enquire now' => 'Course Single Page',
        'Reserve your course now' => 'Course Single Page',
        'Read More Reviews' => 'Home Page',
        'Previous slide' => 'Home Page Slider',
        'Next slide' => 'Home Page Slider',
    );
    foreach ( $course_ui as $s => $g ) {
        PLL_Admin_Strings::register_string( $s, $s, $g, false );
    }

    // 8. Course reserve texts
    $courses = get_posts( array( 'post_type' => 'ca_course', 'posts_per_page' => -1, 'post_status' => 'publish' ) );
    foreach ( $courses as $course ) {
        $rt = get_post_meta( $course->ID, 'ca_reserve_text', true );
        if ( is_string( $rt ) && '' !== trim( $rt ) ) {
            PLL_Admin_Strings::register_string( 'course-reserve-text', $rt, 'Course Reserve Text - ' . $course->post_title, true );
        }
    }

    // 9. WordPress default strings
    PLL_Admin_Strings::register_string( 'blogname', get_option('blogname'), 'WordPress', false );
    PLL_Admin_Strings::register_string( 'blogdescription', get_option('blogdescription'), 'WordPress', false );
    PLL_Admin_Strings::register_string( 'date_format', get_option('date_format'), 'WordPress', false );
    PLL_Admin_Strings::register_string( 'time_format', get_option('time_format'), 'WordPress', false );
}

// ── Get all registered strings ──────────────────────────────
$registered = PLL_Admin_Strings::get_strings();
echo "Total registered strings: " . count( $registered ) . "\n\n";

// ── Cross-reference with term meta ──────────────────────────
// Get all English source strings from term meta
$term_sources = array();
foreach ( PLL()->model->get_languages_list() as $l ) {
    $meta = get_term_meta( $l->term_id, '_pll_strings_translations', true );
    if ( is_array( $meta ) ) {
        foreach ( $meta as $pair ) {
            if ( is_array( $pair ) && count( $pair ) >= 2 && ! empty( $pair[1] ) ) {
                $term_sources[ $pair[0] ] = true;
            }
        }
    }
}

// Find missing strings
$missing = array();
foreach ( $registered as $r ) {
    $str = $r['string'];
    if ( ! isset( $term_sources[ $str ] ) ) {
        $missing[] = $r;
    }
}

echo "Already in term meta: " . count( $term_sources ) . " source strings\n";
echo "Not yet translated: " . count( $missing ) . " strings\n\n";

if ( ! empty( $missing ) ) {
    echo "--- Missing strings ---\n";
    foreach ( $missing as $m ) {
        printf( "  [%s] %s\n", $m['context'], $m['string'] );
    }
}

// ── Google Translate the missing ones ───────────────────────
if ( empty( $missing ) ) {
    echo "Nothing to translate!\n";
    exit;
}

function cal_gt( $text, $target ) {
    if ( empty( trim( $text ) ) ) return $text;
    $url = add_query_arg( array( 'client' => 'gtx', 'sl' => 'en', 'tl' => $target, 'dt' => 't', 'q' => $text ),
        'https://translate.googleapis.com/translate_a/single' );
    $r = wp_remote_get( $url, array( 'timeout' => 60 ) );
    if ( is_wp_error( $r ) ) return null;
    $body = wp_remote_retrieve_body( $r );
    $data = json_decode( $body, true );
    if ( ! is_array( $data ) || ! isset( $data[0] ) ) return null;
    $out = '';
    foreach ( $data[0] as $seg ) { if ( isset( $seg[0] ) ) $out .= $seg[0]; }
    return $out;
}

$lang_map = array( 'fr' => 'fr', 'it' => 'it', 'es' => 'es', 'zh' => 'zh-CN', 'zh-tw' => 'zh-TW' );
$total = 0;

foreach ( $lang_map as $slug => $gt_code ) {
    $lang = PLL()->model->get_language( $slug );
    if ( ! $lang ) continue;

    $existing = get_term_meta( $lang->term_id, '_pll_strings_translations', true );
    $existing = is_array( $existing ) ? $existing : array();

    $existing_map = array();
    foreach ( $existing as $pair ) {
        if ( is_array( $pair ) && count( $pair ) >= 2 ) {
            $existing_map[ $pair[0] ] = $pair[1];
        }
    }

    $added = 0;
    foreach ( $missing as $m ) {
        $source = $m['string'];
        if ( isset( $existing_map[ $source ] ) && ! empty( $existing_map[ $source ] ) ) continue;
        
        // Skip format strings
        if ( preg_match( '/^[\\\\FjYgai:, ]+$/', $source ) ) continue;

        echo "  [$slug] " . mb_substr( $source, 0, 60 ) . "... ";
        $trans = cal_gt( $source, $gt_code );
        if ( null === $trans ) { echo "FAILED\n"; continue; }
        
        // Check if exists (update) or append
        $found = false;
        foreach ( $existing as $k => $pair ) {
            if ( is_array( $pair ) && count( $pair ) >= 2 && $pair[0] === $source ) {
                $existing[ $k ][1] = $trans;
                $found = true;
                break;
            }
        }
        if ( ! $found ) {
            $existing[] = array( $source, $trans );
        }
        $added++; $total++;
        echo "OK\n";
    }
    
    update_term_meta( $lang->term_id, '_pll_strings_translations', $existing );
    echo "  [$slug] Added/updated $added translations\n";
}

// ── Clear cache ─────────────────────────────────────────────
try {
    $ref = new ReflectionClass( 'PLL_MO' );
    $p = $ref->getProperty( 'cache' );
    $p->setAccessible( true );
    $p->setValue( new PLL_Cache() );
} catch ( Exception $e ) {}

if ( isset( $GLOBALS['l10n']['pll_string'] ) && $GLOBALS['l10n']['pll_string'] instanceof PLL_MO ) {
    $global_mo = $GLOBALS['l10n']['pll_string'];
    foreach ( PLL()->model->get_languages_list() as $lang_obj ) {
        $new_mo = new PLL_MO();
        $new_mo->import_from_db( $lang_obj );
        foreach ( $new_mo->entries as $key => $entry ) {
            $global_mo->add_entry( $entry );
        }
    }
}
delete_transient( 'pll_languages_list' );
PLL()->model->languages->clean_cache();

echo "\nDone! Added/updated $total translations.\n";
