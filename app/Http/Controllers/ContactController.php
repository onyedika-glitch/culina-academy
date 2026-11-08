<?php

namespace App\Http\Controllers;

use App\Mail\ContactFormSubmitted;
use App\Models\ContactSubmission;
use App\Services\AdminMailService;
use App\Services\CloudinaryService;
use App\Services\PolylangService;
use App\Services\UiTranslationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    public function show()
    {
        $page = cal_page_by_slug('contact-us');

        if ($page) {
            cal_bind_post($page);
        }

        $bodyClass = 'no-sidebar full-width-content cal-contact-template page-template-template-contact-us';

        return view('site.contact', compact('page', 'bodyClass'));
    }

    public function submit(Request $request)
    {
        $request->merge([
            'name' => $request->input('name', $request->input('cal_name')),
            'email' => $request->input('email', $request->input('cal_email')),
            'phone' => $request->input('phone', $request->input('cal_phone')),
            'message' => $request->input('message', $request->input('cal_message')),
        ]);

        $payload = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'phone' => 'nullable|string|max:50',
            'message' => 'nullable|string|max:5000',
            'attachment' => 'nullable|file|max:10240', // 10MB max for contact attachments
        ]);

        // Home page enquiry form has no message field; default to empty string.
        $payload['message'] = (string) ($payload['message'] ?? '');

        $locale = PolylangService::normalizeCode(
            (string) $request->input('page_language', PolylangService::currentLanguage())
        );

        $payload['locale'] = $locale;
        $payload['page_url'] = (string) $request->input('page_url', $request->headers->get('referer', ''));

        // Handle file upload to Cloudinary
        $file = $request->file('attachment');
        if ($file) {
            $error = CloudinaryService::validateFile($file, 10);
            if ($error) {
                return back()->withErrors(['attachment' => $error])->withInput();
            }

            $result = CloudinaryService::upload($file, 'culina-academy/contacts');
            if ($result) {
                $payload['cloudinary_public_id'] = $result['public_id'];
                $payload['cloudinary_url'] = $result['secure_url'];
                $payload['original_file_name'] = $result['original_name'];
                $payload['file_mime_type'] = $result['mime_type'];
                $payload['file_size_bytes'] = $result['size'];
            } else {
                Log::warning('Contact form file upload to Cloudinary failed.', [
                    'file' => $file->getClientOriginalName(),
                ]);
            }
        }

        ContactSubmission::create($payload);
        AdminMailService::send(ContactFormSubmitted::class, $payload);

        $successMessage = UiTranslationService::translate(
            'Thank you for your message. We will get back to you shortly.',
            $locale
        );

        if ($request->expectsJson() || $request->ajax() || $request->has('cal_name') || $request->input('action') === 'cal_enquiry_submit') {
            return response()->json([
                'success' => true,
                'data' => ['message' => $successMessage],
            ]);
        }

        session()->flash('success', $successMessage);

        return back();
    }
}
