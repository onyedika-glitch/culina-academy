<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Facades\Log;

class StripeService
{
    /**
     * Create a Stripe Checkout Session for a booking deposit.
     *
     * @param  Booking  $booking
     * @param  int  $amountCents  Amount in pence/cents
     * @param  string  $currency  e.g. 'gbp'
     * @return string|null  The checkout session URL (redirect user here), or null on failure
     */
    public static function createCheckoutSession(Booking $booking, int $amountCents = 50000, string $currency = 'gbp'): ?string
    {
        try {
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

            $courseName = $booking->course?->post_title ?? 'Culina Academy Course';

            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'mode' => 'payment',
                'customer_email' => $booking->email,
                'client_reference_id' => (string) $booking->id,
                'line_items' => [[
                    'price_data' => [
                        'currency' => $currency,
                        'unit_amount' => $amountCents,
                        'product_data' => [
                            'name' => 'Course Deposit — ' . $courseName,
                            'description' => 'Deposit payment for ' . $courseName . ($booking->intake_date ? ' (Intake: ' . $booking->intake_date . ')' : ''),
                        ],
                    ],
                    'quantity' => 1,
                ]],
                'success_url' => url('/booking/success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => url('/booking/cancelled') . '?booking_id=' . $booking->id,
                'metadata' => [
                    'booking_id' => $booking->id,
                    'course_id' => $booking->course_id,
                    'intake_date' => $booking->intake_date ?? '',
                ],
            ]);

            // Store the session ID on the booking
            $booking->update([
                'stripe_checkout_session_id' => $session->id,
                'deposit_amount_cents' => $amountCents,
                'currency' => $currency,
            ]);

            return $session->url;
        } catch (\Throwable $e) {
            Log::error('Stripe checkout session creation failed: ' . $e->getMessage(), [
                'booking_id' => $booking->id,
            ]);
            return null;
        }
    }

    /**
     * Construct and verify a Stripe webhook event.
     *
     * @param  string  $payload  Raw request body
     * @param  string  $sigHeader  Stripe-Signature header
     * @return \Stripe\Event
     *
     * @throws \Stripe\Exception\SignatureVerificationException
     */
    public static function constructWebhookEvent(string $payload, string $sigHeader): \Stripe\Event
    {
        return \Stripe\Webhook::constructEvent(
            $payload,
            $sigHeader,
            config('services.stripe.webhook_secret')
        );
    }

    /**
     * Retrieve a checkout session from Stripe.
     */
    public static function retrieveSession(string $sessionId): ?\Stripe\Checkout\Session
    {
        try {
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
            return \Stripe\Checkout\Session::retrieve($sessionId);
        } catch (\Throwable $e) {
            Log::error('Stripe session retrieval failed: ' . $e->getMessage());
            return null;
        }
    }
}
