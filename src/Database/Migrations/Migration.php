<?php

namespace Bonnier\WP\Sitemap\Database\Migrations;

interface Migration
{
    /**
     * Run the migration
     */
    public static function migrate();

    /**
     * Verify that the migration was run successfully
     *
     * @return bool
     */
    public static function verify(): bool;
}
