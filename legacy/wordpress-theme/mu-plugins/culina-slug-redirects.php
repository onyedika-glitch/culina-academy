<?php
/* Plugin Name: Culina Academy Slug Redirects */
add_action('template_redirect', function() {
    $req = $_SERVER["REQUEST_URI"];
    
    $slugs_to_check = [
        'contact-us', 'how-to-apply', 'about-us', 'international-students',
        'reviews', 'faqs', 'terms-and-conditions', 'privacy-policy',
        'complaints-policy-and-procedure', 'attendance-and-punctuality-policy', 'cookies-policy'
    ];
    $langs = ['fr', 'es', 'it', 'zh', 'zh-tw'];
    
    foreach($langs as $lang) {
        foreach($slugs_to_check as $slug) {
            $bad_path1 = "/$lang/$slug-2/";
            $bad_path2 = "/$lang/$slug-3/";
            $good_path = "/$lang/$slug/";
            
            if ($req === $bad_path1 || $req === $bad_path2) {
                wp_redirect($good_path, 301);
                exit;
            }
        }
    }
});
