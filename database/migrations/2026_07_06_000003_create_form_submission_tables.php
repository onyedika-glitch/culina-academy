<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ca_applications', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 100);
            $table->string('surname', 100);
            $table->string('email', 100);
            $table->string('phone', 50);
            $table->string('country', 100)->nullable();
            $table->string('intl_code', 10)->nullable();
            $table->string('address', 255);
            $table->string('city', 100);
            $table->string('postcode', 20);
            $table->string('passport_country', 100);
            $table->string('visa', 10);
            $table->string('heard', 255);
            $table->string('start_year', 4);
            $table->string('start_month', 20);
            $table->string('cookery_ability', 100);
            $table->text('motivation');
            $table->json('courses');
            $table->unsignedBigInteger('course_id')->nullable();
            $table->string('intake_date', 20)->nullable();
            $table->string('locale', 10)->default('en');
            $table->string('page_url', 500)->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestamps();
        });

        Schema::create('ca_contact_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('email', 100);
            $table->string('phone', 50)->nullable();
            $table->text('message');
            $table->string('locale', 10)->default('en');
            $table->string('page_url', 500)->nullable();
            $table->timestamps();
        });

        Schema::table('ca_waitlist', function (Blueprint $table) {
            if (! Schema::hasColumn('ca_waitlist', 'intake_date')) {
                $table->string('intake_date', 20)->nullable()->after('course_id');
            }
            if (! Schema::hasColumn('ca_waitlist', 'locale')) {
                $table->string('locale', 10)->default('en')->after('notes');
            }
            if (! Schema::hasColumn('ca_waitlist', 'page_url')) {
                $table->string('page_url', 500)->nullable()->after('locale');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ca_contact_submissions');
        Schema::dropIfExists('ca_applications');

        Schema::table('ca_waitlist', function (Blueprint $table) {
            $columns = ['page_url', 'locale', 'intake_date'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('ca_waitlist', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
