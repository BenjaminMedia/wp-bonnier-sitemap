<?php

namespace Bonnier\WP\Sitemap\Observers\Subjects;

use Bonnier\WP\Sitemap\Helpers\LocaleHelper;
use Bonnier\WP\Sitemap\Observers\AbstractSubject;

class CategorySubject extends AbstractSubject
{
    public const UPDATE = 'update';
    public const DELETE = 'delete';
    public const COUNT = 'count';

    /** @var \WP_Term|null */
    private $category;

    /** @var string */
    private $type;

    /** @var string */
    private $locale;

    private $affectedPosts = [];

    private $affectedCategories = [];

    public function __construct()
    {
        parent::__construct();
        add_action('create_category', [$this, 'updateCategory']);
        add_action('edited_category', [$this, 'updateCategory']);
        add_action('pre_delete_term', [$this, 'preDeleteCategory'], 0, 2);
        add_action('delete_category', [$this, 'deletedCategory'], 10, 4);
        add_action('set_object_terms', [$this, 'updatePostTerms'], 10, 6);
    }

    /**
     * @return \WP_Term|null
     */
    public function getCategory(): ?\WP_Term
    {
        return $this->category;
    }

    /**
     * @param \WP_Term $category
     * @return CategorySubject
     */
    public function setCategory(\WP_Term $category): CategorySubject
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return array
     */
    public function getAffectedPosts(): array
    {
        return $this->affectedPosts;
    }

    public function getAffectedCategories(): array
    {
        return $this->affectedCategories;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return CategorySubject
     */
    public function setType(string $type): CategorySubject
    {
        if (in_array($type, [self::UPDATE, self::DELETE])) {
            $this->type = $type;
        }
        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    /**
     * @param int $termID
     */
    public function updateCategory(int $termID)
    {
        if (($category = get_term($termID)) && $category instanceof \WP_Term && $category->taxonomy === 'category') {
            $this->category = $category;
            $this->type = self::UPDATE;
            $this->notify();
        }
    }

    public function preDeleteCategory(int $termID, string $taxonomy)
    {
        if ($taxonomy === 'category') {
            $this->locale = LocaleHelper::getTermLocale($termID);
            $this->affectedCategories = get_categories(['parent' => $termID, 'hide_empty' => false]);
        }
    }

    /**
     * @param int $termID
     * @param string $taxonomy
     * @param \WP_Term $category
     * @param array $objectIds
     */
    public function deletedCategory(int $termID, string $taxonomy, \WP_Term $category, array $objectIds)
    {
        $this->affectedPosts = $objectIds;
        $this->category = $category;
        $this->type = self::DELETE;
        $this->notify();
    }

    public function updatePostTerms(int $objectID, array $terms, array $termTaxonomyIDs, string $taxonomy, bool $append, array $oldTermTaxonomyIDs)
    {
        if ($taxonomy === 'category') {
            foreach (array_merge($terms, $oldTermTaxonomyIDs) as $termID) {
                if (($category = get_category($termID)) && $category instanceof \WP_Term) {
                    $this->category = $category;
                    $this->type = self::COUNT;
                    $this->notify();
                }
            }
        }
    }
}
