![geniem-github-banner](https://cloud.githubusercontent.com/assets/5691777/14319886/9ae46166-fc1b-11e5-9630-d60aa3dc4f9e.png)
# WP Plugin: No-Admin-Ajax
[![Latest Stable Version](https://poser.pugx.org/devgeniem/wp-no-admin-ajax/v/stable)](https://packagist.org/packages/devgeniem/wp-no-admin-ajax) [![Total Downloads](https://poser.pugx.org/devgeniem/wp-no-admin-ajax/downloads)](https://packagist.org/packages/devgeniem/wp-no-admin-ajax) [![Latest Unstable Version](https://poser.pugx.org/devgeniem/wp-no-admin-ajax/v/unstable)](https://packagist.org/packages/devgeniem/wp-no-admin-ajax) [![License](https://poser.pugx.org/devgeniem/wp-no-admin-ajax/license)](https://packagist.org/packages/devgeniem/wp-no-admin-ajax)

A WordPress plugin that changes the WP AJAX routine and rewrites the ajax requests to custom url rather than `/wp-admin/admin-ajax.php` back-end.

## Install

Recommended installation to WP project is through composer:
```
$ composer require devgeniem/wp-no-admin-ajax
```

## Use cases
- Rewrite all admin-ajax.php queries into custom url so you can allow `/wp-admin/` to only certain IP-addresses.
- You can use this to confuse bots which might try to use vulnerabilities in admin-ajax.php.

## Configuration
### Variables
This plugin url is by default `/no-admin-ajax/`. You can use filters to change it or you can set the default value by yourself by using:

```php
// This turns the no admin ajax url to -> /ajax/
define('WP_NO_ADMIN_AJAX_URL','ajax');
```

**Notice:** Value set here can be filtered too, this just sets the starting point for the custom url.

**Notice 2:** After plugin installation and other changes be sure to refresh your permalinks by just going to Settings > Permalinks > and saving it without any modification.

### Hooks & Filters
You can customize the url by using filter `no-admin-ajax/keyword`.
```php
<?php

// This changes /no-admin-ajax/ -> /ajax/
add_filter( 'no-admin-ajax/keyword', 'my_custom_no_admin_ajax_url' );
function my_custom_no_admin_ajax_url( $ajax_url ) {
    return "ajax";
}
```

You can run commands before ajax calls by using `no-admin-ajax/before` or `no-admin-ajax/before/{action}`
```php
<?php
// Writes log entries after hearthbeat action for debugging
do_action( 'no-admin-ajax/before/heartbeat' , 'my_custom_no_admin_ajax_debug' );
function my_custom_no_admin_ajax_debug() {
    error_log( 'DEBUG | heartbeat action was run by: '.$_SERVER[“REMOTE_ADDR”] );
}
```
