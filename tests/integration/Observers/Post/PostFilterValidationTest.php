<?php

namespace Bonnier\WP\Sitemap\Tests\integration\Observers\Post;

use Bonnier\WP\Sitemap\Models\Sitemap;
use Bonnier\WP\Sitemap\Tests\integration\Observers\ObserverTestCase;
use Bonnier\WP\Sitemap\WpBonnierSitemap;

class PostFilterValidationTest extends ObserverTestCase
{
    public function testCanExcludePostFromSitemapOnSave()
    {
        add_filter(WpBonnierSitemap::FILTER_POST_ALLOWED_IN_SITEMAP, [$this, 'disallowPostFilter'], 10, 2);

        $post = $this->getPost();

        $this->assertNull($this->sitemapRepository->all()->first(function (Sitemap $sitemap) use ($post) {
            return $sitemap->getWpType() === $post->post_type;
        }));

        remove_filter(WpBonnierSitemap::FILTER_POST_ALLOWED_IN_SITEMAP, [$this, 'disallowPostFilter'], 10);
    }

    public function testPostInSitemapCanBeExcludedFromSitemapAfterTheFact()
    {
        $post = $this->getPost();

        $sitemaps = $this->sitemapRepository->all();
        $this->assertNotNull($sitemaps);
        $this->assertCount(2, $sitemaps);
        $this->assertSitemapEntryMatchesPost($sitemaps->first(function (Sitemap $sitemap) use ($post) {
            return $sitemap->getWpType() === $post->post_type;
        }), $post);

        add_filter(WpBonnierSitemap::FILTER_POST_ALLOWED_IN_SITEMAP, [$this, 'disallowPostFilter'], 10, 2);

        $this->updatePost($post->ID, ['post_name' => 'hidden-from-sitemap']);

        $this->assertNull($this->sitemapRepository->all()->first(function (Sitemap $sitemap) use ($post) {
            return $sitemap->getWpType() === $post->post_type;
        }));
        remove_filter(WpBonnierSitemap::FILTER_POST_ALLOWED_IN_SITEMAP, [$this, 'disallowPostFilter'], 10);
    }

    public function testExcludedPostIsStillExcludedIfCategoryIsUpdated()
    {
        add_filter(WpBonnierSitemap::FILTER_POST_ALLOWED_IN_SITEMAP, [$this, 'disallowPostFilter'], 10, 2);

        $category = $this->getCategory();
        $this->getPost(['post_category' => [$category->term_id]]);

        $sitemaps = $this->sitemapRepository->all();
        $this->assertNotNull($sitemaps);
        $this->assertCount(1, $sitemaps);
        $this->assertSitemapEntryMatchesCategory($sitemaps->first(), $category);

        wp_update_category(['cat_ID' => $category->term_id, 'category_nicename' => 'new-category-slug']);
        $updatedCategory = get_term($category->term_id);

        $newSitemaps = $this->sitemapRepository->all();
        $this->assertNotNull($newSitemaps);
        $this->assertCount(1, $newSitemaps);
        $this->assertSitemapEntryMatchesCategory($newSitemaps->first(), $updatedCategory);
        remove_filter(WpBonnierSitemap::FILTER_POST_ALLOWED_IN_SITEMAP, [$this, 'disallowPostFilter'], 10);
    }

    public function disallowPostFilter(bool $allowed, \WP_Post $post)
    {
        $this->assertIsBool($allowed);
        $this->assertInstanceOf(\WP_Post::class, $post);
        return false;
    }
}
