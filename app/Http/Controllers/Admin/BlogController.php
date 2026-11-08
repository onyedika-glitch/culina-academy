<?php

namespace App\Http\Controllers\Admin;

class BlogController extends PostResourceController
{
    protected function postType(): string
    {
        return 'post';
    }

    protected function resourceLabel(): string
    {
        return 'Blog Post';
    }

    protected function indexRoute(): string
    {
        return 'admin.blog.index';
    }

    protected function editRoute(): string
    {
        return 'admin.blog.edit';
    }
}
