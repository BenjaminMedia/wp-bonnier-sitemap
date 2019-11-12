<?php

namespace Bonnier\WP\Sitemap\Tests\integration\Observers\Tags;

class TagStatusChangesTest extends TagObserverTestCase
{
    public function testCreatingTagAddsSitemapEntry()
    {
        $tag = $this->getTag();

        $sitemaps = $this->sitemapRepository->findAllBy('wp_type', $tag->taxonomy);
        $this->assertNotNull($sitemaps);
        $this->assertCount(1, $sitemaps);
        $this->assertSitemapEntryMatchesTag($sitemaps->first(), $tag);
    }

    public function testDeletingTagRemovesSitemapEntry()
    {
        $tag = $this->getTag();

        $sitemaps = $this->sitemapRepository->findAllBy('wp_type', $tag->taxonomy);
        $this->assertNotNull($sitemaps);
        $this->assertCount(1, $sitemaps);
        $this->assertSitemapEntryMatchesTag($sitemaps->first(), $tag);

        wp_delete_term($tag->term_id, $tag->taxonomy);

        $this->assertNull($this->sitemapRepository->findAllBy('wp_type', $tag->taxonomy));
    }
}
