<?php

namespace Bonnier\WP\Sitemap\Observers\Dependents;

use Bonnier\WP\Sitemap\Helpers\Utils;
use Bonnier\WP\Sitemap\Observers\Subjects\CategorySubject;
use Bonnier\WP\Sitemap\Observers\Subjects\PostSubject;
use Bonnier\WP\Sitemap\Repositories\SitemapRepository;
use Bonnier\WP\Sitemap\Observers\Interfaces\ObserverInterface;
use Bonnier\WP\Sitemap\Observers\Interfaces\SubjectInterface;
use WP_Term;

class CategorySlugChangeObserver implements ObserverInterface
{
    private $sitemapRepository;
    private $postObserver;

    public function __construct(SitemapRepository $sitemapRepository)
    {
        $this->sitemapRepository = $sitemapRepository;
        $this->postObserver = new PostChangeObserver($this->sitemapRepository);
    }

    /**
     * @param SubjectInterface|CategorySubject $subject
     * @throws \Exception
     */
    public function update(SubjectInterface $subject)
    {
        if ($subject->getType() !== CategorySubject::UPDATE) {
            return;
        }
        $category = $subject->getCategory();
        if (!$category) {
            return;
        }
        $existingEntry = $this->sitemapRepository->findByTerm($category);
        if ($existingEntry && $existingEntry->getUrl() === get_term_link($category->term_id, $category->taxonomy)) {
            return; // Slug has not changed since last entry no further processing needed
        }
        $this->sitemapRepository->insertOrUpdateCategory($category);

        $this->updateChildren($category);
        $this->updatePosts($category);
    }

    private function updateChildren(WP_Term $category)
    {
        if ($children = get_categories(['parent' => $category->term_id, 'hide_empty' => false])) {
            foreach ($children as $child) {
                $subject = new CategorySubject();
                $subject->setCategory($child)->setType(CategorySubject::UPDATE);
                $this->update($subject);
            }
        }
    }

    private function updatePosts(?WP_Term $category)
    {
        $postTypes = collect(Utils::getValidPostTypes());
        $postTypes->each(function (string $postType) use ($category) {
            if ($posts = get_posts([
                'post_type' => $postType,
                'category' => $category->term_id,
                'posts_per_page' => -1
            ])
            ) {
                foreach ($posts as $post) {
                    $subject = new PostSubject();
                    $subject->setPost($post);
                    $this->postObserver->update($subject);
                }
            }
        });
    }
}
