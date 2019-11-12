<?php

namespace Bonnier\WP\Sitemap\Tests\integration\Observers\Post;

use Bonnier\WP\Sitemap\Models\Sitemap;
use Bonnier\WP\Sitemap\Tests\integration\Observers\ObserverTestCase;

class PostStatusChangesTest extends ObserverTestCase
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

    public function testSitemapEntryNotCreatedForDrafts()
    {
        $this->getPost([
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
