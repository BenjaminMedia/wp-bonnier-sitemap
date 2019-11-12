<?php

namespace Bonnier\WP\Sitemap\Observers\Dependents;

use Bonnier\WP\Sitemap\Helpers\Utils;
use Bonnier\WP\Sitemap\Observers\Subjects\CategorySubject;
use Bonnier\WP\Sitemap\Repositories\SitemapRepository;
use Bonnier\WP\Sitemap\Observers\Interfaces\ObserverInterface;
use Bonnier\WP\Sitemap\Observers\Interfaces\SubjectInterface;

class CategorySlugChangeObserver implements ObserverInterface
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
        if ($subject->getType() !== CategorySubject::UPDATE) {
            return;
        }
        $category = $subject->getCategory();
        if (!$category) {
            return;
        }
        $this->sitemapRepository->insertOrUpdateCategory($category);

        if ($children = get_categories(['parent' => $category->term_id, 'hide_empty' => false])) {
            foreach ($children as $child) {
                do_action('edited_category', $child->term_id, $child->term_taxonomy_id);
            }
        }

        $postTypes = collect(Utils::getValidPostTypes());
        $postTypes->each(function (string $postType) use ($category) {
            if (
                $posts = get_posts([
                'post_type' => $postType,
                'category' => $category->term_id,
                'posts_per_page' => -1
                ])
            ) {
                foreach ($posts as $post) {
                    do_action('save_post', $post->ID, $post, true);
                }
            }
        });
    }
}
