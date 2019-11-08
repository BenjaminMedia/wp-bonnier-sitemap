<?php

namespace Bonnier\WP\Sitemap\Tests\integration\Observers\Post;

use Bonnier\WP\Sitemap\Tests\integration\Observers\ObserverTestCase;

class FilterValidationTest extends ObserverTestCase
{
    public function testCanExcludePostFromSitemapOnSave()
    {
        // Register filter
        // Create post
        // Assert empty sitemap
        $this->assertTrue(true);
    }

    public function testPostInSitemapCanBeExcludedFromSitemapAfterTheFact()
    {
        // Create post
        // Assert exists in sitemap
        // Register filter
        // Update post
        // Assert empty sitemap
        $this->assertTrue(true);
    }

    public function testExcludedPostIsStillExcludedIfCategoryIsUpdated()
    {
        // Register filter
        // Create Post on Category
        // Assert empty sitemap
        // Update category
        // Assert empty sitemap
        $this->assertTrue(true);
    }
}
