<?php

namespace Tests\Feature\Admin;

use App\Models\Post;
use App\Models\WpUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Booking\Concerns\CreatesCourses;
use Tests\TestCase;

class TrashPostTest extends TestCase
{
    use CreatesCourses;
    use RefreshDatabase;

    private function administrator(): WpUser
    {
        $id = 1;
        DB::table('wp_users')->insert([
            'ID' => $id,
            'user_login' => 'admin',
            'user_email' => 'admin@example.com',
            'user_pass' => bcrypt('secret'),
        ]);
        DB::table('wp_usermeta')->insert([
            'umeta_id' => 1,
            'user_id' => $id,
            'meta_key' => 'wp_capabilities',
            'meta_value' => serialize(['administrator' => true]),
        ]);

        return WpUser::findOrFail($id);
    }

    public function test_posts_can_be_created_and_updated_through_eloquent(): void
    {
        Carbon::setTestNow('2027-03-01 09:00:00');
        $course = $this->createCourse();

        $this->assertSame('2027-03-01 09:00:00', $course->post_date->format('Y-m-d H:i:s'));

        Carbon::setTestNow('2027-03-02 10:30:00');
        $course->update(['post_title' => 'Renamed']);

        $this->assertSame('2027-03-02 10:30:00', $course->fresh()->post_modified->format('Y-m-d H:i:s'));
        Carbon::setTestNow();
    }

    public function test_an_administrator_can_move_a_course_to_the_trash(): void
    {
        $course = $this->createCourse();

        $this->actingAs($this->administrator(), 'admin')
            ->delete("/admin/courses/{$course->ID}")
            ->assertRedirect(route('admin.courses.index'))
            ->assertSessionHas('success');

        $this->assertSame('trash', Post::find($course->ID)->post_status);
    }

    public function test_guests_cannot_trash_posts(): void
    {
        $course = $this->createCourse();

        $this->delete("/admin/courses/{$course->ID}")->assertRedirect(route('admin.login'));

        $this->assertSame('publish', Post::find($course->ID)->post_status);
    }
}
