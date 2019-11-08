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

    public function query(): Query {
        return $this->database->query();
    }

    public function results(Query $query): ?array {
        try {
            return $this->database->getResults($query);
        } catch (Exception $e) {
            return null;
        }
    }

    public function all(): ?Collection
    {
        $query = $this->database->query()->select('*');
        if ($sitemaps = $this->database->getResults($query)) {
            return $this->mapSitemaps($sitemaps);
        }
        return null;
    }

    /**
     * @param $key
     * @param $value
     * @return Collection|null
     * @throws \Exception
     */
    public function findAllBy($key, $value): ?Collection
    {
        $query = $this->database->query()->select('*')
            ->where([$key, $value]);
        if ($sitemaps = $this->database->getResults($query)) {
            return $this->mapSitemaps($sitemaps);
        }

        return null;
    }

    public function findByPost(\WP_Post $post): ?Sitemap
    {
        $query = $this->database->query()->select('*')
            ->where(['wp_id', $post->ID], Query::FORMAT_INT)
            ->andWhere(['post_type', $post->post_type])
            ->limit(1);
        if ($sitemaps = $this->database->getResults($query)) {
            if (isset($sitemaps[0]) && $sitemap = $sitemaps[0]) {
                return Sitemap::createFromArray($sitemap);
            }
        }
        return null;
    }

    public function insertOrUpdateByPost(?\WP_Post $post): ?Sitemap
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

    public function deleteByPost(?\WP_Post $post): bool
    {
        if ($post) {
            $sitemap = $this->findByPost($post);
            if ($sitemap) {
                try {
                    return $this->database->delete($sitemap->getID());
                } catch(Exception $exception) {
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
