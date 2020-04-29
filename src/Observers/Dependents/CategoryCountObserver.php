<?php

namespace Bonnier\WP\Sitemap\Observers\Dependents;

use Bonnier\WP\Sitemap\Helpers\Utils;
use Bonnier\WP\Sitemap\Observers\Interfaces\ObserverInterface;
use Bonnier\WP\Sitemap\Observers\Interfaces\SubjectInterface;
use Bonnier\WP\Sitemap\Observers\Subjects\CategorySubject;
use Bonnier\WP\Sitemap\Repositories\SitemapRepository;

class CategoryCountObserver implements ObserverInterface
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
        if ($subject->getType() !== CategorySubject::COUNT) {
            return;
        }
        $category = $subject->getCategory();
        if (!$category) {
            return;
        }
        if ($this->hasPublishedPosts($category)) {
            $this->sitemapRepository->insertOrUpdateCategory($category);
        } else {
            $this->sitemapRepository->deleteByTerm($category);
        }
    }

    private function hasPublishedPosts(\WP_Term $category)
    {
        foreach (Utils::getValidPostTypes() as $postType) {
            $posts = get_posts([
                'post_type' => $postType,
                'category' => $category->term_id,
                'post_status' => 'publish'
            ]);
            if ($posts && count($posts) > 0) {
                return true;
            }
        }
        return false;
    }
}
