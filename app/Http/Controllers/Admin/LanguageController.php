<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Services\PolylangService;

class LanguageController extends Controller
{
    public function index()
    {
        $languages = PolylangService::getLanguages();
        $counts = [];

        foreach ($languages as $lang) {
            $counts[$lang['slug']] = [
                'pages' => 0,
                'courses' => 0,
                'posts' => 0,
            ];
        }

        foreach (['page', 'ca_course', 'post'] as $type) {
            $key = $type === 'ca_course' ? 'courses' : ($type === 'post' ? 'posts' : 'pages');
            $ids = Post::where('post_type', $type)->where('post_status', 'publish')->pluck('ID');
            foreach ($ids as $id) {
                $lang = PolylangService::getPostLanguage($id);
                if (isset($counts[$lang][$key])) {
                    $counts[$lang][$key]++;
                }
            }
        }

        return view('admin.languages.index', compact('languages', 'counts'));
    }
}