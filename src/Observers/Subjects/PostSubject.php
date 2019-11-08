<?php

namespace Bonnier\WP\Sitemap\Observers\Subjects;

use Bonnier\WP\Sitemap\Observers\AbstractSubject;
use Bonnier\WP\Sitemap\WpBonnierSitemap;

class PostSubject extends AbstractSubject
{
    /** @var \WP_Post */
    private $post;

    public function __construct()
    {
        parent::__construct();
        add_action('save_post', [$this, 'updatePost'], 10, 2);
    }

    /**
     * @return \WP_Post|null
     */
    public function getPost(): ?\WP_Post
    {
        return $this->post;
    }

    /**
     * @param \WP_Post $post
     * @return PostSubject
     */
    public function setPost(\WP_Post $post): PostSubject
    {
        $this->post = $post;
        return $this;
    }

    /**
     * @param int $postID
     * @param \WP_Post $post
     */
    public function updatePost(int $postID, \WP_Post $post)
    {
        if (!(wp_is_post_revision($postID) || wp_is_post_autosave($postID)) || !$this->validatePostType($post->post_type)) {
            $this->post = $post;
            $this->notify();
        }
    }

    private function validatePostType(string $postType): bool
    {
        $validPostTypes = array_filter(get_post_types(['public' => true]), function (string $postType) {
            return $postType !== 'attachment';
        });
        $validPostTypes = apply_filters(WpBonnierSitemap::FILTER_ALLOWED_POST_TYPES, $validPostTypes);
        return in_array($postType, $validPostTypes);
    }
}
