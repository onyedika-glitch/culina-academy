<?php

namespace App\Http\Controllers;

use App\Mail\EnrollmentSubmitted;
use App\Mail\WaitlistJoined;
use App\Models\Post;
use App\Models\IntakeDate;
use App\Models\Enrollment;
use App\Models\Waitlist;
use App\Services\AdminMailService;
use App\Services\PolylangService;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index()
    {
        $page = cal_page_by_slug('cooking-courses');

        if ($page) {
            cal_bind_post($page);
        }

        $courses = Post::with('meta')
            ->where('post_type', 'ca_course')
            ->published()
            ->forLanguage()
            ->orderBy('menu_order')
            ->orderBy('post_date', 'desc')
            ->get();

        $bodyClass = cal_body_class_for_view('site.cooking-courses');

        return view('site.cooking-courses', compact('courses', 'page', 'bodyClass'));
    }

    public function show(Request $request)
    {
        $slug = $request->route('slug');
        $course = PolylangService::findCourseBySlug($slug);

        if (!$course) {
            abort(404);
        }

        $fields = [];
        foreach ($course->meta as $meta) {
            if (!str_starts_with($meta->meta_key, '_')) {
                $fields[$meta->meta_key] = $meta->meta_value;
            }
        }

        cal_bind_post($course);

        $intakes = IntakeDate::where('course_id', $course->ID)
            ->where('intake_date', '>=', now())
            ->orderBy('intake_date')
            ->get();

        $bodyClass = 'no-sidebar full-width-content single-ca_course';

        return view('site.course-detail', compact('course', 'fields', 'intakes', 'bodyClass'));
    }

    public function enroll(Request $request)
    {
        $intakeId = $request->route('intakeId');
        $data = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'phone' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $intake = IntakeDate::findOrFail($intakeId);
        $course = Post::find($intake->course_id);

        if ($intake->status !== 'available' || $intake->enrolled_count >= $intake->max_students) {
            $entry = Waitlist::create([
                'course_id' => $intake->course_id,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'notes' => $data['notes'],
                'intake_date' => $intake->intake_date?->format('Y-m-d'),
                'locale' => PolylangService::currentLanguage(),
                'page_url' => $request->headers->get('referer', ''),
            ]);

            AdminMailService::send(WaitlistJoined::class, $entry, $course?->post_title);

            return back()->with('success', 'This intake is full. You have been added to the waitlist.');
        }

        $data['intake_id'] = $intakeId;
        $data['status'] = 'pending';
        $enrollment = Enrollment::create($data);

        $intake->increment('enrolled_count');

        AdminMailService::send(EnrollmentSubmitted::class, $enrollment, $course?->post_title);

        return back()->with('success', 'Your enrollment has been submitted successfully. We will contact you soon.');
    }

    public function showEnrollmentForm()
    {
        $page = cal_page_by_slug('course-enrollment');

        if ($page) {
            cal_bind_post($page);
        }

        $courses = Post::with('meta')
            ->where('post_type', 'ca_course')
            ->published()
            ->forLanguage()
            ->orderBy('menu_order')
            ->get();

        $bodyClass = cal_body_class_for_view('site.course-enrollment');

        return view('site.course-enrollment', compact('courses', 'page', 'bodyClass'));
    }
}