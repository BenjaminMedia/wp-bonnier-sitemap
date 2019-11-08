# WP Bonnier Sitemap
A plugin for handling sitemaps in WordPress

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
}, 10, 1);
```

---
