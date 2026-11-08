<?php

namespace App\Http\Controllers;

use App\Mail\BookingConfirmed;
use App\Models\Booking;
use App\Models\IntakeDate;
use App\Services\AdminMailService;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\Exception\SignatureVerificationException;

class StripeWebhookController extends Controller
{
    /**
     * Handle incoming Stripe webhook events.
     *
     * POST /api/webhooks/stripe
     *
     * By using a hosted Stripe checkout session, card details never
     * touch our server, keeping the site completely PCI-DSS compliant.
     * Webhooks ensure the booking is confirmed regardless of whether
     * the user closes their browser after payment.
     */
    public function __invoke(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature', '');

        try {
            $event = StripeService::constructWebhookEvent($payload, $sigHeader);
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe webhook signature verification failed.', [
                'error' => $e->getMessage(),
            ]);

            return response('Invalid signature.', 400);
        } catch (\Throwable $e) {
            Log::error('Stripe webhook error: '.$e->getMessage());

            return response('Webhook error.', 400);
        }

        // Handle the event
        switch ($event->type) {
            case 'checkout.session.completed':
                $this->handleCheckoutCompleted($event->data->object);
                break;

            case 'checkout.session.expired':
                $this->handleCheckoutExpired($event->data->object);
                break;

            default:
                Log::info('Unhandled Stripe event: '.$event->type);
                break;
        }

        return response('OK', 200);
    }

    /**
     * When payment succeeds, confirm the booking.
     */
    protected function handleCheckoutCompleted($session): void
    {
        $booking = Booking::where('stripe_checkout_session_id', $session->id)->first();

        if (! $booking) {
            Log::warning('Stripe checkout.session.completed: no booking found.', [
                'session_id' => $session->id,
            ]);

            return;
        }

        if ($booking->status === 'confirmed') {
            Log::info('Booking #'.$booking->id.' already confirmed, skipping.');

            return;
        }

        // Update booking to confirmed
        $booking->update([
            'status' => 'confirmed',
            'stripe_payment_intent_id' => $session->payment_intent ?? null,
        ]);

        // Update intake enrolled count if we have an intake record
        if ($booking->intake_date) {
            $intake = IntakeDate::where('course_id', $booking->course_id)
                ->whereDate('intake_date', $booking->intake_date)
                ->first();
            if ($intake) {
                $intake->increment('enrolled_count');
            }
        }

        // Send confirmation email to student
        try {
            $courseTitle = $booking->course?->post_title ?? 'Culina Academy Course';
            Mail::to($booking->email)->send(new BookingConfirmed($booking, $courseTitle));
        } catch (\Throwable $e) {
            Log::error('Booking confirmation email failed: '.$e->getMessage());
        }

        // Notify admin
        AdminMailService::send(BookingConfirmed::class, $booking, $booking->course?->post_title);

        Log::info('Booking #'.$booking->id.' confirmed via Stripe webhook.');
    }

    /**
     * When checkout session expires, mark the booking as expired.
     */
    protected function handleCheckoutExpired($session): void
    {
        $booking = Booking::where('stripe_checkout_session_id', $session->id)->first();

        if ($booking && $booking->status === 'pending') {
            $booking->update(['status' => 'expired']);
            Log::info('Booking #'.$booking->id.' expired (Stripe session expired).');
        }
    }
}
