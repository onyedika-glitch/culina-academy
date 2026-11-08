<?php
$key = $_GET['key'] ?? '';
if ( $key !== 'opencode' ) { http_response_code(403); echo 'forbidden'; exit; }
require_once dirname(__DIR__,4).'/wp-load.php';

/**
 * Scrape the Polylang Strings Translation admin page to find untranslated strings.
 * Uses wp-admin cookies from a logged-in admin session.
 */

// Get admin cookies from the current request's forwarded cookie
$cookies = '';
if (isset($_SERVER['HTTP_COOKIE'])) {
    $cookies = $_SERVER['HTTP_COOKIE'];
}

if (empty($cookies)) {
    // Try from a temporary file
    $cookie_file = __DIR__ . '/.pll-cookies.txt';
    if (file_exists($cookie_file)) {
        $cookies = file_get_contents($cookie_file);
    }
}

if (empty($cookies)) {
    echo "ERROR: No admin cookies available.\n";
    echo "To set cookies, first visit: http://q.local/wp-login.php\n";
    echo "Then run this script again.\n";
    exit;
}

// Fetch the Polylang strings page
$admin_url = admin_url('admin.php?page=mlang_strings');
$args = array(
    'headers' => array(
        'Cookie' => $cookies,
    ),
    'timeout' => 30,
);
$response = wp_remote_get($admin_url, $args);

if (is_wp_error($response)) {
    echo "ERROR: " . $response->get_error_message() . "\n";
    exit;
}

$html = wp_remote_retrieve_body($response);

// Parse the HTML to find all string entries and their translation status
// Look for rows in the strings table
$lines = explode("\n", $html);
$in_table = false;
$current_string = '';
$current_group = '';
$current_name = '';
$translations = array(); // string => [fr_status, it_status, es_status]
$empty_strings = array();

foreach ($lines as $line) {
    $line = trim($line);
    
    // Detect string row - look for the tr elements
    if (preg_match('/<tr[^>]*id="([^"]+)"[^>]*>/', $line, $m)) {
        $current_string = '';
        $current_group = '';
        $current_name = '';
        $current_translations = array('it' => '', 'fr' => '', 'es' => '');
    }
    
    // Extract string name from headings
    if (preg_match('/<th[^>]*>([^<]+)<\/th>/', $line, $m)) {
        $current_name = trim(strip_tags($m[1]));
    }
    
    // Extract group/context
    if (preg_match('/<td[^>]*class="[^"]*column-context[^"]*"[^>]*>([^<]+)<\/td>/', $line, $m)) {
        $current_group = trim(strip_tags($m[1]));
    }
    
    // Extract string text
    if (preg_match('/<td[^>]*class="[^"]*column-string[^"]*"[^>]*>([^<]+)<\/td>/', $line, $m)) {
        $current_string = trim(strip_tags($m[1]));
    }
    
    // Detect empty translation inputs
    if (preg_match('/<input[^>]*value=""[^>]*placeholder="Translation"[^>]*>/', $line)) {
        // Extract language from surrounding context
        if (strpos($line, 'lang="it"') !== false || strpos($line, 'it_IT') !== false || strpos($line, '-it') !== false) {
            $current_translations['it'] = 'EMPTY';
        } elseif (strpos($line, 'lang="fr"') !== false || strpos($line, 'fr_FR') !== false || strpos($line, '-fr') !== false) {
            $current_translations['fr'] = 'EMPTY';
        } elseif (strpos($line, 'lang="es"') !== false || strpos($line, 'es_ES') !== false || strpos($line, '-es') !== false) {
            $current_translations['es'] = 'EMPTY';
        }
    }
    
    // End of row
    if (preg_match('/<\/tr>/', $line) && !empty($current_string)) {
        if (in_array('EMPTY', $current_translations)) {
            $empty_strings[] = array(
                'string' => $current_string,
                'group' => $current_group,
                'empty_in' => array_keys(array_filter($current_translations, function($v) { return $v === 'EMPTY'; })),
            );
        }
    }
}

echo "Found " . count($empty_strings) . " strings with empty translations:\n\n";
foreach ($empty_strings as $es) {
    printf("  [%s] %s (empty in: %s)\n", $es['group'], $es['string'], implode(', ', $es['empty_in']));
}
