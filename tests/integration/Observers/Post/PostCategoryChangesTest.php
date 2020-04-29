<?php

namespace Bonnier\WP\Sitemap\Tests\integration\Observers\Post;

use Bonnier\WP\Sitemap\Models\Sitemap;
use Bonnier\WP\Sitemap\Tests\integration\Observers\ObserverTestCase;

class PostCategoryChangesTest extends ObserverTestCase
{
    public function testPostInSitemapWillBeUpdatedOnCategorySlugChange()
    {
        $category = $this->getCategory([], false);
        $post = $this->getPost([
            'post_category' => [$category->term_id],
        ]);

        $sitemaps = $this->sitemapRepository->all();
        $this->assertNotNull($sitemaps);
        $this->assertCount(2, $sitemaps);
        $this->assertSitemapEntryMatchesCategory($sitemaps->first(), $category);
        $this->assertSitemapEntryMatchesPost($sitemaps->last(), $post);

        wp_update_category(['cat_ID' => $category->term_id, 'category_nicename' => 'post-category-changed']);

        $updatedCategory = get_term($category->term_id);
        $updatedPost = get_post($post->ID);

        $newSitemaps = $this->sitemapRepository->all();
        $this->assertNotNull($newSitemaps);
        $this->assertCount(2, $newSitemaps);
        $this->assertSitemapEntryMatchesCategory($newSitemaps->first(), $updatedCategory);
        $this->assertSitemapEntryMatchesPost($newSitemaps->last(), $updatedPost);
    }

    public function testPostInSitemapWillBeUpdatedOnCategoryDelete()
    {
        $category = $this->getCategory([], false);
        $post = $this->getPost([
            'post_category' => [$category->term_id]
        ]);

        $sitemaps = $this->sitemapRepository->all();
        $this->assertNotNull($sitemaps);
        $this->assertCount(2, $sitemaps);
        $this->assertSitemapEntryMatchesCategory($sitemaps->first(), $category);
        $this->assertSitemapEntryMatchesPost($sitemaps->last(), $post);

        wp_delete_category($category->term_id);

        $updatedPost = get_post($post->ID);

        $newSitemaps = $this->sitemapRepository->all();
        $this->assertNotNull($newSitemaps);
        $this->assertCount(2, $newSitemaps); // Uncategorized category created
        $this->assertSitemapEntryMatchesPost($newSitemaps->first(), $updatedPost);
    }

    public function testPostInSitemapWillBeUpdatedOnCategoryAncestorSlugChange()
    {
        $parent = $this->getCategory();
        $category = $this->getCategory(['parent' => $parent->term_id], false);
        $post = $this->getPost(['post_category' => [$category->term_id]]);

        $sitemaps = $this->sitemapRepository->all();
        $this->assertNotNull($sitemaps);
        $this->assertCount(4, $sitemaps);
        $this->assertSitemapEntryMatchesCategory($sitemaps->first(function (Sitemap $sitemap) use ($parent) {
            return $sitemap->getWpType() === $parent->taxonomy && $sitemap->getWpID() === $parent->term_id;
        }), $parent);
        $this->assertSitemapEntryMatchesCategory($sitemaps->first(function (Sitemap $sitemap) use ($category) {
            return $sitemap->getWpType() === $category->taxonomy && $sitemap->getWpID() === $category->term_id;
        }), $category);
        $this->assertSitemapEntryMatchesPost($sitemaps->first(function (Sitemap $sitemap) use ($post) {
            return $sitemap->getWpType() === $post->post_type && $sitemap->getWpID() === $post->ID;
        }), $post);

        wp_update_category(['cat_ID' => $parent->term_id, 'category_nicename' => 'new-ancestor-slug']);

        $updatedParent = get_term($parent->term_id);
        $updatedCategory = get_term($category->term_id);
        $updatedPost = get_post($post->ID);

        $newSitemaps = $this->sitemapRepository->all();
        $this->assertNotNull($newSitemaps);
        $this->assertCount(4, $newSitemaps);
        $this->assertSitemapEntryMatchesCategory($newSitemaps->first(function (Sitemap $sitemap) use ($updatedParent) {
            return $sitemap->getWpType() === $updatedParent->taxonomy && $sitemap->getWpID() === $updatedParent->term_id;
        }), $updatedParent);
        $this->assertSitemapEntryMatchesCategory($newSitemaps->first(function (Sitemap $sitemap) use ($updatedCategory) {
            return $sitemap->getWpType() === $updatedCategory->taxonomy && $sitemap->getWpID() === $updatedCategory->term_id;
        }), $updatedCategory);
        $this->assertSitemapEntryMatchesPost($newSitemaps->first(function (Sitemap $sitemap) use ($updatedPost) {
            return $sitemap->getWpType() === $updatedPost->post_type && $sitemap->getWpID() === $updatedPost->ID;
        }), $updatedPost);
    }

