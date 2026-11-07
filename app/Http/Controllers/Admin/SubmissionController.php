<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\WaitlistInvitation;
use App\Models\Application;
use App\Models\Booking;
use App\Models\BookingDocument;
use App\Models\ContactSubmission;
use App\Models\Enrollment;
use App\Models\Post;
use App\Models\Waitlist;
use App\Models\WaitingList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SubmissionController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'contacts');

        return view('admin.submissions.index', [
            'tab' => $tab,
            'contacts' => ContactSubmission::orderByDesc('created_at')->paginate(20, ['*'], 'contacts_page'),
            'applications' => Application::orderByDesc('created_at')->paginate(20, ['*'], 'applications_page'),
            'waitlist' => Waitlist::orderByDesc('created_at')->paginate(20, ['*'], 'waitlist_page'),
            'enrollments' => Enrollment::orderByDesc('created_at')->paginate(20, ['*'], 'enrollments_page'),
            'bookings' => Booking::with('course', 'documents')->orderByDesc('created_at')->paginate(20, ['*'], 'bookings_page'),
            'waitingLists' => WaitingList::orderBy('created_at', 'asc')->paginate(20, ['*'], 'waitinglists_page'),
            'courses' => Post::where('post_type', 'ca_course')->pluck('post_title', 'ID'),
        ]);
    }

    public function showContact(int $id)
    {
        $submission = ContactSubmission::findOrFail($id);

        return view('admin.submissions.contact', compact('submission'));
    }

    public function showApplication(int $id)
    {
        $application = Application::findOrFail($id);

        return view('admin.submissions.application', compact('application'));
    }

    public function updateApplicationStatus(Request $request, int $id)
    {
        $application = Application::findOrFail($id);
        $data = $request->validate(['status' => ['required', 'in:pending,reviewed,accepted,rejected']]);
        $application->update(['status' => $data['status']]);

        return back()->with('success', 'Application status updated.');
    }

    public function destroyContact(int $id)
    {
        ContactSubmission::findOrFail($id)->delete();

        return redirect()->route('admin.submissions.index', ['tab' => 'contacts'])
            ->with('success', 'Contact submission deleted.');
    }

    public function destroyApplication(int $id)
    {
        Application::findOrFail($id)->delete();

        return redirect()->route('admin.submissions.index', ['tab' => 'applications'])
            ->with('success', 'Application deleted.');
    }

    // ─── Booking Management ───────────────────────

    public function showBooking(int $id)
    {
        $booking = Booking::with('course', 'documents')->findOrFail($id);
        $courses = Post::where('post_type', 'ca_course')->pluck('post_title', 'ID');

        return view('admin.submissions.booking', compact('booking', 'courses'));
    }

    public function updateBookingStatus(Request $request, int $id)
    {
        $booking = Booking::findOrFail($id);
        $data = $request->validate(['status' => ['required', 'in:pending,confirmed,cancelled,expired']]);
        $booking->update(['status' => $data['status']]);

        return back()->with('success', 'Booking status updated.');
    }

    public function destroyBooking(int $id)
    {
        Booking::findOrFail($id)->delete();

        return redirect()->route('admin.submissions.index', ['tab' => 'bookings'])
            ->with('success', 'Booking deleted.');
    }

    // ─── Waiting List Invite System ───────────────

    /**
     * Admin clicks "Invite" next to a waiting list entry.
     * Generates a secure token and sends an email to the student.
     */
    public function inviteWaitlistEntry(Request $request, int $id)
    {
        $entry = WaitingList::findOrFail($id);

        if ($entry->status !== 'waiting') {
            return back()->with('error', 'This entry has already been invited or claimed.');
        }

        // Generate a cryptographically secure token
        $token = Str::random(64);
        $expiryHours = (int) ($request->input('expiry_hours', 48));
        $expiresAt = now()->addHours($expiryHours);

        $entry->update([
            'status' => 'invited',
            'invite_token' => $token,
            'invite_token_expires_at' => $expiresAt,
            'invited_at' => now(),
        ]);

        // Build the invite URL
        $inviteUrl = url('/waitlist/invite/' . $token);

        // Get course title
        $course = Post::find($entry->course_id);
        $courseTitle = $course?->post_title ?? 'Culina Academy Course';

        // Send invitation email
        try {
            Mail::to($entry->email)->send(new WaitlistInvitation(
                entry: $entry,
                courseTitle: $courseTitle,
                inviteUrl: $inviteUrl,
                expiryHours: $expiryHours,
            ));

            return back()->with('success', "Invitation sent to {$entry->email}. They have {$expiryHours} hours to claim their spot.");
        } catch (\Throwable $e) {
            Log::error('Failed to send waitlist invitation: ' . $e->getMessage());
            return back()->with('error', 'Invitation token generated but email failed to send. Token: ' . $token);
        }
    }

    /**
     * Delete a waiting list entry.
     */
    public function destroyWaitlistEntry(int $id)
    {
        WaitingList::findOrFail($id)->delete();

        return redirect()->route('admin.submissions.index', ['tab' => 'waitinglists'])
            ->with('success', 'Waiting list entry deleted.');
    }
}