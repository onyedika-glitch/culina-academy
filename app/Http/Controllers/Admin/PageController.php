<?php

namespace App\Http\Controllers\Admin;

class PageController extends PostResourceController
{
    protected function postType(): string
    {
        return 'page';
    }

    protected function resourceLabel(): string
    {
        return 'Page';
    }

    protected function indexRoute(): string
    {
        return 'admin.pages.index';
    }

    protected function editRoute(): string
    {
        return 'admin.pages.edit';
    }
}