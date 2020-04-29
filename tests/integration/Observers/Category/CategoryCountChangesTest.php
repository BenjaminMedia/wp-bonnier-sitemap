<?php

namespace Bonnier\WP\Sitemap\Tests\integration\Observers\Category;

use Bonnier\WP\Sitemap\Models\Sitemap;
use Bonnier\WP\Sitemap\Tests\integration\Observers\ObserverTestCase;
use function foo\func;

class CategoryCountChangesTest extends ObserverTestCase
{
    public function testMakingCategoryWithoutPostsWillNotBeInSitemap()
    {
        $this->getCategory([], false);

        $this->assertNull($this->sitemapRepository->all());
    }

    public function testDraftingArticleRemovesCategoryFromSitemap()
    {
        $category = $this->getCategory([], false);
        $post = $this->getPost([
            'post_category' => [$category->term_id]
        ]);

        $sitemaps = $this->sitemapRepository->all();
        $this->assertNotNull($sitemaps);
        $this->assertCount(2, $sitemaps);
        $this->assertSitemapEntryMatchesCategory($sitemaps->first(function (Sitemap $sitemap) use ($category) {
            return $sitemap->getWpType() === $category->taxonomy;
        }), $category);
        $this->assertSitemapEntryMatchesPost($sitemaps->first(function (Sitemap $sitemap) use ($post) {
            return $sitemap->getWpType() === $post->post_type;
        }), $post);

        $this->updatePost($post->ID, [
            'post_status' => 'draft'
        ]);
        $this->assertNull($this->sitemapRepository->all());
    }
}
