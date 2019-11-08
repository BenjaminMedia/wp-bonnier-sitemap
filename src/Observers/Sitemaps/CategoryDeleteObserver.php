<?php

namespace Bonnier\WP\Sitemap\Observers\Sitemaps;

use Bonnier\WP\Sitemap\Repositories\SitemapRepository;
use Bonnier\WP\Sitemap\Observers\CategorySubject;
use Bonnier\WP\Sitemap\Observers\Interfaces\ObserverInterface;
use Bonnier\WP\Sitemap\Observers\Interfaces\SubjectInterface;

class CategoryDeleteObserver implements ObserverInterface
{
    private $sitemapRepository;

    public function __construct(SitemapRepository $sitemapRepository)
    {
        $this->sitemapRepository = $sitemapRepository;
    }

    /**
     * @param SubjectInterface|CategorySubject $subject
     * @throws \Exception
     */
    public function update(SubjectInterface $subject)
    {
        if ($subject->getType() !== CategorySubject::DELETE) {
            return;
        }
        $category = $subject->getCategory();
        if (!$category) {
            return;
        }
    }
}
