<?php

namespace Bonnier\WP\Sitemap\Tests\integration\Observers\User;

use Bonnier\WP\Sitemap\Tests\integration\Observers\ObserverTestCase;
use Illuminate\Support\Collection;

class UserObserverTestCase extends ObserverTestCase
{
    protected function getUser(array $args = [], ?Collection $posts = null): \WP_User
    {
        $user = parent::getUser($args);
        if (!$posts) {
            collect(array_fill(0, 5, ''))->map(function () use ($user) {
                return $this->getPost([
                    'post_author' => $user->ID,
                    'lang' => 'en'
                ]);
            });
        }
        return get_user_by('ID', $user->ID);
    }
}
