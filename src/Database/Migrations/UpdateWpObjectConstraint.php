<?php

namespace Bonnier\WP\Sitemap\Database\Migrations;

use Illuminate\Support\Str;

class UpdateWpObjectConstraint implements Migration
{
    /**
     * @inheritDoc
     */
    public static function migrate()
    {
        if (self::verify()) {
            return;
        }

        global $wpdb;
        $table = $wpdb->prefix . Migrate::TABLE;

        $wpdb->query("ALTER TABLE `$table` DROP INDEX `wp_object`;");
        $wpdb->query("ALTER TABLE `$table` ADD CONSTRAINT `wp_locale_object` UNIQUE (`wp_id`, `locale`, `wp_type`);");
    }

    /**
     * @inheritDoc
     */
    public static function verify(): bool
    {
        global $wpdb;
        $table = $wpdb->prefix . Migrate::TABLE;
        $result = $wpdb->get_row("SHOW CREATE TABLE $table", ARRAY_A);
        return isset($result['Create Table']) && Str::contains($result['Create Table'], 'wp_locale_object');
    }
}
