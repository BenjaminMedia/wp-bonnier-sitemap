<?php

namespace Bonnier\WP\Sitemap\Tests\integration\Observers\Post;

use Bonnier\WP\Sitemap\Tests\integration\Observers\ObserverTestCase;

class PostSlugChangesTest extends ObserverTestCase
{
    public function testSitemapEntryIsUpdatedOnPostSlugChange()
    {
        $post = $this->getPost();
        $originalPermalink = get_permalink($post);

        $originalSitemaps = $this->sitemapRepository->all();
        $this->assertNotNull($originalSitemaps);
        $this->assertCount(1, $originalSitemaps);
        $originalSitemap = $originalSitemaps->first();
        $this->assertEquals($originalPermalink, $originalSitemap->getUrl());

        $this->updatePost($post->ID, [
            'post_name' => 'new-post-slug'
        ]);
        $updatedPost = get_post($post->ID);

        $newSitemaps = $this->sitemapRepository->all();
        $this->assertNotNull($newSitemaps);
        $this->assertCount(1, $newSitemaps);
        $newSitemap = $newSitemaps->first();
        $this->assertSitemapEntryMatchesPost($newSitemap, $updatedPost);
    }
}
