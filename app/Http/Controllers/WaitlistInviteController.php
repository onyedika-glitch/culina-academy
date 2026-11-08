<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingDocument;
use App\Models\Post;
use App\Models\WaitingList;
use App\Services\AcfFieldService;
use App\Services\CloudinaryService;
use App\Services\PolylangService;
use App\Services\StripeService;
use Illuminate\Http\Request;

class WaitlistInviteController extends Controller
{
    /**
     * Show the invite claim page where a waitlisted student can
     * upload documents, pay their deposit, and claim their spot.
     *
     * GET /waitlist/invite/{token}
     */
    public function show(Request $request)
    {
        $token = $request->route('token');
        $entry = WaitingList::where('invite_token', $token)
            ->where('status', 'invited')
            ->first();

        if (! $entry) {
            abort(404, 'This invitation link is invalid or has already been used.');
        }

        if (! $entry->isInviteValid()) {
            $entry->update(['status' => 'expired']);

            return view('site.waitlist-invite-expired');
        }

        $course = Post::with('meta')->find($entry->course_id);

        $page = cal_page_by_slug('course-enrollment');
        if ($page) {
            cal_bind_post($page);
        }

        return view('site.waitlist-invite-claim', compact('entry', 'course', 'page'));
    }

    /**
     * Process the invite claim: validate files, upload to Cloudinary,
     * create booking, and redirect to Stripe checkout.
     *
     * POST /waitlist/invite/{token}/claim
     */
    public function claim(Request $request)
    {
        $token = $request->route('token');
        $entry = WaitingList::where('invite_token', $token)
            ->where('status', 'invited')
            ->first();

        if (! $entry) {
            abort(404, 'This invitation link is invalid or has already been used.');
        }

        if (! $entry->isInviteValid()) {
            $entry->update(['status' => 'expired']);

            return redirect()->route('home', ['locale' => PolylangService::currentLanguage() ?: 'en'])
                ->withErrors(['invite' => 'This invitation has expired.']);
        }

        $data = $request->validate([
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
            'documents.*' => 'file|max:5120',
            'document_types' => 'required|array',
            'document_types.*' => 'string|in:cv,id_proof,certificate,other',
        ]);

        // Validate files
        $files = $request->file('documents', []);
        foreach ($files as $file) {
            $error = CloudinaryService::validateFile($file);
            if ($error) {
                return back()->withErrors(['documents' => $error])->withInput();
            }
        }

        // Create the booking from the waitlist entry
        $booking = Booking::create([
            'course_id' => $entry->course_id,
            'intake_date' => $entry->intake_date,
            'first_name' => $entry->first_name,
            'last_name' => $entry->last_name,
            'email' => $entry->email,
            'phone' => $data['phone'],
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'postcode' => $data['postcode'] ?? null,
            'country' => $data['country'] ?? null,
            'passport_country' => $data['passport_country'] ?? null,
            'visa' => $data['visa'] ?? null,
            'notes' => $data['notes'] ?? null,
            'status' => 'pending',
            'locale' => $entry->locale ?? PolylangService::currentLanguage(),
            'page_url' => $request->headers->get('referer', ''),
        ]);

        // Upload documents
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
            }
        }

        // Mark waitlist entry as claimed
        $entry->update(['status' => 'claimed']);

        // Create Stripe Checkout Session
        $depositAmountCents = (int) (AcfFieldService::getField($booking->course_id, 'ca_deposit_amount', 500) * 100);
        $checkoutUrl = StripeService::createCheckoutSession($booking, $depositAmountCents);

        if (! $checkoutUrl) {
            return back()->withErrors(['payment' => 'Unable to start payment. Please try again or contact us.'])->withInput();
        }

        return redirect()->away($checkoutUrl);
    }
}
