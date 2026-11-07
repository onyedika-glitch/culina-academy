<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Create WordPress core tables with exact column names from the MySQL dump
        // These match the wp_ prefix after SERVMASK_PREFIX replacement

        $tables = [
            "CREATE TABLE IF NOT EXISTS \"wp_users\" (
                \"ID\" bigserial NOT NULL PRIMARY KEY,
                \"user_login\" varchar(60) DEFAULT '',
                \"user_pass\" varchar(255) DEFAULT '',
                \"user_nicename\" varchar(50) DEFAULT '',
                \"user_email\" varchar(100) DEFAULT '',
                \"user_url\" varchar(100) DEFAULT '',
                \"user_registered\" timestamp DEFAULT NULL,
                \"user_activation_key\" varchar(255) DEFAULT '',
                \"user_status\" int DEFAULT 0,
                \"display_name\" varchar(250) DEFAULT ''
            )",
            "CREATE TABLE IF NOT EXISTS \"wp_usermeta\" (
                \"umeta_id\" bigserial NOT NULL PRIMARY KEY,
                \"user_id\" bigint DEFAULT 0,
                \"meta_key\" varchar(255) DEFAULT NULL,
                \"meta_value\" text DEFAULT NULL
            )",
            "CREATE TABLE IF NOT EXISTS \"wp_terms\" (
                \"term_id\" bigserial NOT NULL PRIMARY KEY,
                \"name\" varchar(200) DEFAULT '',
                \"slug\" varchar(200) DEFAULT '',
                \"term_group\" bigint DEFAULT 0
            )",
            "CREATE TABLE IF NOT EXISTS \"wp_term_taxonomy\" (
                \"term_taxonomy_id\" bigserial NOT NULL PRIMARY KEY,
                \"term_id\" bigint DEFAULT 0,
                \"taxonomy\" varchar(32) DEFAULT '',
                \"description\" text DEFAULT '',
                \"parent\" bigint DEFAULT 0,
                \"count\" bigint DEFAULT 0
            )",
            "CREATE TABLE IF NOT EXISTS \"wp_term_relationships\" (
                \"object_id\" bigint DEFAULT 0,
                \"term_taxonomy_id\" bigint DEFAULT 0,
                \"term_order\" int DEFAULT 0,
                PRIMARY KEY (\"object_id\", \"term_taxonomy_id\")
            )",
            "CREATE TABLE IF NOT EXISTS \"wp_termmeta\" (
                \"meta_id\" bigserial NOT NULL PRIMARY KEY,
                \"term_id\" bigint DEFAULT 0,
                \"meta_key\" varchar(255) DEFAULT NULL,
                \"meta_value\" text DEFAULT NULL
            )",
            "CREATE TABLE IF NOT EXISTS \"wp_posts\" (
                \"ID\" bigserial NOT NULL PRIMARY KEY,
                \"post_author\" bigint DEFAULT 0,
                \"post_date\" timestamp DEFAULT NULL,
                \"post_date_gmt\" timestamp DEFAULT NULL,
                \"post_content\" text DEFAULT '',
                \"post_title\" text DEFAULT '',
                \"post_excerpt\" text DEFAULT '',
                \"post_status\" varchar(20) NOT NULL DEFAULT 'publish',
                \"comment_status\" varchar(20) NOT NULL DEFAULT 'open',
                \"ping_status\" varchar(20) NOT NULL DEFAULT 'open',
                \"post_password\" varchar(255) DEFAULT '',
                \"post_name\" varchar(200) DEFAULT '',
                \"to_ping\" text DEFAULT '',
                \"pinged\" text DEFAULT '',
                \"post_modified\" timestamp DEFAULT NULL,
                \"post_modified_gmt\" timestamp DEFAULT NULL,
                \"post_content_filtered\" text DEFAULT '',
                \"post_parent\" bigint DEFAULT 0,
                \"guid\" varchar(255) DEFAULT '',
                \"menu_order\" int DEFAULT 0,
                \"post_type\" varchar(20) NOT NULL DEFAULT 'post',
                \"post_mime_type\" varchar(100) DEFAULT '',
                \"comment_count\" bigint DEFAULT 0
            )",
            "CREATE TABLE IF NOT EXISTS \"wp_postmeta\" (
                \"meta_id\" bigserial NOT NULL PRIMARY KEY,
                \"post_id\" bigint DEFAULT 0,
                \"meta_key\" varchar(255) DEFAULT NULL,
                \"meta_value\" text DEFAULT NULL
            )",
            "CREATE TABLE IF NOT EXISTS \"wp_comments\" (
                \"comment_ID\" bigserial NOT NULL PRIMARY KEY,
                \"comment_post_ID\" bigint DEFAULT 0,
                \"comment_author\" varchar(255) DEFAULT '',
                \"comment_author_email\" varchar(100) DEFAULT '',
                \"comment_author_url\" varchar(200) DEFAULT '',
                \"comment_author_IP\" varchar(100) DEFAULT '',
                \"comment_date\" timestamp DEFAULT NULL,
                \"comment_date_gmt\" timestamp DEFAULT NULL,
                \"comment_content\" text DEFAULT '',
                \"comment_karma\" int DEFAULT 0,
                \"comment_approved\" varchar(20) NOT NULL DEFAULT '1',
                \"comment_agent\" varchar(255) DEFAULT '',
                \"comment_type\" varchar(20) NOT NULL DEFAULT 'comment',
                \"comment_parent\" bigint DEFAULT 0,
                \"user_id\" bigint DEFAULT 0
            )",
            "CREATE TABLE IF NOT EXISTS \"wp_commentmeta\" (
                \"meta_id\" bigserial NOT NULL PRIMARY KEY,
                \"comment_id\" bigint DEFAULT 0,
                \"meta_key\" varchar(255) DEFAULT NULL,
                \"meta_value\" text DEFAULT NULL
            )",
            "CREATE TABLE IF NOT EXISTS \"wp_options\" (
                \"option_id\" bigserial NOT NULL PRIMARY KEY,
                \"option_name\" varchar(191) DEFAULT '',
                \"option_value\" text DEFAULT '',
                \"autoload\" varchar(20) NOT NULL DEFAULT 'yes'
            )",
            "CREATE TABLE IF NOT EXISTS \"wp_links\" (
                \"link_id\" bigserial NOT NULL PRIMARY KEY,
                \"link_url\" varchar(255) DEFAULT '',
                \"link_name\" varchar(255) DEFAULT '',
                \"link_image\" varchar(255) DEFAULT '',
                \"link_target\" varchar(25) DEFAULT '',
                \"link_description\" varchar(255) DEFAULT '',
                \"link_visible\" varchar(20) NOT NULL DEFAULT 'Y',
                \"link_owner\" bigint NOT NULL DEFAULT 1,
                \"link_rating\" int DEFAULT 0,
                \"link_updated\" timestamp DEFAULT NULL,
                \"link_rel\" varchar(255) DEFAULT '',
                \"link_notes\" text DEFAULT '',
                \"link_rss\" varchar(255) DEFAULT ''
            )",
        ];

        foreach ($tables as $sql) {
            try {
                DB::statement($sql);
            } catch (\Exception $e) {
                echo "Warning: " . $e->getMessage() . "\n";
            }
        }

        // Create indexes
        $indexes = [
            "CREATE INDEX IF NOT EXISTS wp_postmeta_post_id_idx ON wp_postmeta (post_id)",
            "CREATE INDEX IF NOT EXISTS wp_postmeta_meta_key_idx ON wp_postmeta (meta_key)",
            "CREATE INDEX IF NOT EXISTS wp_posts_post_type_idx ON wp_posts (post_type)",
            "CREATE INDEX IF NOT EXISTS wp_posts_post_status_idx ON wp_posts (post_status)",
            "CREATE INDEX IF NOT EXISTS wp_posts_post_name_idx ON wp_posts (post_name)",
            "CREATE INDEX IF NOT EXISTS wp_posts_post_parent_idx ON wp_posts (post_parent)",
            "CREATE INDEX IF NOT EXISTS wp_posts_post_date_idx ON wp_posts (post_date)",
            "CREATE INDEX IF NOT EXISTS wp_usermeta_user_id_idx ON wp_usermeta (user_id)",
            "CREATE INDEX IF NOT EXISTS wp_usermeta_meta_key_idx ON wp_usermeta (meta_key)",
            "CREATE INDEX IF NOT EXISTS wp_options_option_name_idx ON wp_options (option_name)",
            "CREATE INDEX IF NOT EXISTS wp_term_taxonomy_taxonomy_idx ON wp_term_taxonomy (taxonomy)",
            "CREATE INDEX IF NOT EXISTS wp_term_relationships_tt_id_idx ON wp_term_relationships (term_taxonomy_id)",
        ];
        foreach ($indexes as $sql) {
            try {
                DB::statement($sql);
            } catch (\Exception $e) {
                // Index may already exist
            }
        }

        // Create unique indexes
        $uniqueIndexes = [
            "CREATE UNIQUE INDEX IF NOT EXISTS wp_users_user_login_idx ON wp_users (user_login)",
            "CREATE UNIQUE INDEX IF NOT EXISTS wp_options_option_name_unique ON wp_options (option_name)",
        ];
        foreach ($uniqueIndexes as $sql) {
            try {
                DB::statement($sql);
            } catch (\Exception $e) {}
        }
    }

    public function down(): void
    {
        $tables = [
            'wp_commentmeta', 'wp_comments', 'wp_postmeta', 'wp_posts',
            'wp_termmeta', 'wp_term_relationships', 'wp_term_taxonomy', 'wp_terms',
            'wp_usermeta', 'wp_users', 'wp_options', 'wp_links',
        ];
        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }
    }
};
