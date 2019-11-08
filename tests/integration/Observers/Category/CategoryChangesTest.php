<?php

namespace Bonnier\WP\Sitemap\Tests\integration\Observers\Category;

use Bonnier\WP\Sitemap\Models\Sitemap;
use Bonnier\WP\Sitemap\Tests\integration\Observers\ObserverTestCase;

class CategoryChangesTest extends ObserverTestCase
{
    public function testCategoryCreationMakesSitemapEntry()
    {
        $category = $this->getCategory();

        $sitemaps = $this->sitemapRepository->all();
        $this->assertNotNull($sitemaps);
        $this->assertCount(1, $sitemaps);
        /** @var Sitemap $sitemap */
        $sitemap = $sitemaps->first();
        $this->assertSitemapEntryMatchesCategory($sitemap, $category);
    }

    public function testSitemapEntryIsUpdatedOnCategorySlugChange()
    {
        $category = $this->getCategory();

        $sitemaps = $this->sitemapRepository->all();
        $this->assertNotNull($sitemaps);
        $this->assertCount(1, $sitemaps);
        /** @var Sitemap $originalSitemap */
        $originalSitemap = $sitemaps->first();
        $this->assertSitemapEntryMatchesCategory($originalSitemap, $category);

        wp_update_category(['cat_ID' => $category->term_id, 'category_nicename' => 'new-category-slug']);
        $updatedCategory = get_term($category->term_id);

        $newSitemaps = $this->sitemapRepository->all();
        $this->assertNotNull($sitemaps);
        $this->assertCount(1, $sitemaps);
        /** @var Sitemap $newSitemap */
        $newSitemap = $newSitemaps->first();
        $this->assertSitemapEntryMatchesCategory($newSitemap, $updatedCategory);
    }
}
