<?php

namespace Bonnier\WP\Sitemap;

use Bonnier\WP\Sitemap\Observers\Observers;
use Bonnier\WP\Sitemap\Repositories\SitemapRepository;
use Bonnier\WP\Sitemap\Commands\Commands;
use Bonnier\WP\Sitemap\Database\DB;
use Bonnier\WP\Sitemap\Database\Migrations\Migrate;

class WpBonnierSitemap
{
    public const FILTER_ALLOWED_POST_TYPES = 'sitemap_allowed_post_types';
    public const FILTER_POST_ALLOWED_IN_SITEMAP = 'post_allowed_in_sitemap';
    public const FILTER_POST_TAG_MINIMUM_COUNT = 'post_tag_minimum_count';
    public const FILTER_POST_PERMALINK = 'sitemap_post_permalink';
    public const FILTER_CATEGORY_PERMALINK = 'sitemap_category_permalink';
    public const FILTER_TAG_PERMALINK = 'sitemap_tag_permalink';

    private static $instance;

    private $dir;

    private $basename;

    private $pluginDir;

    private $pluginUrl;

    private $sitemapRepository;

    public function __construct()
    {
        add_option(Migrate::OPTION);
        Migrate::run();
        // Set plugin file variables
        $this->dir = __DIR__;
        $this->basename = plugin_basename($this->dir);
        $this->pluginDir = plugin_dir_path($this->dir);
        $this->pluginUrl = plugin_dir_url($this->dir);

        try {
            $this->sitemapRepository = new SitemapRepository(new DB());
        } catch (\Exception $exception) {
            wp_die($exception->getMessage());
        }

        Observers::bootstrap($this->sitemapRepository);
        Commands::register();
    }

    /**
     * Returns the instance of this class.
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self();
            /**
             * Run after the plugin has been loaded.
             */
            do_action('wp_bonnier_sitemap_loaded');
        }

        return self::$instance;
    }

    public static function boot()
    {
        self::$instance = new self();
    }

    /**
     * @return SitemapRepository
     */
    public function getSitemapRepository()
    {
        return $this->sitemapRepository;
    }
}
