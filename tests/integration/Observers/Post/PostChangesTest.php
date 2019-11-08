<?php

namespace Bonnier\WP\Sitemap\Tests\integration\Observers\Post;

use Bonnier\WP\Sitemap\Models\Sitemap;
use Bonnier\WP\Sitemap\Tests\integration\Observers\ObserverTestCase;

class PostChangesTest extends ObserverTestCase
{
    public function testPostCreationMakesSitemapEntry()
    {
        $post = $this->getPost();

        $sitemaps = $this->sitemapRepository->all();
        $this->assertNotNull($sitemaps);
        $this->assertCount(1, $sitemaps);
        /** @var Sitemap $sitemap */
        $sitemap = $sitemaps->first();
        $this->assertSitemapEntryMatchesPost($sitemap, $post);
    }

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

    public function testSitemapEntryNotCreatedForDrafts()
    {
        $post = $this->getPost([
            'post_status' => 'draft'
        ]);
        $sitemaps = $this->sitemapRepository->all();
        $this->assertNull($sitemaps);
    }

    public function testSitemapEntryRemovedWhenPostIsDrafted()
    {
        $post = $this->getPost();

        $sitemaps = $this->sitemapRepository->all();
        $this->assertNotNull($sitemaps);
        $this->assertCount(1, $sitemaps);
        /** @var Sitemap $sitemap */
        $sitemap = $sitemaps->first();
        $this->assertSitemapEntryMatchesPost($sitemap, $post);

        $this->updatePost($post->ID, [
            'post_status' => 'draft'
        ]);
        $updatedSitemaps = $this->sitemapRepository->all();
        $this->assertNull($updatedSitemaps);
    }

    public function testSitemapEntryRemovedWhenPostIsDeleted()
    {
        $post = $this->getPost();

        $sitemaps = $this->sitemapRepository->all();
        $this->assertNotNull($sitemaps);
        $this->assertCount(1, $sitemaps);
        $sitemap = $sitemaps->first();
        $this->assertSitemapEntryMatchesPost($sitemap, $post);

        $this->updatePost($post->ID, [
            'post_status' => 'trash'
        ]);
        $updatedSitemaps = $this->sitemapRepository->all();
        $this->assertNull($updatedSitemaps);
    }
}
