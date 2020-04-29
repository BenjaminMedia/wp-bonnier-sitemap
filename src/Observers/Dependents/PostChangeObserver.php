<?php

namespace Bonnier\WP\Sitemap\Observers\Dependents;

use Bonnier\WP\Sitemap\Observers\Subjects\PostSubject;
use Bonnier\WP\Sitemap\Observers\Subjects\TagSubject;
use Bonnier\WP\Sitemap\Repositories\SitemapRepository;
use Bonnier\WP\Sitemap\Observers\Interfaces\ObserverInterface;
use Bonnier\WP\Sitemap\Observers\Interfaces\SubjectInterface;
use Bonnier\WP\Sitemap\WpBonnierSitemap;

class PostChangeObserver implements ObserverInterface
{
    private $sitemapRepository;
    private $tagObserver;

    public function __construct(SitemapRepository $sitemapRepository)
    {
        $this->sitemapRepository = $sitemapRepository;
        $this->tagObserver = new TagSlugChangeObserver($sitemapRepository);
    }

    /**
     * @param SubjectInterface|PostSubject $subject
     * @throws \Exception
     */
    public function update(SubjectInterface $subject)
    {
        if ($post = $subject->getPost()) {
            if (
                $post->post_status === 'publish' &&
                apply_filters(WpBonnierSitemap::FILTER_POST_ALLOWED_IN_SITEMAP, true, $post)
            ) {
                $this->sitemapRepository->insertOrUpdatePost($post);
            } else {
                $this->sitemapRepository->deleteByPost($post);
            }
            if (!empty($tags = wp_get_post_terms($post->ID, 'post_tag', ['hide_empty' => false]))) {
                foreach ($tags as $tag) {
                    $subject = new TagSubject();
                    $subject->setTag($tag)->setType(TagSubject::UPDATE);
                    $this->tagObserver->update($subject);
                }
            }
        }
    }
}
