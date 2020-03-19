<?php

namespace Bonnier\WP\Sitemap\Observers\Dependents;

use Bonnier\WP\Sitemap\Helpers\LocaleHelper;
use Bonnier\WP\Sitemap\Helpers\Utils;
use Bonnier\WP\Sitemap\Observers\Interfaces\ObserverInterface;
use Bonnier\WP\Sitemap\Observers\Interfaces\SubjectInterface;
use Bonnier\WP\Sitemap\Observers\Subjects\UserSubject;
use Bonnier\WP\Sitemap\Repositories\SitemapRepository;
use Bonnier\WP\Sitemap\WpBonnierSitemap;
use Illuminate\Support\Arr;

class UserUpdateObserver implements ObserverInterface
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
        if ($subject->getType() !== UserSubject::UPDATE) {
            return;
        }

        $user = $subject->getUser();
        if (!$user) {
            return;
        }

        $allowInSitemap = apply_filters(WpBonnierSitemap::FILTER_ALLOW_USER_IN_SITEMAP, true, $user->ID, $user);
        $minPosts = Utils::getUserMinimumCount();
        foreach (LocaleHelper::getLanguages() as $locale) {
            if ($allowInSitemap && Utils::countUserPosts($user, $locale) >= $minPosts) {
                $this->sitemapRepository->insertOrUpdateUser($user, $locale);
            } else {
                $this->sitemapRepository->deleteByUser($user, $locale);
            }
        }
    }
}
