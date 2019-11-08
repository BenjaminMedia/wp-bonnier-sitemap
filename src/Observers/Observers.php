<?php

namespace Bonnier\WP\Sitemap\Observers;

use Bonnier\WP\Sitemap\Observers\Dependents\CategoryDeleteObserver;
use Bonnier\WP\Sitemap\Observers\Dependents\CategorySlugChangeObserver;
use Bonnier\WP\Sitemap\Observers\Dependents\PostChangeObserver;
use Bonnier\WP\Sitemap\Observers\Dependents\TagDeleteObserver;
use Bonnier\WP\Sitemap\Observers\Dependents\TagSlugChangeObserver;
use Bonnier\WP\Sitemap\Observers\Subjects\CategorySubject;
use Bonnier\WP\Sitemap\Observers\Subjects\PostSubject;
use Bonnier\WP\Sitemap\Observers\Subjects\TagSubject;
use Bonnier\WP\Sitemap\Repositories\SitemapRepository;

class Observers
{
    private static $sitemapRepository;

    /**
     * @param SitemapRepository $sitemapRepository
     */
    public static function bootstrap(SitemapRepository $sitemapRepository)
    {
        self::$sitemapRepository = $sitemapRepository;

        self::bootstrapCategorySubject();

        self::bootstrapPostSubject();

        self::bootstrapTagSubject();
    }

    public static function bootstrapCategorySubject()
    {
        $slugChangeObserver = new CategorySlugChangeObserver(self::$sitemapRepository);
        $deleteObserver = new CategoryDeleteObserver(self::$sitemapRepository);

        $categorySubject = new CategorySubject();
        $categorySubject->attach($slugChangeObserver);
        $categorySubject->attach($deleteObserver);
    }

    public static function bootstrapPostSubject()
    {
        $slugChangeObserver = new PostChangeObserver(self::$sitemapRepository);
        $postSubject = new PostSubject();
        $postSubject->attach($slugChangeObserver);
    }

    public static function bootstrapTagSubject()
    {
        $slugChangeObserver = new TagSlugChangeObserver(self::$sitemapRepository);
        $deleteObserver = new TagDeleteObserver(self::$sitemapRepository);
        $tagSubject = new TagSubject();
        $tagSubject->attach($slugChangeObserver);
        $tagSubject->attach($deleteObserver);
    }
}
