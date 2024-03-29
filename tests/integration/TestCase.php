<?php

namespace Bonnier\WP\Sitemap\Tests\integration;

use Bonnier\WP\Sitemap\Helpers\LocaleHelper;
use Bonnier\WP\Sitemap\Models\Sitemap;
use Codeception\TestCase\WPTestCase;

class TestCase extends WPTestCase
{
    public static function _setUpBeforeClass()
    {
        /** @var \WP_Rewrite $wp_rewrite */
        global $wp_rewrite;
        $wp_rewrite->set_permalink_structure('/%category%/%postname%/');
        $wp_rewrite->add_permastruct('category', '/%category%');
        $wp_rewrite->add_permastruct('post_tag', '/%post_tag%');
        $wp_rewrite->flush_rules();
        parent::_setUpBeforeClass();
    }

    protected function updatePost(int $postID, array $args)
    {
        $_POST['post_ID'] = $postID;
        $_POST = array_merge($_POST, $args);
        edit_post();
    }

    protected function getPost(array $args = []): \WP_Post
    {
        return $this->factory()->post->create_and_get($args);
    }

    protected function getCategory(array $args = [], bool $withPost = true): \WP_Term
    {
        $category = $this->factory()->category->create_and_get($args);
        if ($withPost) {
            $this->getPost(['post_category' => [$category->term_id]]);
        }
        return $category;
    }

    protected function getTag(array $args = []): \WP_Term
    {
        return $this->factory()->tag->create_and_get($args);
    }

    protected function getUser(array $args = []): \WP_User
    {
        return $this->factory()->user->create_and_get($args);
    }

    protected function assertSitemapEntryMatchesPost(Sitemap $sitemap, \WP_Post $post)
    {
        $this->assertEquals($post->ID, $sitemap->getWpID());
        $this->assertEquals(get_permalink($post), $sitemap->getUrl());
        $this->assertEquals($post->post_type, $sitemap->getWpType());
        $this->assertEquals(LocaleHelper::getPostLocale($post->ID), $sitemap->getLocale());
    }

    protected function assertSitemapEntryMatchesCategory(Sitemap $sitemap, \WP_Term $category)
    {
        $this->assertEquals($category->term_id, $sitemap->getWpID());
        $this->assertEquals(get_category_link($category), $sitemap->getUrl());
        $this->assertEquals($category->taxonomy, $sitemap->getWpType());
        $this->assertEquals(LocaleHelper::getTermLocale($category->term_id), $sitemap->getLocale());
    }

    protected function assertSitemapEntryMatchesTag(Sitemap $sitemap, \WP_Term $tag)
    {
        $this->assertEquals($tag->term_id, $sitemap->getWpID());
        $this->assertEquals(get_tag_link($tag), $sitemap->getUrl());
        $this->assertEquals($tag->taxonomy, $sitemap->getWpType());
        $this->assertEquals(LocaleHelper::getTermLocale($tag->term_id), $sitemap->getLocale());
    }

    protected function assertSitemapEntryMatchesUser(Sitemap $sitemap, \WP_User $user)
    {
        $this->assertEquals($user->ID, $sitemap->getWpID());
        $this->assertEquals(get_author_posts_url($user->ID), $sitemap->getUrl());
        $this->assertEquals('user', $sitemap->getWpType());
        $this->assertEquals(LocaleHelper::getLanguages()[0], $sitemap->getLocale());
    }
}
