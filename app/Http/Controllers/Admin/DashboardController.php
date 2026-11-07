<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Booking;
use App\Models\ContactSubmission;
use App\Models\Enrollment;
use App\Models\Post;
use App\Models\Waitlist;
use App\Models\WaitingList;
use App\Services\PolylangService;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'pages' => Post::where('post_type', 'page')->where('post_status', 'publish')->count(),
            'courses' => Post::where('post_type', 'ca_course')->where('post_status', 'publish')->count(),
            'posts' => Post::where('post_type', 'post')->where('post_status', 'publish')->count(),
            'applications' => Application::count(),
            'contacts' => ContactSubmission::count(),
            'waitlist' => Waitlist::count(),
            'enrollments' => Enrollment::count(),
            'bookings' => Booking::count(),
            'bookings_confirmed' => Booking::where('status', 'confirmed')->count(),
            'bookings_pending' => Booking::where('status', 'pending')->count(),
            'waiting_lists' => WaitingList::where('status', 'waiting')->count(),
        ];

        $recentContacts = ContactSubmission::orderByDesc('created_at')->limit(5)->get();
        $recentApplications = Application::orderByDesc('created_at')->limit(5)->get();
        $recentBookings = Booking::with('course')->orderByDesc('created_at')->limit(5)->get();
        $languages = PolylangService::getLanguages();

        return view('admin.dashboard', compact('stats', 'recentContacts', 'recentApplications', 'recentBookings', 'languages'));
    }
}