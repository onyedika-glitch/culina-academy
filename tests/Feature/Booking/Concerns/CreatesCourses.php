<?php

namespace Tests\Feature\Booking\Concerns;

use App\Models\Post;

trait CreatesCourses
{
    /**
     * The wp_* tables are created with PostgreSQL bigserial keys, which SQLite
     * does not auto-increment, so test rows get explicit IDs.
     */
    protected function nextPostId(): int
    {
        return (int) Post::max('ID') + 1;
    }

    protected function createCourse(string $title = 'Professional Chef Diploma'): Post
    {
        return Post::create([
            'ID' => $this->nextPostId(),
            'post_title' => $title,
            'post_name' => str($title)->slug()->value(),
            'post_type' => 'ca_course',
            'post_status' => 'publish',
            'post_content' => '',
            'post_excerpt' => '',
            'to_ping' => '',
            'pinged' => '',
            'post_content_filtered' => '',
            'guid' => '',
        ]);
    }

    protected function createPage(string $title = 'About us'): Post
    {
        return tap($this->createCourse($title), fn (Post $post) => $post->update(['post_type' => 'page']));
    }
}
