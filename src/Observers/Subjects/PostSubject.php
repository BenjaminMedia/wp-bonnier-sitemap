<?php

namespace Bonnier\WP\Sitemap\Observers\Subjects;

use Bonnier\WP\Sitemap\Helpers\Utils;
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
        if (
            !(wp_is_post_revision($postID) || wp_is_post_autosave($postID)) ||
            !in_array($post->post_type, Utils::getValidPostTypes())
        ) {
            $this->post = $post;
            $this->notify();
        }
    }
}
