<?php
$key = $_GET['key'] ?? '';
if ( $key !== 'opencode' ) { http_response_code(403); echo 'forbidden'; exit; }
require_once dirname(__DIR__,4).'/wp-load.php';

// Clear PLL_MO static cache via reflection
try {
    $ref = new ReflectionClass('PLL_MO');
    $cache_prop = $ref->getProperty('cache');
    $cache_prop->setAccessible(true);
    $cache_prop->setValue( new PLL_Cache() );
} catch ( Exception $e ) {
    echo "Reflection failed: " . $e->getMessage() . "\n";
}

// Force-reload PLL_MO for each language into the global l10n
if ( isset( $GLOBALS['l10n']['pll_string'] ) && $GLOBALS['l10n']['pll_string'] instanceof PLL_MO ) {
    $global_mo = $GLOBALS['l10n']['pll_string'];
    foreach ( PLL()->model->get_languages_list() as $lang ) {
        $new_mo = new PLL_MO();
        $new_mo->import_from_db( $lang );
        // Merge entries from the fresh MO into the global one
        foreach ( $new_mo->entries as $key => $entry ) {
            $global_mo->add_entry( $entry );
        }
    }
    echo "Global PLL_MO reloaded.\n";
}

// Clear all Polylang transients and caches
delete_transient('pll_languages_list');
delete_transient('pll_translated_slugs');
PLL()->model->languages->clean_cache();
if ( method_exists( PLL()->model, 'clean_languages_cache' ) ) {
    PLL()->model->clean_languages_cache();
}

echo "All Polylang caches cleared.\n";

// Now test
$tests = array(
    'BECOME A PROFESSIONAL CHEF',
    'Gallery',
    'Our Policies',
    'View our courses',
    'Location',
    'Duration',
    'Enquire now',
);
foreach ( $tests as $t ) {
    $translated = function_exists('pll__') ? pll__($t) : 'pll__ not found';
    echo "$t => $translated\n";
}
