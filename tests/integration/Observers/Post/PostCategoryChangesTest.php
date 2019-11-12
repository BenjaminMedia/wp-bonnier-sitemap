<?php

namespace Bonnier\WP\Sitemap\Tests\integration\Observers\Post;

use Bonnier\WP\Sitemap\Tests\integration\Observers\ObserverTestCase;

class PostCategoryChangesTest extends ObserverTestCase
{
    public function testPostInSitemapWillBeUpdatedOnCategorySlugChange()
    {
        $category = $this->getCategory();
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
        $category = $this->getCategory();
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
        $this->assertCount(1, $newSitemaps);
        $this->assertSitemapEntryMatchesPost($newSitemaps->first(), $updatedPost);
    }

    public function testPostInSitemapWillBeUpdatedOnCategoryAncestorSlugChange()
    {
        $parent = $this->getCategory();
        $category = $this->getCategory(['parent' => $parent->term_id]);
        $post = $this->getPost(['post_category' => [$category->term_id]]);

        $sitemaps = $this->sitemapRepository->all();
        $this->assertNotNull($sitemaps);
        $this->assertCount(3, $sitemaps);
        $this->assertSitemapEntryMatchesCategory($sitemaps->get(0), $parent);
        $this->assertSitemapEntryMatchesCategory($sitemaps->get(1), $category);
        $this->assertSitemapEntryMatchesPost($sitemaps->get(2), $post);

        wp_update_category(['cat_ID' => $parent->term_id, 'category_nicename' => 'new-ancestor-slug']);

        $updatedParent = get_term($parent->term_id);
        $updatedCategory = get_term($category->term_id);
        $updatedPost = get_post($post->ID);

        $newSitemaps = $this->sitemapRepository->all();
        $this->assertNotNull($newSitemaps);
        $this->assertCount(3, $newSitemaps);
        $this->assertSitemapEntryMatchesCategory($newSitemaps->get(0), $updatedParent);
        $this->assertSitemapEntryMatchesCategory($newSitemaps->get(1), $updatedCategory);
        $this->assertSitemapEntryMatchesPost($newSitemaps->get(2), $updatedPost);
    }

    public function testPostInSitemapWillBeUpdatedOnCategoryAncestorDelete()
    {
        $parent = $this->getCategory();
        $category = $this->getCategory(['parent' => $parent->term_id]);
        $post = $this->getPost(['post_category' => [$category->term_id]]);

        $sitemaps = $this->sitemapRepository->all();
        $this->assertNotNull($sitemaps);
        $this->assertCount(3, $sitemaps);
        $this->assertSitemapEntryMatchesCategory($sitemaps->get(0), $parent);
        $this->assertSitemapEntryMatchesCategory($sitemaps->get(1), $category);
        $this->assertSitemapEntryMatchesPost($sitemaps->get(2), $post);

        wp_delete_category($parent->term_id);

        $updatedCategory = get_term($category->term_id);
        $updatedPost = get_post($post->ID);

        $newSitemaps = $this->sitemapRepository->all();
        $this->assertNotNull($newSitemaps);
        $this->assertCount(2, $newSitemaps);
        $this->assertSitemapEntryMatchesCategory($newSitemaps->first(), $updatedCategory);
        $this->assertSitemapEntryMatchesPost($newSitemaps->last(), $updatedPost);
    }
}
