<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IntakeDate;
use App\Models\Post;
use Illuminate\Http\Request;

class IntakeController extends Controller
{
    /**
     * Show all intake dates for a course, with real-time capacity and override controls.
     */
    public function index(Request $request)
    {
        $courseId = (int) $request->get('course_id', 0);
        $courses = Post::where('post_type', 'ca_course')
            ->where('post_status', 'publish')
            ->orderBy('menu_order')
            ->orderBy('post_title')
            ->get(['ID', 'post_title']);

        $intakes = collect();
        $selectedCourse = null;

        if ($courseId) {
            $selectedCourse = Post::find($courseId);
            $intakes = IntakeDate::where('course_id', $courseId)
                ->orderBy('intake_date')
                ->get();
        }

        return view('admin.intakes.index', compact('courses', 'intakes', 'courseId', 'selectedCourse'));
    }

    /**
     * Store a new intake date for a course.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'course_id' => 'required|integer',
            'intake_date' => 'required|date',
            'max_students' => 'required|integer|min:1|max:999',
            'status' => 'required|in:available,closed',
            'availability_override' => 'nullable|in:force_available,force_full',
            'admin_notes' => 'nullable|string|max:500',
        ]);

        IntakeDate::create($data);

        return redirect()
            ->route('admin.intakes.index', ['course_id' => $data['course_id']])
            ->with('success', 'Intake date added.');
    }

    /**
     * Update an intake date's settings (capacity, override, status).
     */
    public function update(Request $request, int $id)
    {
        $intake = IntakeDate::findOrFail($id);

        $data = $request->validate([
            'intake_date' => 'required|date',
            'max_students' => 'required|integer|min:1|max:999',
            'status' => 'required|in:available,closed',
            'availability_override' => 'nullable|in:force_available,force_full',
            'admin_notes' => 'nullable|string|max:500',
        ]);

        // Allow clearing the override
        if (! $request->filled('availability_override')) {
            $data['availability_override'] = null;
        }

        $intake->update($data);

        return redirect()
            ->route('admin.intakes.index', ['course_id' => $intake->course_id])
            ->with('success', 'Intake date updated.');
    }

    /**
     * Delete an intake date.
     */
    public function destroy(int $id)
    {
        $intake = IntakeDate::findOrFail($id);
        $courseId = $intake->course_id;
        $intake->delete();

        return redirect()
            ->route('admin.intakes.index', ['course_id' => $courseId])
            ->with('success', 'Intake date removed.');
    }

    /**
     * Quick-toggle the availability override via AJAX or form POST.
     */
    public function toggleOverride(Request $request, int $id)
    {
        $intake = IntakeDate::findOrFail($id);

        $data = $request->validate([
            'availability_override' => 'nullable|in:force_available,force_full',
        ]);

        $intake->update([
            'availability_override' => $data['availability_override'] ?? null,
        ]);

        return back()->with('success', 'Availability override updated for '.$intake->intake_date->format('d M Y').'.');
    }
}
