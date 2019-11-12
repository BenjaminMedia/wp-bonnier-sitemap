<?php

namespace Bonnier\WP\Sitemap\Tests\integration\Observers\Tags;

class TagSlugChangesTest extends TagObserverTestCase
{
    public function testUpdatingTagNameUpdatesSitemapEntry()
    {
        $tag = $this->getTag();

        $sitemaps = $this->sitemapRepository->findAllBy('wp_type', $tag->taxonomy);
        $this->assertNotNull($sitemaps);
        $this->assertCount(1, $sitemaps);
        $this->assertSitemapEntryMatchesTag($sitemaps->first(), $tag);

        wp_update_term($tag->term_id, $tag->taxonomy, [
            'name' => 'Updated Tag',
            'slug' => 'updated-tag',
        ]);

        $updatedTag = get_tag($tag->term_id);

        $newSitemaps = $this->sitemapRepository->findAllBy('wp_type', $tag->taxonomy);
        $this->assertNotNull($newSitemaps);
        $this->assertCount(1, $newSitemaps);
        $this->assertSitemapEntryMatchesTag($newSitemaps->first(), $updatedTag);
    }
}
