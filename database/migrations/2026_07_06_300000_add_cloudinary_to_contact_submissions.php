<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ca_contact_submissions', function (Blueprint $table) {
            $table->string('cloudinary_public_id')->nullable()->after('page_url');
            $table->text('cloudinary_url')->nullable()->after('cloudinary_public_id');
            $table->string('original_file_name')->nullable()->after('cloudinary_url');
            $table->string('file_mime_type')->nullable()->after('original_file_name');
            $table->bigInteger('file_size_bytes')->nullable()->after('file_mime_type');
        });
    }

    public function down(): void
    {
        Schema::table('ca_contact_submissions', function (Blueprint $table) {
            $table->dropColumn([
                'cloudinary_public_id',
                'cloudinary_url',
                'original_file_name',
                'file_mime_type',
                'file_size_bytes',
            ]);
        });
    }
};
