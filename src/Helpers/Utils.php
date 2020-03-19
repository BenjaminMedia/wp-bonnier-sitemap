<?php

namespace Bonnier\WP\Sitemap\Helpers;

use Bonnier\WP\Sitemap\WpBonnierSitemap;

class Utils
{
    public static function getValidPostTypes()
    {
        $validPostTypes = array_filter(get_post_types(['public' => true]), function (string $postType) {
            return $postType !== 'attachment';
        });
        return apply_filters(WpBonnierSitemap::FILTER_ALLOWED_POST_TYPES, $validPostTypes);
    }

    public static function getPostTagMinimumCount()
    {
        return apply_filters(WpBonnierSitemap::FILTER_POST_TAG_MINIMUM_COUNT, 5);
    }

    public static function getUserMinimumCount()
    {
        return apply_filters(WpBonnierSitemap::FILTER_USER_MINIMUM_COUNT, 5);
    }

    public static function countUserPosts(\WP_User $user, string $locale)
    {
        $count = 0;
        foreach (self::getValidPostTypes() as $postType) {
            $query = new \WP_Query([
                'author' => $user->ID,
                'post_type' => $postType,
                'lang' => $locale,
                'posts_per_page' => -1
            ]);
            $count += $query->post_count;
        }
        return $count;
    }
}
