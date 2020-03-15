<?php

namespace Bonnier\WP\Sitemap\Observers\Dependents;

use Bonnier\WP\Sitemap\Observers\Interfaces\ObserverInterface;
use Bonnier\WP\Sitemap\Observers\Interfaces\SubjectInterface;
use Bonnier\WP\Sitemap\Observers\Subjects\UserSubject;
use Bonnier\WP\Sitemap\Repositories\SitemapRepository;
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
        
        $meta = get_user_meta($user->ID);
        if ($meta && (Arr::get($meta, 'public.0', '0') === '1')) {
            $this->sitemapRepository->insertOrUpdateUser($user);
        } else {
            $this->sitemapRepository->deleteByUser($user);
        }
    }
}