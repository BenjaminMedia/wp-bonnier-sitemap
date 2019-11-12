<?php

namespace Bonnier\WP\Sitemap\Tests\integration\Observers\Category;

use Bonnier\WP\Sitemap\Tests\integration\Observers\ObserverTestCase;

class CategoryStatusChangesTest extends ObserverTestCase
{
    public function testCategoryCreationMakesSitemapEntry()
    {
        $category = $this->getCategory();

        $sitemaps = $this->sitemapRepository->all();
        $this->assertNotNull($sitemaps);
        $this->assertCount(1, $sitemaps);
        $this->assertSitemapEntryMatchesCategory($sitemaps->first(), $category);
    }

    public function testDeletingCategoryRemovesSitemapEntry()
    {
        $category = $this->getCategory();

        $sitemaps = $this->sitemapRepository->all();
        $this->assertNotNull($sitemaps);
        $this->assertCount(1, $sitemaps);
        $this->assertSitemapEntryMatchesCategory($sitemaps->first(), $category);

        wp_delete_category($category->term_id);

        $this->assertNull($this->sitemapRepository->all());
    }

    public function testDeletingParentRemovesSitemapEntryAndUpdatesChildSitemapEntry()
    {
        $parent = $this->getCategory();
        $child = $this->getCategory(['parent' => $parent->term_id]);

        $sitemaps = $this->sitemapRepository->all();
        $this->assertNotNull($sitemaps);
        $this->assertCount(2, $sitemaps);
        $this->assertSitemapEntryMatchesCategory($sitemaps->first(), $parent);
        $this->assertSitemapEntryMatchesCategory($sitemaps->last(), $child);

        wp_delete_category($parent->term_id);

        $updatedChild = get_term($child->term_id);

        $newSitemaps = $this->sitemapRepository->all();
        $this->assertNotNull($newSitemaps);
        $this->assertCount(1, $newSitemaps);
        $this->assertSitemapEntryMatchesCategory($newSitemaps->first(), $updatedChild);
    }
}
