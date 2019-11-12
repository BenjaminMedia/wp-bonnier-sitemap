<?php

namespace Bonnier\WP\Sitemap\Tests\integration\Observers\Tags;

use Bonnier\WP\Sitemap\Tests\integration\Observers\ObserverTestCase;
use Illuminate\Support\Collection;

class TagObserverTestCase extends ObserverTestCase
{
    protected function getTag(array $args = [], ?Collection $posts = null): \WP_Term
    {
        $tag = parent::getTag($args);
        if (!$posts) {
            $posts = collect(array_fill(0, 5, ''))->map(function () {
                return $this->getPost();
            });
        }
        $posts->each(function (\WP_Post $post) use ($tag) {
            wp_set_post_tags($post->ID, [$tag->term_id]);
        });
        return get_tag($tag->term_id);
    }
}
