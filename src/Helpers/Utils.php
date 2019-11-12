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
}
