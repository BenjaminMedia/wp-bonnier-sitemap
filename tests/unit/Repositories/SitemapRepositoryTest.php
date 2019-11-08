<?php

namespace Bonnier\WP\Sitemap\Tests\unit\Repositories;

use Bonnier\WP\Sitemap\Database\DB;
use Bonnier\WP\Sitemap\Repositories\SitemapRepository;
use Codeception\Test\Unit;

class SitemapRepositoryTest extends Unit
{
    public function testCanInstantiateRepository()
    {
        try {
            /** @var DB $database */
            $database = $this->makeEmpty(DB::class);
        } catch (\Exception $exception) {
            $this->fail(sprintf('Failed mocking DB (%s)', $exception->getMessage()));
        }
        try {
            $repo = new SitemapRepository($database);
        } catch (\Exception $exception) {
            $this->fail(sprintf('Failed instatiating repository (%s)', $exception->getMessage()));
            return;
        }
        $this->assertInstanceOf(SitemapRepository::class, $repo);
    }
}
