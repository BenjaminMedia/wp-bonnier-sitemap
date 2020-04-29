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
        $this->assertCount(2, $sitemaps);
        $this->assertSitemapEntryMatchesPost($sitemaps->first(function (Sitemap $sitemap) use ($post) {
            return $sitemap->getWpType() === $post->post_type;
        }), $post);
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
        $this->assertCount(2, $sitemaps);
        $this->assertSitemapEntryMatchesPost($sitemaps->first(function (Sitemap $sitemap) use ($post) {
            return $sitemap->getWpType() === $post->post_type;
        }), $post);

        $this->updatePost($post->ID, [
            'post_status' => 'draft'
        ]);
        $this->assertNull($this->sitemapRepository->all());
    }

    public function testSitemapEntryRemovedWhenPostIsDeleted()
    {
        $post = $this->getPost();

        $sitemaps = $this->sitemapRepository->all();
        $this->assertNotNull($sitemaps);
        $this->assertCount(2, $sitemaps);
        $this->assertSitemapEntryMatchesPost($sitemaps->first(function (Sitemap $sitemap) use ($post) {
            return $sitemap->getWpType() === $post->post_type;
        }), $post);

        $this->updatePost($post->ID, [
            'post_status' => 'trash'
        ]);
        $this->assertNull($this->sitemapRepository->all());
    }
}
