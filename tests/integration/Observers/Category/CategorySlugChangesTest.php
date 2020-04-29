<?php

namespace Bonnier\WP\Sitemap\Tests\integration\Observers\Category;

use Bonnier\WP\Sitemap\Models\Sitemap;
use Bonnier\WP\Sitemap\Tests\integration\Observers\ObserverTestCase;

class CategorySlugChangesTest extends ObserverTestCase
{
    public function testSitemapEntryIsUpdatedOnCategorySlugChange()
    {
        $category = $this->getCategory();

        $sitemaps = $this->sitemapRepository->all();
        $this->assertNotNull($sitemaps);
        $this->assertCount(2, $sitemaps);
        $this->assertSitemapEntryMatchesCategory($sitemaps->first(function (Sitemap $sitemap) use ($category) {
            return $sitemap->getWpType() === $category->taxonomy;
        }), $category);

        wp_update_category(['cat_ID' => $category->term_id, 'category_nicename' => 'new-category-slug']);
        $updatedCategory = get_term($category->term_id);

        $newSitemaps = $this->sitemapRepository->all();
        $this->assertNotNull($sitemaps);
        $this->assertCount(2, $sitemaps);
        $this->assertSitemapEntryMatchesCategory($newSitemaps->first(function (Sitemap $sitemap) use ($updatedCategory) {
            return $sitemap->getWpType() === $updatedCategory->taxonomy;
        }), $updatedCategory);
    }

    public function testChangingParentCategoryUpdatesSitemapEntryForChildAlso()
    {
        $parent = $this->getCategory();
        $child = $this->getCategory(['parent' => $parent->term_id]);

        $sitemaps = $this->sitemapRepository->all();
        $this->assertNotNull($sitemaps);
        $this->assertCount(4, $sitemaps);
        $this->assertSitemapEntryMatchesCategory($sitemaps->first(function (Sitemap $sitemap) use ($parent) {
            return $sitemap->getWpType() === $parent->taxonomy && $sitemap->getWpID() === $parent->term_id;
        }), $parent);
        $this->assertSitemapEntryMatchesCategory($sitemaps->first(function (Sitemap $sitemap) use ($child) {
            return $sitemap->getWpType() === $child->taxonomy && $sitemap->getWpID() === $child->term_id;
        }), $child);

        wp_update_category(['cat_ID' => $parent->term_id, 'category_nicename' => 'new-parent-slug']);
        $updatedParent = get_term($parent->term_id);
        $updatedChild = get_term($child->term_id);

        $newSitemaps = $this->sitemapRepository->all();
        $this->assertNotNull($newSitemaps);
        $this->assertCount(4, $newSitemaps);
        $this->assertSitemapEntryMatchesCategory($newSitemaps->first(function (Sitemap $sitemap) use ($updatedParent) {
            return $sitemap->getWpType() === $updatedParent->taxonomy && $sitemap->getWpID() === $updatedParent->term_id;
        }), $updatedParent);
        $this->assertSitemapEntryMatchesCategory($newSitemaps->first(function (Sitemap $sitemap) use ($updatedChild) {
            return $sitemap->getWpType() === $updatedChild->taxonomy && $sitemap->getWpID() === $updatedChild->term_id;
        }), $updatedChild);
    }
}
