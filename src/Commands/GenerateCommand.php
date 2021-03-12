<?php

namespace Bonnier\WP\Sitemap\Commands;

use Bonnier\WP\Sitemap\Helpers\Utils;
use Bonnier\WP\Sitemap\WpBonnierSitemap;
use function WP_CLI\Utils\make_progress_bar;

class GenerateCommand extends \WP_CLI_Command
{
    /**
     * Generate sitemaps
     *
     * ## OPTIONS
     *
     * [--host=<host>]
     * : Set host name for proper loading of envs
     *
     * ## EXAMPLES
     *     wp bonnier sitemap generate sitemaps
     */
    public function sitemaps($args, $assocArgs)
    {
        if (isset($assocArgs['host'])) {
            $_SERVER['HOST_NAME'] = $assocArgs['host'];
        }

        $start = microtime(true);
        $postTypes = Utils::getValidPostTypes();
        foreach ($postTypes as $postType) {
            $postCount = wp_count_posts($postType)->publish;
            $postProgress = make_progress_bar(sprintf('Generating %s sitemap entries for %s...', number_format($postCount), $postType), $postCount);
            $postOffset = 0;
            while ($posts = get_posts([
                'numberposts' => 100,
                'post_type' => $postType,
                'offset' =>  $postOffset,
                'post_status' => 'publish',
            ])) {
                foreach ($posts as $post) {
                    if (apply_filters(WpBonnierSitemap::FILTER_POST_ALLOWED_IN_SITEMAP, true, $post)) {
                        WpBonnierSitemap::instance()->getSitemapRepository()->insertOrUpdatePost($post);
                    } else {
                        WpBonnierSitemap::instance()->getSitemapRepository()->deleteByPost($post);
                    }
                    $postProgress->tick();
                }
                $postOffset += 100;
            }
            $postProgress->finish();
        }

        $categoryCount = wp_count_terms('category');
        $categoryProgress = make_progress_bar(sprintf('Generating %s sitemap entries for categories...', number_format($categoryCount)), $categoryCount);
        $catOffset = 0;
        while ($categories = get_categories([
            'hide_empty' => false,
            'number' => 100,
            'offset' => $catOffset
        ])) {
            foreach ($categories as $category) {
                WpBonnierSitemap::instance()->getSitemapRepository()->insertOrUpdateCategory($category);
                $categoryProgress->tick();
            }
            $catOffset += 100;
        }
        $categoryProgress->finish();

        $minTagCount = Utils::getPostTagMinimumCount();
        $tagCount = wp_count_terms('post_tag');
        $tagProgress = make_progress_bar(sprintf('Generating %s sitemap entries for tags...', number_format($tagCount)), $tagCount);
        $tagOffset = 0;
        while ($tags = get_tags([
            'hide_empty' => false,
            'number' => 100,
            'offset' => $tagOffset
        ])) {
            foreach ($tags as $tag) {
                if ($tag->count >= $minTagCount) {
                    WpBonnierSitemap::instance()->getSitemapRepository()->insertOrUpdateTag($tag);
                }
                $tagProgress->tick();
            }
            $tagOffset += 100;
        }
        $tagProgress->finish();
        \WP_CLI::success(sprintf('Done in %.2f seconds', microtime(true) - $start));
    }
}
