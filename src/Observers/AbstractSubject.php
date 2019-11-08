<?php

namespace Bonnier\WP\Sitemap\Observers;

use Bonnier\WP\Sitemap\Observers\Interfaces\ObserverInterface;
use Bonnier\WP\Sitemap\Observers\Interfaces\SubjectInterface;
use Illuminate\Support\Collection;

abstract class AbstractSubject implements SubjectInterface
{
    protected $observers;

    public function __construct()
    {
        $this->observers = new Collection();
    }

    public function attach(ObserverInterface $observer)
    {
        $this->observers->push($observer);
    }

    public function detach(ObserverInterface $observer)
    {
        $this->observers->each(function (ObserverInterface $attachedObserver, int $key) use ($observer) {
            if ($attachedObserver === $observer) {
                $this->observers->forget($key);
            }
        });
    }

    public function notify()
    {
        $this->observers->each(function (ObserverInterface $observer) {
            $observer->update($this);
        });
    }
}
