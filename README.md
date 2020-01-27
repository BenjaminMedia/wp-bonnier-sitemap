# WP Bonnier Sitemap
A plugin for handling sitemaps in WordPress

This plugin will monitor all public WP_Post types and 

## Filters
This plugin makes some filters available to developers of WordPress sites, in order to customize the validation and handling of content to be processed by this plugin.

---

**WpBonnierSitemap::FILTER_ALLOWED_POST_TYPES = 'sitemap_allowed_post_types'**

This filter will allow you to remove or add post types that are allowed by the sitemap plugin,
and therefore which post types will be listened for on changes.

```php
add_filter('sitemap_allowed_post_types', function(array $postTypes) {
    // Don't handle sitemaps for the page post type.
    return array_filter($postTypes, function(string $postType) {
        return $postType !== 'page';
    });
}, 10);
```

---

**WpBonnierSitemap::FILTER_POST_ALLOWED_IN_SITEMAP = 'post_allowed_in_sitemap'**

This filter will allow you to hijack the validation for whether a WP_Post is supposed to be in the sitemap.

Default: true

```php
add_filter('post_allowed_in_sitemap', function (bool $allowed, \WP_Post $post) {
    if ($post->post_type !== 'page') {
        $allowed = false;
    }
    return $allowed;
}, 10, 2);
```

---

**WpBonnierSitemap::FILTER_POST_TAG_MINIMUM_COUNT = 'post_tag_minimum_count'**

This filter will allow you to define the minimum number of posts a tag needs to be attached to, for it to be included in a sitemap.

Default: 5

```php
add_filter('post_tag_minimum_count', function (int $count) {
    // For SEO purposes, our sitemap cannot have less than 10 posts for tag pages.
    return 10;
}, 10);
``` 

---

**WpBonnierSitemap::FILTER_POST_PERMALINK = 'sitemap_post_permalink'**

This filter will allow you to alter the generated permalink for a post being saved.

```php
add_filter('sitemap_post_permalink', function (string $permalink, \WP_Post $post) {
    return $permalink;
}, 10, 2);
``` 

---

**WpBonnierSitemap::FILTER_CATEGORY_PERMALINK = 'sitemap_category_permalink'**

This filter will allow you to alter the generated permalink for a category being saved.

```php
add_filter('sitemap_category_permalink', function (string $permalink, \WP_Term $category) {
    return $permalink;
}, 10, 2);
``` 

---

**WpBonnierSitemap::FILTER_TAG_PERMALINK = 'sitemap_tag_permalink'**

This filter will allow you to alter the generated permalink for a post_tag being saved.

```php
add_filter('sitemap_tag_permalink', function (string $permalink, \WP_Term $tag) {
    return $permalink;
}, 10, 2);
```