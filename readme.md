# Nette Latte Engine for WordPress

This [mu-plugin](https://codex.wordpress.org/Must_Use_Plugins) gives theme and plugin developers availability to write templates with [Nette Latte v2.5](https://latte.nette.org/en/).

## Requirements

WordPress: 4.7+
PHP: 5.6.0+
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

## Instalation with Composer

1. Go to `wp-content/mu-plugins` folder and require wp-latte with `composer require mejta/wp-latte` command.
1. Don't forget to load composer dependencies in your loader (e.g. `wp-content/mu-plugins/load.php`):

```php
<?php // wp-content/mu-plugins/load.php
require WPMU_PLUGIN_DIR . '/vendor/autoload.php';
```

## Instalation with Git

1. Go to `wp-content/mu-plugins` folder and clone the repository with `git clone git@github.com:mejta/wp-latte.git` command.
1. Go to plugin folder with `cd wp-latte`
1. Install composer dependencies with `composer install`
1. Don't forget to load the plugin in your loader (e.g. `wp-content/mu-plugins/load.php`):

```php
<?php // wp-content/mu-plugins/load.php
require WPMU_PLUGIN_DIR . '/wp-latte/vendor/autoload.php';
require WPMU_PLUGIN_DIR . '/wp-latte/wp-latte.php';
```

## Usage

After plugin activation, you can use Nette Latte templates in your theme. Just use `.latte` file extension instead of `.php`. Template files with `.php`  extension will continue to work, but that templates will have lower priority in template resolution.

There are limitations with `header.php` and `footer.php`. You should use instead `{php wp_head()}` and `{php wp_footer()}` macro inside layout file. If you need to use header.php and footer.php file (e.g. for WooCommerce plugin), leave that files blank.

If you want to have a fallback for a case when the plugin is not activated, add `index.php` inside your theme folder with some meaningful message that says that there is plugin needed.

You can also define your custom filters and macros. Use the following code in your `functions.php` file.

```php
<?php // wp-content/themes/my-theme/functions.php
use NetteLatteEngine\NetteLatteEngine;

// https://latte.nette.org/en/guide#toc-user-defined-macros
NetteLatteEngine::addMacro('test', $start_php_code, $end_php_code);

// https://latte.nette.org/en/guide#toc-custom-filters
NetteLatteEngine::addFilter('test', $callback_function);
```

If you want define custom post template, create a template file in theme root directory and put the comment block at the begining of the file like this:

```latte
{*
Template Name: Custom template file
Template Post Type: post, page
*}
...
<h1>Content</h1>
...
```

## Contribution

Feel free to improve the plugin and open pull request in the [Github repository](https://github.com/mejta/wp-latte).
