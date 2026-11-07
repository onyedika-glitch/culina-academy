<?php

namespace App\Http\Controllers\Admin;

class CourseController extends PostResourceController
{
    protected function postType(): string
    {
        return 'ca_course';
    }

    protected function resourceLabel(): string
    {
        return 'Course';
    }

    protected function indexRoute(): string
    {
        return 'admin.courses.index';
    }

    protected function editRoute(): string
    {
        return 'admin.courses.edit';
    }
}