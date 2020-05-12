<?php

namespace Bonnier\WP\Sitemap\Tests\integration\Observers\Tags;

use Bonnier\WP\Sitemap\Models\Sitemap;
use Bonnier\WP\Sitemap\WpBonnierSitemap;

class TagFilterValidationTest extends TagObserverTestCase
{
    public function testCanExcludeTagFromSitemapOnSave()
    {
        add_filter(WpBonnierSitemap::FILTER_TAG_ALLOWED_IN_SITEMAP, [$this, 'disallowTagFilter'], 10, 2);

        $posts = collect(array_fill(0, 5, ''))->map(function () {
            return $this->getPost();
        });
        $tag = $this->getTag([], $posts);
        $this->assertEquals(5, $tag->count);

        $this->assertNull($this->sitemapRepository->all()->first(function (Sitemap $sitemap) use ($tag) {
            return $sitemap->getWpType() === $tag->taxonomy;
        }));

        remove_filter(WpBonnierSitemap::FILTER_TAG_ALLOWED_IN_SITEMAP, [$this, 'disallowTagFilter'], 10);
    }

    public function disallowTagFilter(bool $allowed, \WP_Term $tag)
    {
        $this->assertIsBool($allowed);
        $this->assertInstanceOf(\WP_Term::class, $tag);
        return false;
    }
}
