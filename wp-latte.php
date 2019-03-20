<?php
/*
Plugin Name: Nette Latte Engine
Plugin URI: https://www.mejta.net
Description: Nette Latte template engine support for WordPress.
Author: Daniel Mejta
Version: 1.0.0
Author URI: https://www.mejta.net
*/

if (!function_exists('add_action')) {
  exit;
}

if(!class_exists('NetteLatteEngine\NetteLatteEngine')) {
  require __DIR__ . '/vendor/autoload.php';

  register_activation_hook(__FILE__, ['NetteLatteEngine\NetteLatteEngine', 'activate']);
  register_deactivation_hook(__FILE__, ['NetteLatteEngine\NetteLatteEngine', 'deactivate']);

  NetteLatteEngine\NetteLatteEngine::initialize();
}
