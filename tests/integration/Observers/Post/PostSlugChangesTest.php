<?php

namespace Bonnier\WP\Sitemap\Tests\integration\Observers\Post;

use Bonnier\WP\Sitemap\Models\Sitemap;
use Bonnier\WP\Sitemap\Tests\integration\Observers\ObserverTestCase;

class PostSlugChangesTest extends ObserverTestCase
{
    public function testSitemapEntryIsUpdatedOnPostSlugChange()
    {
        $post = $this->getPost();
        $originalPermalink = get_permalink($post);

        $originalSitemaps = $this->sitemapRepository->all();
        $this->assertNotNull($originalSitemaps);
        $this->assertCount(2, $originalSitemaps);
        $originalSitemap = $originalSitemaps->first(function (Sitemap $sitemap) use ($post) {
            return $sitemap->getWpType() === $post->post_type;
        });
        $this->assertEquals($originalPermalink, $originalSitemap->getUrl());

        $this->updatePost($post->ID, [
            'post_name' => 'new-post-slug'
        ]);
        $updatedPost = get_post($post->ID);

        $newSitemaps = $this->sitemapRepository->all();
        $this->assertNotNull($newSitemaps);
        $this->assertCount(2, $newSitemaps);
        $newSitemap = $newSitemaps->first(function (Sitemap $sitemap) use ($post) {
            return $sitemap->getWpType() === $post->post_type;
        });
        $this->assertSitemapEntryMatchesPost($newSitemap, $updatedPost);
    }
}