    public function testPostInSitemapWillBeUpdatedOnCategoryAncestorDelete()
    {
        $parent = $this->getCategory();
        $category = $this->getCategory(['parent' => $parent->term_id], false);
        $post = $this->getPost(['post_category' => [$category->term_id]]);

        $sitemaps = $this->sitemapRepository->all();
        $this->assertNotNull($sitemaps);
        $this->assertCount(4, $sitemaps);
        $this->assertSitemapEntryMatchesCategory($sitemaps->first(function (Sitemap $sitemap) use ($parent) {
            return $sitemap->getWpType() === $parent->taxonomy && $sitemap->getWpID() === $parent->term_id;
        }), $parent);
        $this->assertSitemapEntryMatchesCategory($sitemaps->first(function (Sitemap $sitemap) use ($category) {
            return $sitemap->getWpType() === $category->taxonomy && $sitemap->getWpID() === $category->term_id;
        }), $category);
        $this->assertSitemapEntryMatchesPost($sitemaps->first(function (Sitemap $sitemap) use ($post) {
            return $sitemap->getWpType() === $post->post_type && $sitemap->getWpID() === $post->ID;
        }), $post);

        wp_delete_category($parent->term_id);

        $updatedCategory = get_term($category->term_id);
        $updatedPost = get_post($post->ID);

        $newSitemaps = $this->sitemapRepository->all();
        $this->assertNotNull($newSitemaps);
        $this->assertCount(4, $newSitemaps); // Uncategorized created and the post is moved there.
        $this->assertSitemapEntryMatchesCategory($newSitemaps->first(function (Sitemap $sitemap) use ($updatedCategory) {
            return $sitemap->getWpType() === $updatedCategory->taxonomy && $sitemap->getWpID() === $updatedCategory->term_id;
        }), $updatedCategory);
        $this->assertSitemapEntryMatchesPost($newSitemaps->first(function (Sitemap $sitemap) use ($updatedPost) {
            return $sitemap->getWpType() === $updatedPost->post_type && $sitemap->getWpID() === $updatedPost->ID;
        }), $updatedPost);
        $this->assertNull($newSitemaps->first(function (Sitemap $sitemap) use ($parent) {
            return $sitemap->getWpType() === $parent->taxonomy && $sitemap->getWpID() === $parent->term_id;
        }));
    }

    public function testPostInSitemapWillBeUpdatedOnCategoryChange()
    {
        $category = $this->getCategory([], false);
        $otherCategory = $this->getCategory([], false);
        $post = $this->getPost([
            'post_category' => [$category->term_id],
        ]);

        $sitemaps = $this->sitemapRepository->all();
        $this->assertNotNull($sitemaps);
        $this->assertCount(2, $sitemaps);
        $this->assertSitemapEntryMatchesCategory($sitemaps->first(), $category);
        $this->assertSitemapEntryMatchesPost($sitemaps->last(), $post);

        $this->updatePost($post->ID, [
            'post_category' => [$otherCategory->term_id]
        ]);

        $updatedPost = get_post($post->ID);

        $newSitemaps = $this->sitemapRepository->all();
        $this->assertNotNull($newSitemaps);
        $this->assertCount(2, $newSitemaps);

        $categorySitemap = $newSitemaps->first(function (Sitemap $sitemap) use ($otherCategory) {
            return $sitemap->getWpType() === $otherCategory->taxonomy;
        });
        $postSitemap = $newSitemaps->first(function (Sitemap $sitemap) use ($updatedPost) {
            return $sitemap->getWpType() === $updatedPost->post_type;
        });

        $this->assertSitemapEntryMatchesCategory($categorySitemap, $otherCategory);
        $this->assertSitemapEntryMatchesPost($postSitemap, $updatedPost);
    }
}
