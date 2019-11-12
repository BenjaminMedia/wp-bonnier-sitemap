<?php

namespace Bonnier\WP\Sitemap\Repositories;

use Bonnier\WP\Sitemap\Database\DB;
use Bonnier\WP\Sitemap\Database\Migrations\Migrate;
use Bonnier\WP\Sitemap\Database\Query;
use Bonnier\WP\Sitemap\Models\Sitemap;
use Exception;
use Illuminate\Support\Collection;

class SitemapRepository
{
    /** @var DB */
    protected $database;
    protected $tableName;

    /**
     * BaseRepository constructor.
     *
     * @param DB $database
     * @throws Exception
     */
    public function __construct(DB $database)
    {
        $this->tableName = Migrate::TABLE;
        $this->database = $database;
        if (!$this->tableName) {
            throw new Exception('Missing required property \'$tableName\'');
        }
        $this->database->setTable($this->tableName);
    }

    public function query(): Query
    {
        return $this->database->query();
    }

    public function results(Query $query): ?array
    {
        try {
            return $this->database->getResults($query);
        } catch (Exception $e) {
            return null;
        }
    }

    public function all(): ?Collection
    {
        try {
            $query = $this->database->query()->select('*');
            if ($sitemaps = $this->database->getResults($query)) {
                return $this->mapSitemaps($sitemaps);
            }
        } catch (Exception $exception) {
            return null;
        }
        return null;
    }

    /**
     * @param $key
     * @param $value
     * @return Collection|null
     */
    public function findAllBy($key, $value): ?Collection
    {
        try {
            $query = $this->database->query()->select('*')
                ->where([$key, $value]);
            if ($sitemaps = $this->database->getResults($query)) {
                return $this->mapSitemaps($sitemaps);
            }
        } catch (Exception $exception) {
            return null;
        }

        return null;
    }

    public function findByPost(\WP_Post $post): ?Sitemap
    {
        try {
            $query = $this->database->query()->select('*')
                ->where(['wp_id', $post->ID], Query::FORMAT_INT)
                ->andWhere(['wp_type', $post->post_type])
                ->limit(1);
            if ($sitemaps = $this->database->getResults($query)) {
                if (isset($sitemaps[0]) && $sitemap = $sitemaps[0]) {
                    return Sitemap::createFromArray($sitemap);
                }
            }
        } catch (Exception $exception) {
            return null;
        }

        return null;
    }

    /**
     * @param \WP_Term $term
     * @return Sitemap|null
     */
    public function findByTerm(\WP_Term $term): ?Sitemap
    {
        try {
            $query = $this->database->query()->select('*')
            ->where(['wp_id', $term->term_id], Query::FORMAT_INT)
            ->andWhere(['wp_type', $term->taxonomy])
            ->limit(1);
            if ($sitemaps = $this->database->getResults($query)) {
                if (isset($sitemaps[0]) && $sitemap = $sitemaps[0]) {
                    return Sitemap::createFromArray($sitemap);
                }
            }
        } catch (Exception $exception) {
        }
        return null;
    }

    public function insertOrUpdatePost(?\WP_Post $post): ?Sitemap
    {
        if ($post) {
            $sitemap = $this->findByPost($post);
            if ($sitemap) {
                $sitemap->setUrl(get_permalink($post));
                try {
                    if ($this->database->update($sitemap->getID(), $sitemap->toArray())) {
                        return $sitemap;
                    }
                } catch (Exception $exception) {
                    return null;
                }
            } else {
                try {
                    $sitemap = Sitemap::createFromPost($post);
                    $sitemapID = $this->database->insert($sitemap->toArray());
                    return $sitemap->setID($sitemapID);
                } catch (Exception $exception) {
                    return null;
                }
            }
        }

        return null;
    }

    public function insertOrUpdateCategory(?\WP_Term $category): ?Sitemap
    {
        if ($category) {
            $sitemap = $this->findByTerm($category);
            if ($sitemap) {
                $sitemap->setUrl(get_category_link($category));
                try {
                    if ($this->database->update($sitemap->getID(), $sitemap->toArray())) {
                        return $sitemap;
                    }
                } catch (Exception $exception) {
                    return null;
                }
            } else {
                try {
                    $sitemap = Sitemap::createFromCategory($category);
                    $sitemapID = $this->database->insert($sitemap->toArray());
                    return $sitemap->setID($sitemapID);
                } catch (Exception $exception) {
                    return null;
                }
            }
        }

        return null;
    }

    public function insertOrUpdateTag(?\WP_Term $tag): ?Sitemap
    {
        if ($tag) {
            $sitemap = $this->findByTerm($tag);
            if ($sitemap) {
                $sitemap->setUrl(get_tag_link($tag));
                try {
                    if ($this->database->update($sitemap->getID(), $sitemap->toArray())) {
                        return $sitemap;
                    }
                } catch (Exception $exception) {
                    return null;
                }
            } else {
                try {
                    $sitemap = Sitemap::createFromTag($tag);
                    $sitemapID = $this->database->insert($sitemap->toArray());
                    return $sitemap->setID($sitemapID);
                } catch (Exception $exception) {
                    return null;
                }
            }
        }

        return null;
    }

    public function deleteByPost(?\WP_Post $post): bool
    {
        if ($post) {
            $sitemap = $this->findByPost($post);
            if ($sitemap) {
                try {
                    return $this->database->delete($sitemap->getID());
                } catch (Exception $exception) {
                    return false;
                }
            }
            return true;
        }

        return false;
    }

    public function deleteByTerm(?\WP_Term $term): bool
    {
        if ($term) {
            $sitemap = $this->findByTerm($term);
            if ($sitemap) {
                try {
                    return $this->database->delete($sitemap->getID());
                } catch (Exception $exception) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    /**
     * @param array $sitemaps
     * @return Collection
     */
    private function mapSitemaps(array $sitemaps): Collection
    {
        return collect($sitemaps)->map(function (array $data) {
            return Sitemap::createFromArray($data);
        });
    }
}
