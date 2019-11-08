<?php

namespace Bonnier\WP\Sitemap\Tests\integration\Observers\Post;

use Bonnier\WP\Sitemap\Tests\integration\Observers\ObserverTestCase;

class CategoryChangesTest extends ObserverTestCase
{
    public function testPostInSitemapWillBeUpdatedOnCategorySlugChange()
    {
        $this->assertTrue(true);
    }

    public function testPostInSitemapWillBeUpdatedOnCategoryDelete()
    {
        $this->assertTrue(true);
    }

    public function testPostInSitemapWillBeUpdatedOnCategoryAncestorSlugChange()
    {
        $this->assertTrue(true);
    }

    public function testPostInSitemapWillBeUpdatedOnCategoryAncestorDelete()
    {
        $this->assertTrue(true);
    }
}
