<?php

namespace Bonnier\WP\Sitemap\Observers\Interfaces;

interface ObserverInterface
{
    public function update(SubjectInterface $subject);
}
