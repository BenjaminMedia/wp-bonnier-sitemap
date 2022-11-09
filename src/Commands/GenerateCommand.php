<?php

namespace Bonnier\WP\Sitemap\Commands;

use Bonnier\WP\Sitemap\Helpers\LocaleHelper;
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

        $this->truncate(); //truncate database before generating a new sitemap.

        $postTypes = Utils::getValidPostTypes();



        // start <code for test if cronjob is working well(we can delete these codes when ever we want)
        // this line makes the hour and the minutes of the date for the last generated post in sitemap to 00:00. if the cronjob is working well or not
        $lastPostIdsForEachLanguage = [];
        $tmpPosts = get_posts([
            'numberposts' => 100,
            'post_type' => 'contenthub_composite',
            'offset' => 0,
            'post_status' => 'publish',
            'orderby' => 'post_modified',
            'order' => 'DESC'
        ]); 
        $allLangs = [];
        foreach ($tmpPosts as $tmpPost) {
            if(! in_array(pll_get_post_language($tmpPost->ID), $allLangs)){
                $allLangs[] = pll_get_post_language($tmpPost->ID);
            }
        } 
        foreach ($tmpPosts as $tmpPost) { 
            if(in_array(pll_get_post_language($tmpPost->ID), $allLangs)){
                $key = array_search(pll_get_post_language($tmpPost->ID), $allLangs);
                unset($allLangs[$key]);
                $lastPostIdsForEachLanguage[] = $tmpPost->ID;
            }
        }
        // end </code for test if cronjob is working well


        foreach ($postTypes as $postType) {
            $postCount = wp_count_posts($postType)->publish;
            $postProgress = make_progress_bar(sprintf('Generating %s sitemap entries for %s...', number_format($postCount), $postType), $postCount);
            $postOffset = 0;
            while ($posts = get_posts([
                'numberposts' => 100,
                'post_type' => $postType,
                'offset' => $postOffset,
                'post_status' => 'publish',
                'orderby' => 'post_modified',
                'order' => 'ASC'
            ])) {
                foreach ($posts as $post) {
                    if (apply_filters(WpBonnierSitemap::FILTER_POST_ALLOWED_IN_SITEMAP, true, $post)) {

                        // start <code for test if cronjob is working well(we can delete these codes when ever we want)
                        if(in_array($post->ID, $lastPostIdsForEachLanguage)){
                            $post->post_modified[11] = $post->post_modified[12] = $post->post_modified[14] = $post->post_modified[15] = 0;
                        }
                        // end </code for test if cronjob is working well

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
                if (!apply_filters(WpBonnierSitemap::FILTER_TAG_ALLOWED_IN_SITEMAP, true, $tag)) {
                    WpBonnierSitemap::instance()->getSitemapRepository()->deleteByTerm($tag);
                } else if ($this->findCategoryByTag($tag) || $tag->count < $minTagCount) {
                    WpBonnierSitemap::instance()->getSitemapRepository()->deleteByTerm($tag);
                } else {
                    WpBonnierSitemap::instance()->getSitemapRepository()->insertOrUpdateTag($tag);
                }
                $tagProgress->tick();
            }
            $tagOffset += 100;
        }
        $tagProgress->finish();

        $minAuthorPosts = Utils::getUserMinimumCount();
        $authors = get_users([
            'hide_empty' => false,
            'html' => false,
        ]);
        $authorsCount = count($authors);
        $authorsProgress = make_progress_bar(sprintf('Generating %s sitemap entries for authors...', number_format($authorsCount)), $authorsCount);
        foreach ($authors as $author) {
            $hasAuthorRole = in_array('author', $author->roles);
            $hasEditorRole = in_array('editor', $author->roles);
            if ($hasAuthorRole || $hasEditorRole) {
                $allowInSitemap = apply_filters(WpBonnierSitemap::FILTER_ALLOW_USER_IN_SITEMAP, true, $author->ID, $author);
                foreach (LocaleHelper::getLanguages() as $locale) {
                    $countUserPosts = Utils::countUserPosts($author, $locale);
                    if ($allowInSitemap && $countUserPosts >= $minAuthorPosts) {
                        WpBonnierSitemap::instance()->getSitemapRepository()->insertOrUpdateUser($author, $locale);
                    } else {
                        WpBonnierSitemap::instance()->getSitemapRepository()->deleteByUser($author, $locale);
                    }
                }
            }
            $authorsProgress->tick();
        }
        $authorsProgress->finish();

        \WP_CLI::success(sprintf('Done in %.2f seconds', microtime(true) - $start));
    }

    private function truncate()
    {
        global $wpdb;
        $start = microtime(true);
        $table  = $wpdb->prefix . 'bonnier_sitemap';
        $delete = $wpdb->query("TRUNCATE TABLE $table");
        \WP_CLI::success(sprintf('Done in %.2f seconds', microtime(true) - $start));
    }

    private function findCategoryByTag($tag)
    {
        $categories = get_categories([
            'slug' => $tag->slug,
        ]);
        foreach ($categories as $category) {
            if (pll_get_term_language($category->term_id) === pll_get_term_language($tag->term_id)) {
                return $category;
            }
        }
        return null;
    }
}
