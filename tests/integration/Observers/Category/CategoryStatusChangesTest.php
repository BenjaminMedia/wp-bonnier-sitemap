<?php

namespace Bonnier\WP\Sitemap\Tests\integration\Observers\Category;

use Bonnier\WP\Sitemap\Models\Sitemap;
use Bonnier\WP\Sitemap\Tests\integration\Observers\ObserverTestCase;

class CategoryStatusChangesTest extends ObserverTestCase
{
    public function testCategoryCreationMakesSitemapEntry()
    {
        $category = $this->getCategory();
        $sitemaps = $this->sitemapRepository->all();
        $this->assertNotNull($sitemaps);
        $this->assertCount(2, $sitemaps);
        $categoryEntry = $sitemaps->first(function (Sitemap $sitemap) use ($category) {
            return $sitemap->getWpType() === $category->taxonomy;
        });
        $this->assertSitemapEntryMatchesCategory($categoryEntry, $category);
    }

    public function testPostRemovedFromCategoryRemovesCategoryFromSitemap()
    {
        $category = $this->getCategory([], false);
        $otherCategory = $this->getCategory([], false);
        $post = $this->getPost([
            'post_category' => [$category->term_id]
        ]);

        $initialSitemaps = $this->sitemapRepository->all();
        $this->assertCount(2, $initialSitemaps);
        $categoryEntry = $initialSitemaps->first(function (Sitemap $sitemap) use ($category) {
            return $sitemap->getWpType() === $category->taxonomy;
        });
        $postEntry = $initialSitemaps->first(function (Sitemap $sitemap) use ($post) {
            return $sitemap->getWpType() === $post->post_type;
        });
        $this->assertSitemapEntryMatchesCategory($categoryEntry, $category);
        $this->assertSitemapEntryMatchesPost($postEntry, $post);

        $this->updatePost($post->ID, [
            'post_category' => [$otherCategory->term_id]
        ]);

        $newSitemaps = $this->sitemapRepository->all();
        $this->assertNotNull($newSitemaps);
        $this->assertCount(2, $newSitemaps);
        $this->assertNull($newSitemaps->first(function (Sitemap $sitemap) use ($category) {
            return $sitemap->getWpType() === $category->taxonomy && $sitemap->getWpID() === $category->term_id;
        }));
    }

    public function testDeletingCategoryRemovesSitemapEntry()
    {
        $category = $this->getCategory();

        $sitemaps = $this->sitemapRepository->all();
        $this->assertNotNull($sitemaps);
        $this->assertCount(2, $sitemaps);
        $this->assertSitemapEntryMatchesCategory($sitemaps->first(function (Sitemap $sitemap) use ($category) {
            return $sitemap->getWpType() === $category->taxonomy;
        }), $category);

        wp_delete_category($category->term_id);
        $this->assertNull($this->sitemapRepository->all()->first(function (Sitemap $sitemap) use ($category) {
            return $sitemap->getWpType() === $category->taxonomy && $sitemap->getWpID() === $category->term_id;
        }));
    }

    public function testDeletingParentRemovesSitemapEntryAndUpdatesChildSitemapEntry()
    {
        $parent = $this->getCategory();
        $child = $this->getCategory(['parent' => $parent->term_id]);

        $sitemaps = $this->sitemapRepository->all();
        $this->assertNotNull($sitemaps);
        $this->assertCount(4, $sitemaps);
        $this->assertSitemapEntryMatchesCategory($sitemaps->first(function (Sitemap $sitemap) use ($parent) {
            return $sitemap->getWpID() === $parent->term_id && $sitemap->getWpType() === $parent->taxonomy;
        }), $parent);
        $this->assertSitemapEntryMatchesCategory($sitemaps->first(function (Sitemap $sitemap) use ($child) {
            return $sitemap->getWpID() === $child->term_id && $sitemap->getWpType() === $child->taxonomy;
        }), $child);

        wp_delete_category($parent->term_id);

        $updatedChild = get_term($child->term_id);

        $newSitemaps = $this->sitemapRepository->all();
        $this->assertNotNull($newSitemaps);
        $this->assertCount(4, $newSitemaps); // Uncategorized category was added
        $this->assertSitemapEntryMatchesCategory($newSitemaps->first(function (Sitemap $sitemap) use ($updatedChild) {
            return $sitemap->getWpID() === $updatedChild->term_id && $sitemap->getWpType() === $updatedChild->taxonomy;
        }), $updatedChild);
        $this->assertNull($newSitemaps->first(function (Sitemap $sitemap) use ($parent) {
            return $sitemap->getWpType() === $parent->taxonomy && $sitemap->getWpID() === $parent->term_id;
        }));
    }
}
