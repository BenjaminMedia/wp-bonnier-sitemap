<?php

namespace Bonnier\WP\Sitemap\Observers\Sitemaps;

use Bonnier\WP\Sitemap\Repositories\SitemapRepository;
use Bonnier\WP\Sitemap\Observers\Interfaces\ObserverInterface;
use Bonnier\WP\Sitemap\Observers\Interfaces\SubjectInterface;
use Bonnier\WP\Sitemap\Observers\TagSubject;

class TagSlugChangeObserver implements ObserverInterface
{
    private $sitemapRepository;

    public function __construct(SitemapRepository $sitemapRepository)
    {
        $this->sitemapRepository = $sitemapRepository;
    }

    /**
     * @param SubjectInterface|TagSubject $subject
     * @throws \Exception
     */
    public function update(SubjectInterface $subject)
    {
        if ($subject->getType() === TagSubject::UPDATE && $tag = $subject->getTag()) {

        }
    }
}
