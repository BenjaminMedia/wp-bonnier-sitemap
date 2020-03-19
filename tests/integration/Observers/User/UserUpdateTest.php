<?php

namespace Bonnier\WP\Sitemap\Tests\integration\Observers\User;

class UserUpdateTest extends UserObserverTestCase
{
    public function testUpdatingUserUpdatesSitemapEntry()
    {
        $user = $this->getUser();

        $sitemaps = $this->sitemapRepository->findAllBy('wp_type', 'user');
        $this->assertNull($sitemaps);
        wp_update_user($user);
        $sitemaps = $this->sitemapRepository->findAllBy('wp_type', 'user');
        $this->assertNotNull($sitemaps);
        $this->assertCount(1, $sitemaps);
        $this->assertSitemapEntryMatchesUser($sitemaps->first(), $user);
    }
}
