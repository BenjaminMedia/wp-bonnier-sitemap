<?php

namespace Bonnier\WP\Sitemap\Tests\integration\Observers\Tags;

class TagPostChangesTest extends TagObserverTestCase
{
    public function testUnpublishingPostsSettingTagPostCountBelowThresholdRemovesTagFromSitemap()
    {
        $posts = collect(array_fill(0, 5, ''))->map(function () {
            return $this->getPost();
        });
        $tag = $this->getTag([], $posts);
        $this->assertEquals(5, $tag->count);

        $sitemaps = $this->sitemapRepository->findAllBy('wp_type', $tag->taxonomy);
        $this->assertNotNull($sitemaps);
        $this->assertCount(1, $sitemaps);
        $this->assertSitemapEntryMatchesTag($sitemaps->first(), $tag);

        $post = $posts->first();
        $post->post_status = 'draft';
        wp_update_post($post);
        $tag = get_tag($tag->term_id);
        $this->assertEquals(4, $tag->count);
        $this->assertNull($this->sitemapRepository->findAllBy('wp_type', $tag->taxonomy));
    }

    public function testRemovingPostsSettingTagPostCountBelowThresholdRemovesTagFromSitemap()
    {
        $posts = collect(array_fill(0, 5, ''))->map(function () {
            return $this->getPost();
        });
        $tag = $this->getTag([], $posts);
        $this->assertEquals(5, $tag->count);

        $sitemaps = $this->sitemapRepository->findAllBy('wp_type', $tag->taxonomy);
        $this->assertNotNull($sitemaps);
        $this->assertCount(1, $sitemaps);
        $this->assertSitemapEntryMatchesTag($sitemaps->first(), $tag);

        $post = $posts->first();
        wp_set_post_tags($post->ID, '');
        $tag = get_tag($tag->term_id);
        $this->assertEquals(4, $tag->count);
        $this->assertNull($this->sitemapRepository->findAllBy('wp_type', $tag->taxonomy));
    }

    public function testAddingPostsSettingTagPostCountAboveThresholdAddsTagToSitemap()
    {
        $posts = collect(array_fill(0, 4,''))->map(function () {
            return $this->getPost();
        });
        $tag = $this->getTag([], $posts);
        $this->assertEquals(4, $tag->count);

        $this->assertNull($this->sitemapRepository->findAllBy('wp_type', $tag->taxonomy));

        $post = $this->getPost();
        wp_set_post_tags($post->ID, [$tag->term_id]);
        $tag = get_tag($tag->term_id);
        $this->assertEquals(5, $tag->count);
        $sitemaps = $this->sitemapRepository->findAllBy('wp_type', $tag->taxonomy);
        $this->assertNotNull($sitemaps);
        $this->assertCount(1, $sitemaps);
        $this->assertSitemapEntryMatchesTag($sitemaps->first(), $tag);
    }
}
