<?php

namespace Bonnier\WP\Sitemap\Database\Migrations;

class CreateSitemapTable implements Migration
{
    public static function migrate()
    {
        if (self::verify()) {
            return;
        }

        global $wpdb;
        $table = $wpdb->prefix . Migrate::TABLE;
        $charset = $wpdb->get_charset_collate();

        $sql = "
        SET sql_notes = 1;
        CREATE TABLE `$table` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `url` text CHARACTER SET utf8 NOT NULL,
          `locale` char(2) CHARACTER SET utf8 NOT NULL,
          `wp_id` int(11) unsigned NOT NULL,
          `wp_type` varchar(30) CHARACTER SET utf8 NOT NULL,
          `modified_at` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          UNIQUE KEY `wp_object` (`wp_id`, `wp_type`)
        ) $charset;
        SET sql_notes = 1;
        ";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Verify that the migration was run successfully
     *
     * @return bool
     */
    public static function verify(): bool
    {
        global $wpdb;
        $table = $wpdb->prefix . Migrate::TABLE;
        return $wpdb->get_var("SHOW TABLES LIKE '$table'") === $table;
    }
}
