<?php

namespace Bonnier\WP\Sitemap\Observers\Subjects;

use Bonnier\WP\Sitemap\Observers\AbstractSubject;

class UserSubject extends AbstractSubject
{
    public const UPDATE = 'update';
    public const DELETE = 'delete';

    /** @var \WP_User */
    private $user;

    private $type;

    public function __construct()
    {
        parent::__construct();
        add_action('profile_update', [$this, 'updateUser']);
        add_action('delete_user', [$this, 'deleteUser']);
    }

    public function getType()
    {
        return $this->type;
    }
    
    public function getUser()
    {
        return $this->user;
    }

    public function updateUser(int $userID)
    {
        if (($user = get_user_by('ID', $userID)) && $user instanceof \WP_User) {
            $this->user = $user;
            $this->type = self::UPDATE;
            $this->notify();
        }
    }

    public function deleteUser(int $userID)
    {
        if (($user = get_user_by('ID', $userID)) && $user instanceof \WP_User) {
            $this->user = $user;
            $this->type = self::DELETE;
            $this->notify();
        }
    }
}