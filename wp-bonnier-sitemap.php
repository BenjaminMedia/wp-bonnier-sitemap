<?php
/**
 * Plugin Name: WP Bonnier Sitemap
 * Version: 1.6.3
 * Plugin URI: https://github.com/BenjaminMedia/wp-bonnier-sitemap
 * Description: This plugin creates sitemaps with support for Polylang
 * Author: Bonnier Publications
 * License: GPL v3
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('plugins_loaded', function () {
    \Bonnier\WP\Sitemap\WpBonnierSitemap::boot();
}, 0);
