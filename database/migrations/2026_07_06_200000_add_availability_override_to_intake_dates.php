<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ca_intake_dates', function (Blueprint $table) {
            // null = automatic (use enrolled_count vs max_students)
            // 'force_available' = admin override: always show as available regardless of count
            // 'force_full' = admin override: always show as fully booked regardless of count
            $table->string('availability_override', 30)->nullable()->after('enrolled_count');
            $table->text('admin_notes')->nullable()->after('availability_override');
        });
    }

    public function down(): void
    {
        Schema::table('ca_intake_dates', function (Blueprint $table) {
            $table->dropColumn(['availability_override', 'admin_notes']);
        });
    }
};
