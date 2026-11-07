<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id');
            $table->string('intake_date', 20)->nullable();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email', 100);
            $table->string('phone', 50)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('postcode', 20)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('passport_country', 100)->nullable();
            $table->string('visa', 10)->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 30)->default('pending'); // pending, confirmed, cancelled, expired
            $table->string('stripe_checkout_session_id', 255)->nullable()->index();
            $table->string('stripe_payment_intent_id', 255)->nullable();
            $table->unsignedInteger('deposit_amount_cents')->default(0);
            $table->string('currency', 3)->default('gbp');
            $table->string('invite_token', 64)->nullable()->unique();
            $table->timestamp('invite_expires_at')->nullable();
            $table->string('locale', 10)->default('en');
            $table->string('page_url', 500)->nullable();
            $table->timestamps();

            $table->index(['course_id', 'status']);
            $table->index(['course_id', 'intake_date', 'status']);
        });

        Schema::create('booking_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->string('document_type', 50); // cv, id_proof, certificate, other
            $table->string('original_name', 255);
            $table->string('hashed_name', 255); // GDPR-compliant hashed filename
            $table->string('cloudinary_public_id', 500)->nullable();
            $table->string('cloudinary_url', 1000)->nullable();
            $table->string('mime_type', 50);
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->timestamps();
        });

        Schema::create('waiting_lists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id');
            $table->string('intake_date', 20)->nullable();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email', 100);
            $table->string('phone', 50)->nullable();
            $table->string('status', 30)->default('waiting'); // waiting, invited, claimed, expired
            $table->string('invite_token', 64)->nullable()->unique();
            $table->timestamp('invite_token_expires_at')->nullable();
            $table->timestamp('invited_at')->nullable();
            $table->string('locale', 10)->default('en');
            $table->string('page_url', 500)->nullable();
            $table->timestamps();

            $table->index(['course_id', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_documents');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('waiting_lists');
    }
};
