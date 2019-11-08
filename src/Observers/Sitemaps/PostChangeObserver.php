<?php

namespace Bonnier\WP\Sitemap\Observers\Sitemaps;

use Bonnier\WP\Sitemap\Repositories\SitemapRepository;
use Bonnier\WP\Sitemap\Observers\Interfaces\ObserverInterface;
use Bonnier\WP\Sitemap\Observers\Interfaces\SubjectInterface;
use Bonnier\WP\Sitemap\Observers\PostSubject;

class PostChangeObserver implements ObserverInterface
{
    private $sitemapRepository;

    public function __construct(SitemapRepository $sitemapRepository)
    {
        $this->sitemapRepository = $sitemapRepository;
    }

    /**
     * @param SubjectInterface|PostSubject $subject
     * @throws \Exception
     */
    public function update(SubjectInterface $subject)
    {
        if ($post = $subject->getPost()) {
            if ($post->post_status === 'publish') {
                $this->sitemapRepository->insertOrUpdateByPost($subject->getPost());
            } else {
                $this->sitemapRepository->deleteByPost($post);
            }
        }
    }
}
