<?php

namespace Bonnier\WP\Sitemap\Models;

use Bonnier\WP\Sitemap\Helpers\LocaleHelper;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

class Sitemap implements Arrayable
{
    /** @var int */
    private $sitemapID;

    /** @var string */
    private $url;

    /** @var string */
    private $locale;

    /** @var string */
    private $wpType;

    /** @var int */
    private $wpID;

    /** @var \DateTime */
    private $modifiedAt;

    public function __construct()
    {
        $this->sitemapID = 0;
    }

    public static function createFromArray(array $data): Sitemap
    {
        return (new self())->fromArray($data);
    }

    public static function createFromPost(\WP_Post $post): Sitemap
    {
        $sitemap = new Sitemap();
        $sitemap->setUrl(get_permalink($post))
            ->setLocale(LocaleHelper::getPostLocale($post->ID))
            ->setWpType($post->post_type)
            ->setWpID($post->ID)
            ->setModifiedAt(new \DateTime($post->post_modified));
        return $sitemap;
    }

    public static function createFromCategory(\WP_Term $category): Sitemap
    {
        $sitemap = new Sitemap();
        $sitemap->setUrl(get_category_link($category))
            ->setLocale(LocaleHelper::getTermLocale($category->term_id))
            ->setWpType($category->taxonomy)
            ->setWpID($category->term_id)
            ->setModifiedAt(new \DateTime());
        return $sitemap;
    }

    public static function createFromTag(\WP_Term $tag): Sitemap
    {
        $sitemap = new Sitemap();
        $sitemap->setUrl(get_tag_link($tag))
            ->setLocale(LocaleHelper::getTermLocale($tag->term_id))
            ->setWpType($tag->taxonomy)
            ->setWpID($tag->term_id)
            ->setModifiedAt(new \DateTime());
        return $sitemap;
    }

    /**
     * @return int
     */
    public function getID(): int
    {
        return $this->sitemapID;
    }

    /**
     * @param int $sitemapID
     * @return Sitemap
     */
    public function setID(int $sitemapID): Sitemap
    {
        $this->sitemapID = $sitemapID;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return Sitemap
     */
    public function setUrl(string $url): Sitemap
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     * @return Sitemap
     */
    public function setLocale(string $locale): Sitemap
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * @return string
     */
    public function getWpType(): string
    {
        return $this->wpType;
    }

    /**
     * @param string $wpType
     * @return Sitemap
     */
    public function setWpType(string $wpType): Sitemap
    {
        $this->wpType = $wpType;
        return $this;
    }

    /**
     * @return int
     */
    public function getWpID(): int
    {
        return $this->wpID;
    }

    /**
     * @param int $wpID
     * @return Sitemap
     */
    public function setWpID(int $wpID): Sitemap
    {
        $this->wpID = $wpID;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getModifiedAt(): \DateTime
    {
        return $this->modifiedAt;
    }

    /**
     * @param \DateTime $modifiedAt
     * @return Sitemap
     */
    public function setModifiedAt(\DateTime $modifiedAt): Sitemap
    {
        $this->modifiedAt = $modifiedAt;
        return $this;
    }

    public function fromArray(array $data): Sitemap
    {
        $this->sitemapID = intval(Arr::get($data, 'id', 0));
        $this->url = Arr::get($data, 'url', '');
        $this->locale = Arr::get($data, 'locale', '');
        $this->wpID = intval(Arr::get($data, 'wp_id', 0));
        $this->wpType = Arr::get($data, 'wp_type', '');
        try {
            $this->modifiedAt = new \DateTime(Arr::get($data, 'modified_at', 'now'));
        } catch (\Exception $exception) {
            $this->modifiedAt = strtotime('now');
        }

        return $this;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->sitemapID,
            'url' => $this->url,
            'locale' => $this->locale,
            'wp_type' => $this->wpType,
            'wp_id' => $this->wpID,
            'modified_at' => $this->modifiedAt->format('Y-m-d H:i:s')
        ];
    }
}
