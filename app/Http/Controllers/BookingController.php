<?php

namespace App\Http\Controllers;

use App\Mail\ApplicationSubmitted;
use App\Mail\BookingConfirmed;
use App\Mail\WaitlistConfirmation;
use App\Mail\WaitlistJoined;
use App\Models\Application;
use App\Models\Booking;
use App\Models\BookingDocument;
use App\Models\Post;
use App\Models\Waitlist;
use App\Models\WaitingList;
use App\Services\AcfFieldService;
use App\Services\AdminMailService;
use App\Services\CulinaAcademy;
use App\Services\CloudinaryService;
use App\Services\CourseIntakeService;
use App\Services\PolylangService;
use App\Services\StripeService;
use App\Services\UiTranslationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BookingController extends Controller
{
    /**
     * Main AJAX dispatcher – preserves backward-compatibility with existing actions.
     */
    public function handle(Request $request)
    {
        $action = $request->input('action');

        return match ($action) {
            'ca_get_modal' => $this->modal($request),
            'ca_submit_standalone_application' => $this->submitApplication($request),
            'ca_join_waitlist' => $this->joinWaitlist($request),
            default => response()->json(['success' => false, 'data' => 'Unknown action.'], 400),
        };
    }

    // ──────────────────────────────────────────────
    //  BOOKING (Book Now) – with file uploads + Stripe
    // ──────────────────────────────────────────────

    /**
     * Submit a booking with document uploads.
     * Creates a pending booking, uploads files to Cloudinary, and redirects to Stripe Checkout.
     */
    public function submitBooking(Request $request)
    {
        $data = $request->validate([
            'course_id' => 'required|integer',
            'intake_date' => 'nullable|string|max:20',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'phone' => 'required|string|max:50',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'postcode' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'passport_country' => 'nullable|string|max:100',
            'visa' => 'nullable|string|max:10',
            'notes' => 'nullable|string|max:2000',
            'consent' => 'accepted',
            'documents' => 'required|array|min:1|max:5',
            'documents.*' => 'file|max:5120', // 5MB per file
            'document_types' => 'required|array',
            'document_types.*' => 'string|in:cv,id_proof,certificate,other',
        ]);

        // Check the course exists
        $course = Post::with('meta')->find($data['course_id']);
        if (!$course || $course->post_type !== 'ca_course') {
            return back()->withErrors(['course_id' => 'Course not found.'])->withInput();
        }

        // Validate each uploaded file
        $files = $request->file('documents', []);
        foreach ($files as $file) {
            $error = CloudinaryService::validateFile($file);
            if ($error) {
                return back()->withErrors(['documents' => $error])->withInput();
            }
        }

        // Double check capacity validation on submit (respects overrides)
        if (!empty($data['intake_date'])) {
            $intake = \App\Models\IntakeDate::where('course_id', $data['course_id'])
                ->whereDate('intake_date', $data['intake_date'])
                ->first();
            if ($intake && !$intake->isAvailable()) {
                return back()->withErrors(['booking' => 'This intake date is fully booked. Please join the waiting list instead.'])->withInput();
            }
        }

        // Create the pending booking record
        $booking = Booking::create([
            'course_id' => (int) $data['course_id'],
            'intake_date' => $data['intake_date'] ?? null,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'postcode' => $data['postcode'] ?? null,
            'country' => $data['country'] ?? null,
            'passport_country' => $data['passport_country'] ?? null,
            'visa' => $data['visa'] ?? null,
            'notes' => $data['notes'] ?? null,
            'status' => 'pending',
            'locale' => PolylangService::currentLanguage(),
            'page_url' => $request->headers->get('referer', ''),
        ]);

        // Upload documents to Cloudinary with GDPR hashed filenames
        $documentTypes = $request->input('document_types', []);
        foreach ($files as $index => $file) {
            $result = CloudinaryService::upload($file);
            if ($result) {
                BookingDocument::create([
                    'booking_id' => $booking->id,
                    'document_type' => $documentTypes[$index] ?? 'other',
                    'original_name' => $result['original_name'],
                    'hashed_name' => $result['hashed_name'],
                    'cloudinary_public_id' => $result['public_id'],
                    'cloudinary_url' => $result['secure_url'],
                    'mime_type' => $result['mime_type'],
                    'size_bytes' => $result['size'],
                ]);
            } else {
                Log::warning('Document upload failed for booking #' . $booking->id, [
                    'file' => $file->getClientOriginalName(),
                ]);
            }
        }

        // Determine deposit amount from course meta (default £500)
        $depositAmountCents = (int) (AcfFieldService::getField($booking->course_id, 'ca_deposit_amount', 500) * 100);

        // Create Stripe Checkout Session and redirect
        $checkoutUrl = StripeService::createCheckoutSession($booking, $depositAmountCents);

        if (!$checkoutUrl) {
            return back()->withErrors(['payment' => 'Unable to start payment. Please try again or contact us.'])->withInput();
        }

        return redirect()->away($checkoutUrl);
    }

    /**
     * Booking success page – shown after returning from Stripe.
     * Note: The actual confirmation is done via webhook for reliability.
     */
    public function bookingSuccess(Request $request)
    {
        $sessionId = $request->query('session_id');
        $booking = null;

        if ($sessionId) {
            $booking = Booking::where('stripe_checkout_session_id', $sessionId)->first();
        }

        $page = cal_page_by_slug('course-enrollment');
        if ($page) {
            cal_bind_post($page);
        }

        return view('site.booking-success', compact('booking', 'page'));
    }

    /**
     * Booking cancelled page – shown when user cancels Stripe checkout.
     */
    public function bookingCancelled(Request $request)
    {
        $bookingId = $request->query('booking_id');
        $booking = $bookingId ? Booking::find($bookingId) : null;

        $page = cal_page_by_slug('course-enrollment');
        if ($page) {
            cal_bind_post($page);
        }

        return view('site.booking-cancelled', compact('booking', 'page'));
    }

    // ──────────────────────────────────────────────
    //  WAITING LIST (Join Waiting List – no payment)
    // ──────────────────────────────────────────────

    /**
     * Submit a waiting list entry (no payment, no file upload).
     * Sends confirmation email to the student.
     */
    public function submitWaitingList(Request $request)
    {
        $data = $request->validate([
            'course_id' => 'required|integer',
            'intake_date' => 'nullable|string|max:20',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'phone' => 'nullable|string|max:50',
        ]);

        $course = Post::find($data['course_id']);
        if (!$course || $course->post_type !== 'ca_course') {
            return back()->withErrors(['course_id' => 'Course not found.'])->withInput();
        }

        $entry = WaitingList::create([
            'course_id' => (int) $data['course_id'],
            'intake_date' => $data['intake_date'] ?? null,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'status' => 'waiting',
            'locale' => PolylangService::currentLanguage(),
            'page_url' => $request->headers->get('referer', ''),
        ]);

        // Send confirmation email to the student
        try {
            Mail::to($entry->email)->send(new WaitlistConfirmation($entry, $course->post_title));
        } catch (\Throwable $e) {
            Log::error('Waitlist confirmation email failed: ' . $e->getMessage());
        }

        // Notify admin
        AdminMailService::send(WaitlistJoined::class, new Waitlist([
            'course_id' => $entry->course_id,
            'first_name' => $entry->first_name,
            'last_name' => $entry->last_name,
            'email' => $entry->email,
            'phone' => $entry->phone,
            'intake_date' => $entry->intake_date,
            'locale' => $entry->locale,
            'page_url' => $entry->page_url,
        ]), $course->post_title);

        return back()->with('success', 'You have been added to the waiting list. A confirmation email has been sent to you.');
    }

    // ──────────────────────────────────────────────
    //  Existing methods (backward-compatible)
    // ──────────────────────────────────────────────

    protected function modal(Request $request)
    {
        $courseId = (int) $request->input('course_id');
        $course = Post::with('meta')->find($courseId);

        if (!$course || $course->post_type !== 'ca_course') {
            return response()->json(['success' => false, 'data' => 'Course not found.']);
        }

        $image = cal_image_url(AcfFieldService::getField($courseId, 'ca_image_url'));

        return response()->json([
            'success' => true,
            'data' => [
                'title' => $course->post_title,
                'image' => $image,
                'price' => CulinaAcademy::formatPrice($courseId),
                'meta' => CourseIntakeService::courseMetaLine($courseId),
                'html' => CourseIntakeService::renderModalHtml($courseId),
            ],
        ]);
    }

    protected function submitApplication(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:100',
            'surname' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'phone' => 'required|string|max:50',
            'country' => 'nullable|string|max:100',
            'intl_code' => 'nullable|string|max:10',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'postcode' => 'required|string|max:20',
            'passport_country' => 'required|string|max:100',
            'visa' => 'required|string|max:10',
            'heard' => 'required|string|max:255',
            'start_year' => 'required|string|max:4',
            'start_month' => 'required|string|max:20',
            'cookery_ability' => 'required|string|max:100',
            'motivation' => 'required|string|max:5000',
            'courses' => 'required|array|min:1',
            'courses.*' => 'string|max:255',
            'course_id' => 'nullable|integer',
            'intake_date' => 'nullable|string|max:20',
            'consent' => 'accepted',
        ]);

        $locale = PolylangService::normalizeCode(
            (string) $request->input('page_language', PolylangService::currentLanguage())
        );

        $application = Application::create([
            'first_name' => $data['first_name'],
            'surname' => $data['surname'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'country' => $data['country'] ?? null,
            'intl_code' => $data['intl_code'] ?? null,
            'address' => $data['address'],
            'city' => $data['city'],
            'postcode' => $data['postcode'],
            'passport_country' => $data['passport_country'],
            'visa' => $data['visa'],
            'heard' => $data['heard'],
            'start_year' => $data['start_year'],
            'start_month' => $data['start_month'],
            'cookery_ability' => $data['cookery_ability'],
            'motivation' => $data['motivation'],
            'courses' => $data['courses'],
            'course_id' => $data['course_id'] ?? null,
            'intake_date' => $data['intake_date'] ?? null,
            'locale' => $locale,
            'page_url' => (string) $request->input('page_url', $request->headers->get('referer', '')),
            'status' => 'pending',
        ]);

        AdminMailService::send(ApplicationSubmitted::class, $application);

        $message = UiTranslationService::translate(
            'Our team will be in contact with you shortly to discuss your application and schedule an informal telephone interview.',
            $locale
        );

        return response()->json([
            'success' => true,
            'data' => ['message' => $message],
        ]);
    }

    protected function joinWaitlist(Request $request)
    {
        $data = $request->validate([
            'course_id' => 'required|integer',
            'user_name' => 'nullable|string|max:200',
            'user_email' => 'required_without:email|nullable|email|max:100',
            'email' => 'required_without:user_email|nullable|email|max:100',
            'user_phone' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:50',
            'intake_date' => 'nullable|string|max:20',
            'intake_id' => 'nullable|string|max:50',
        ]);

        $course = Post::find((int) $data['course_id']);
        if (!$course || $course->post_type !== 'ca_course') {
            return response()->json(['success' => false, 'data' => 'Course not found.'], 404);
        }

        [$firstName, $lastName] = static::splitFullName((string) ($data['user_name'] ?? ''));

        $entry = Waitlist::create([
            'course_id' => (int) $data['course_id'],
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $data['user_email'] ?? $data['email'],
            'phone' => $data['user_phone'] ?? $data['phone'] ?? null,
            'notes' => $data['intake_date'] ?? $data['intake_id'] ?? null,
            'intake_date' => $data['intake_date'] ?? null,
            'locale' => PolylangService::currentLanguage(),
            'page_url' => (string) $request->input('page_url', $request->headers->get('referer', '')),
        ]);

        AdminMailService::send(WaitlistJoined::class, $entry, $course->post_title);

        return response()->json([
            'success' => true,
            'data' => [
                'message' => UiTranslationService::translate(
                    'You have been added to the waiting list. We will be in contact shortly.'
                ),
            ],
        ]);
    }

    /**
     * Split a single "full name" field into first and last name.
     * A blank name becomes "Guest" so admin lists never show an empty name.
     *
     * @return array{0:string,1:string}
     */
    protected static function splitFullName(string $fullName): array
    {
        $fullName = trim($fullName);
        if ($fullName === '') {
            return ['Guest', ''];
        }

        $parts = preg_split('/\s+/', $fullName, 2) ?: [$fullName];

        return [$parts[0], $parts[1] ?? ''];
    }
}
