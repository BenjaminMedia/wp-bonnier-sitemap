<?php

namespace Bonnier\WP\Sitemap\Observers\Dependents;

use Bonnier\WP\Sitemap\Helpers\Utils;
use Bonnier\WP\Sitemap\Repositories\SitemapRepository;
use Bonnier\WP\Sitemap\Observers\Interfaces\ObserverInterface;
use Bonnier\WP\Sitemap\Observers\Interfaces\SubjectInterface;
use Bonnier\WP\Sitemap\Observers\Subjects\TagSubject;
use Bonnier\WP\Sitemap\WpBonnierSitemap;

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
        if ($subject->getType() !== TagSubject::UPDATE) {
            return;
        }
        $tag = $subject->getTag();
        if (!$tag) {
            return;
        }
        if (
            !apply_filters(WpBonnierSitemap::FILTER_TAG_ALLOWED_IN_SITEMAP, true, $tag) ||
            $tag->count < Utils::getPostTagMinimumCount()
        ) {
            $this->sitemapRepository->deleteByTerm($tag);
            return;
        }
        $this->sitemapRepository->insertOrUpdateTag($tag);
    }
}
