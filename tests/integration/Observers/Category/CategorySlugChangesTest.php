<?php

namespace Bonnier\WP\Sitemap\Tests\integration\Observers\Category;

use Bonnier\WP\Sitemap\Tests\integration\Observers\ObserverTestCase;

class CategorySlugChangesTest extends ObserverTestCase
{
    public function testSitemapEntryIsUpdatedOnCategorySlugChange()
    {
        $category = $this->getCategory();

        $sitemaps = $this->sitemapRepository->all();
        $this->assertNotNull($sitemaps);
        $this->assertCount(1, $sitemaps);
        $this->assertSitemapEntryMatchesCategory($sitemaps->first(), $category);

        wp_update_category(['cat_ID' => $category->term_id, 'category_nicename' => 'new-category-slug']);
        $updatedCategory = get_term($category->term_id);

        $newSitemaps = $this->sitemapRepository->all();
        $this->assertNotNull($sitemaps);
        $this->assertCount(1, $sitemaps);
        $this->assertSitemapEntryMatchesCategory($newSitemaps->first(), $updatedCategory);
    }

    public function testChangingParentCategoryUpdatesSitemapEntryForChildAlso()
    {
        $parent = $this->getCategory();
        $child = $this->getCategory(['parent' => $parent->term_id]);

        $sitemaps = $this->sitemapRepository->all();
        $this->assertNotNull($sitemaps);
        $this->assertCount(2, $sitemaps);
        $this->assertSitemapEntryMatchesCategory($sitemaps->first(), $parent);
        $this->assertSitemapEntryMatchesCategory($sitemaps->last(), $child);

        wp_update_category(['cat_ID' => $parent->term_id, 'category_nicename' => 'new-parent-slug']);
        $updatedParent = get_term($parent->term_id);
        $updatedChild = get_term($child->term_id);

        $newSitemaps = $this->sitemapRepository->all();
        $this->assertNotNull($newSitemaps);
        $this->assertCount(2, $newSitemaps);
        $this->assertSitemapEntryMatchesCategory($newSitemaps->first(), $updatedParent);
        $this->assertSitemapEntryMatchesCategory($newSitemaps->last(), $updatedChild);
    }
}
