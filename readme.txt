=== Plugin Name ===
Contributors: mejta
Donate link: https://www.mejta.net/
Tags: nette, latte
Requires at least: 4.7
Tested up to: 5.0
Requires PHP: 5.6.0
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
 
This plugin enables Nette Latte templates in themes.
 
== Description ==
 
This plugin gives theme developers availability to develop themes with [Nette Latte v2.5](https://latte.nette.org/en/). You should use [TGMA](http://tgmpluginactivation.com/) in your theme to require this plugin.

After plugin activation, you can use Nette Latte templates in your theme. Just use `.latte` file extension instead of `.php`. Template files with `.php`  extension will continue to work, but that templates will have lower priority in template resolution.

There are limitations with `header.php` and `footer.php`. You should use instead `{php wp_head()}` and `{php wp_footer()}` macro inside layout file. If you need to use header.php and footer.php file (e.g. for WooCommerce plugin), leave that files blank.

If you want to have a fallback for a case when the plugin is not activated, add `index.php` inside your theme folder with some meaningful message that says that there is plugin needed.

You can also define your custom filters and macros. Use the following code in your `functions.php` file.

```php
use NetteLatteEngine\NetteLatteEngine;

if (class_exists('NetteLatteEngine\NetteLatteEngine')) {
  // https://latte.nette.org/en/guide#toc-user-defined-macros
  NetteLatteEngine::addMacro('test', $start_php_code, $end_php_code);

  // https://latte.nette.org/en/guide#toc-custom-filters
  NetteLatteEngine::addFilter('test', $callback_function);
}
```

== Installation ==
 
This section describes how to install the plugin and get it working.
 
e.g.
 
1. Install plugin from WordPress directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Use the `.latte` files in your templates
 
== Changelog ==
 
= 1.0 =
* Initial release.
