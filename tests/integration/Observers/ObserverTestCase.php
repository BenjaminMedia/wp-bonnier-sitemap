<?php

namespace Bonnier\WP\Sitemap\Tests\integration\Observers;

use Bonnier\WP\Sitemap\Observers\Observers;
use Bonnier\WP\Sitemap\Repositories\SitemapRepository;
use Bonnier\WP\Sitemap\Database\DB;
use Bonnier\WP\Sitemap\Tests\integration\TestCase;

class ObserverTestCase extends TestCase
{
    protected $sitemapRepository;

    public function _setUp()
    {
        parent::_setUp();

        try {
            $this->sitemapRepository = new SitemapRepository(new DB);
        } catch (\Exception $exception) {
            $this->fail(sprintf('Failed instantiating a LogRepository (%s)', $exception->getMessage()));
        }

        // Make sure we are admin so we may call edit_post();
        $userID = self::factory()->user->create(['role' => 'administrator']);
        wp_set_current_user($userID);

        Observers::bootstrap($this->sitemapRepository);
    }
}
