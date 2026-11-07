<?php
$key = $_GET['key'] ?? '';
if ( $key !== 'opencode' ) { http_response_code(403); echo 'forbidden'; exit; }
require_once dirname(__DIR__,4).'/wp-load.php';

// Step 1: Login via XML-RPC or REST API
$login_url = site_url('/wp-login.php');
$admin_url = admin_url('admin.php?page=mlang_strings');

// We'll use wp_remote_post to login
$login_data = array(
    'log' => 'culinaacademy', // Default Local WP admin username
    'pwd' => 'culinaacademy',  
    'rememberme' => 'forever',
    'wp-submit' => 'Log In',
    'redirect_to' => $admin_url,
    'testcookie' => 1,
);

$login_response = wp_remote_post($login_url, array(
    'body' => $login_data,
    'timeout' => 30,
    'redirection' => 0,
));

if (is_wp_error($login_response)) {
    echo "Login failed: " . $login_response->get_error_message() . "\n";
    exit;
}

$headers = wp_remote_retrieve_headers($login_response);
$cookies = array();
foreach ($login_response['cookies'] ?? array() as $cookie) {
    if ($cookie instanceof WP_Http_Cookie) {
        $cookies[] = $cookie->name . '=' . $cookie->value;
    }
}

if (empty($cookies)) {
    // Try extracting from Set-Cookie headers
    $set_cookie = wp_remote_retrieve_header($login_response, 'set-cookie');
    if (is_array($set_cookie)) {
        foreach ($set_cookie as $sc) {
            if (preg_match('/^([^=]+=[^;]+)/', $sc, $m)) {
                $cookies[] = $m[1];
            }
        }
    }
}

if (empty($cookies)) {
    echo "Login failed - no cookies received. Status: " . wp_remote_retrieve_response_code($login_response) . "\n";
    // Try alternative credentials
    echo "Trying with different credentials...\n";
    // Let me check what the actual admin user might be
    $users = get_users(array('role__in' => array('administrator'), 'number' => 1));
    if (!empty($users)) {
        echo "First admin user: " . $users[0]->user_login . "\n";
    }
    exit;
}

$cookie_str = implode('; ', $cookies);

// Step 2: Fetch the Polylang strings page
$page = 1;
$all_empty = array();

while ($page <= 14) {
    $url = $admin_url . '&paged=' . $page;
    $response = wp_remote_get($url, array(
        'headers' => array('Cookie' => $cookie_str),
        'timeout' => 30,
    ));
    
    if (is_wp_error($response)) {
        echo "Page $page: " . $response->get_error_message() . "\n";
        $page++;
        continue;
    }
    
    $html = wp_remote_retrieve_body($response);
    
    // Parse the strings table
    // The admin page uses a specific table structure
    // Look for tr elements with string data
    
    $dom = new DOMDocument();
    @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
    $xpath = new DOMXPath($dom);
    
    // Find all table rows in the strings table
    $rows = $xpath->query("//table[contains(@class, 'wp-list-table')]//tr[not(contains(@class, 'column-headers'))]");
    
    foreach ($rows as $row) {
        $cells = $row->getElementsByTagName('td');
        $string_text = '';
        $group_text = '';
        $empty_langs = array();
        
        foreach ($cells as $cell) {
            $class = $cell->getAttribute('class');
            if (strpos($class, 'column-string') !== false) {
                $string_text = trim($cell->textContent);
            }
            if (strpos($class, 'column-context') !== false) {
                $group_text = trim($cell->textContent);
            }
            // Check for empty inputs
            $inputs = $cell->getElementsByTagName('input');
            foreach ($inputs as $input) {
                if ($input->getAttribute('value') === '' && $input->getAttribute('type') === 'text') {
                    // Determine language from surrounding structure
                    $lang = '';
                    $parent = $input->parentNode;
                    while ($parent) {
                        if ($parent->nodeType === XML_ELEMENT_NODE && $parent->hasAttribute('class')) {
                            $cls = $parent->getAttribute('class');
                            if (strpos($cls, 'it_IT') !== false || strpos($cls, '-it') !== false) $lang = 'it';
                            if (strpos($cls, 'fr_FR') !== false || strpos($cls, '-fr') !== false) $lang = 'fr';
                            if (strpos($cls, 'es_ES') !== false || strpos($cls, '-es') !== false) $lang = 'es';
                        }
                        if ($lang) break;
                        $parent = $parent->parentNode;
                    }
                    if ($lang && !in_array($lang, $empty_langs)) {
                        $empty_langs[] = $lang;
                    }
                }
            }
        }
        
        if (!empty($string_text) && !empty($empty_langs)) {
            $all_empty[] = array(
                'string' => $string_text,
                'group' => $group_text,
                'empty_in' => $empty_langs,
            );
        }
    }
    
    echo "Page $page: scanned\n";
    $page++;
}

echo "\n=== All empty strings ===\n";
echo "Found " . count($all_empty) . " strings with empty translations\n\n";

$unique_strings = array();
foreach ($all_empty as $e) {
    $unique_strings[$e['string']] = $e['string'];
    printf("  [%s] %s\n", $e['group'], $e['string']);
}

echo "\nUnique missing source strings: " . count($unique_strings) . "\n";
