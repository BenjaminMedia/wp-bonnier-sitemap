<?php

namespace Bonnier\WP\Sitemap\Observers\Dependents;

use Bonnier\WP\Sitemap\Helpers\LocaleHelper;
use Bonnier\WP\Sitemap\Observers\Interfaces\ObserverInterface;
use Bonnier\WP\Sitemap\Observers\Interfaces\SubjectInterface;
use Bonnier\WP\Sitemap\Observers\Subjects\UserSubject;
use Bonnier\WP\Sitemap\Repositories\SitemapRepository;

class UserDeleteObserver implements ObserverInterface
{
    private $sitemapRepository;

    public function __construct(SitemapRepository $sitemapRepository)
    {
        $this->sitemapRepository = $sitemapRepository;
    }

    /**
     * @param SubjectInterface|UserSubject $subject
     */
    public function update(SubjectInterface $subject)
    {
        if ($subject->getType() !== UserSubject::DELETE) {
            return;
        }

        $user = $subject->getUser();
        if (!$user) {
            return;
        }

        foreach (LocaleHelper::getLanguages() as $locale) {
            $this->sitemapRepository->deleteByUser($user, $locale);
        }
    }
}
