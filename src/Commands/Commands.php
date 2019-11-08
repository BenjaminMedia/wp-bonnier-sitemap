<?php


namespace Bonnier\WP\Sitemap\Commands;


class Commands
{
    private static $commands = [
        'generate' => GenerateCommand::class
    ];

    public static function register() {
        if (defined('WP_CLI') && WP_CLI) {
            collect(self::$commands)->each(function (string $class, string $prefix) {
                \WP_CLI::add_command(sprintf('bonnier sitemap %s', $prefix), $class);
            });
        }
    }
}
