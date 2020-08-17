<?php

namespace Bonnier\WP\Sitemap\Observers\Dependents;

use Bonnier\WP\Sitemap\Observers\Subjects\CategorySubject;
use Bonnier\WP\Sitemap\Repositories\SitemapRepository;
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
        $this->sitemapRepository->deleteByTerm($category);
        if (!empty($children = $subject->getAffectedCategories())) {
            foreach ($children as $child) {
                do_action('edited_category', $child->term_id, $child->term_taxonomy_id);
            }
        }

        if (!empty($postIDs = $subject->getAffectedPosts())) {
            foreach ($postIDs as $postID) {
                if ($post = get_post($postID)) {
                    do_action('save_post', $post->ID, $post, true);
                }
            }
        }
    }
}
