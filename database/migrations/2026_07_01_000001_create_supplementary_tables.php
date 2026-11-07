<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Supplementary tables - WordPress tables are created by the import command

        Schema::create('ca_intake_dates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id');
            $table->date('intake_date');
            $table->string('status', 20)->default('available');
            $table->integer('max_students')->default(10);
            $table->integer('enrolled_count')->default(0);
            $table->timestamps();
        });

        Schema::create('ca_enrollments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('intake_id');
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email', 100);
            $table->string('phone', 50)->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestamps();
        });

        Schema::create('ca_waitlist', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id');
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email', 100);
            $table->string('phone', 50)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('locale', 20);
            $table->string('name', 50);
            $table->string('flag_code', 10)->nullable();
            $table->boolean('rtl')->default(false);
            $table->integer('term_id')->nullable();
        });

        Schema::create('post_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id');
            $table->unsignedBigInteger('language_id');
            $table->string('translation_group', 32)->index();
            $table->unique(['post_id', 'language_id']);
        });

        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->string('slug', 200)->unique();
            $table->string('language_code', 10)->nullable();
            $table->integer('term_id')->nullable();
            $table->timestamps();
        });

        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('menu_id');
            $table->bigInteger('parent_id')->default(0);
            $table->unsignedBigInteger('post_id')->nullable();
            $table->string('title', 255);
            $table->string('url', 255)->nullable();
            $table->string('target', 20)->nullable();
            $table->string('classes', 255)->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('seo_meta', function (Blueprint $table) {
            $table->id();
            $table->morphs('seoable');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('canonical_url')->nullable();
            $table->string('robots', 50)->nullable();
            $table->text('og_data')->nullable();
            $table->text('schema_data')->nullable();
            $table->string('focus_keyword', 255)->nullable();
            $table->timestamps();
            $table->unique(['seoable_id', 'seoable_type']);
        });

        Schema::create('page_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('blade_view', 100);
            $table->string('description', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('field_groups', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->string('slug', 200)->unique();
            $table->json('location_rules')->nullable();
            $table->json('fields')->nullable();
            $table->timestamps();
        });

        Schema::create('plugin_settings', function (Blueprint $table) {
            $table->id();
            $table->string('plugin', 100);
            $table->string('key', 191);
            $table->text('value')->nullable();
            $table->unique(['plugin', 'key']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $tables = [
            'plugin_settings', 'field_groups', 'page_templates', 'seo_meta',
            'menu_items', 'menus', 'post_translations', 'languages',
            'ca_waitlist', 'ca_enrollments', 'ca_intake_dates',
        ];
        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }
    }
};
