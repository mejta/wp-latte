<?php
namespace NetteLatteEngine;

class NetteLatteEngine {
  /**
   * @var self
   */
  private static $instance;

  /**
   * https://developer.wordpress.org/reference/hooks/type_template_hierarchy/
   * @var string[]
   */
  private $types = ['index', '404', 'archive', 'author', 'category', 'tag', 'taxonomy', 'date', 'embed', 'home', 'frontpage', 'page', 'paged', 'search', 'single', 'singular', 'attachment'];
  
  /**
   * @var string
   */
  private $emptyTemplate = __DIR__ . '/../empty-template.php';

  /**
   * @var \Latte\Engine
   */
  private $engine;

  /**
   * @var \Latte\Macros\MacroSet
   */
  private $set;

  /**
   * Constructor
   */
  private function __construct() {
    $this->engine = new \Latte\Engine;
    $this->engine->setTempDirectory($this->createCachePath());
    $this->set = new \Latte\Macros\MacroSet($this->engine->getCompiler());

    foreach ($this->types as $type) {
      add_filter($type . '_template_hierarchy', [$this, 'addLatteTemplate']);
    }

    add_filter('template_include', [$this, 'templateInclude']);
    add_filter('comments_template', [$this, 'commentsTemplate']);
  }

  /**
   * Gets cache path for Latte
   * @return string Cache directory path
   * @throws \Exception
   */
  private function getCachePath() {
    return wp_get_upload_dir()['basedir'] . '/.latte-cache';
  }

  /**
   * Creates cache directory for Latte
   * @return string Cache directory path
   */
  private function createCachePath() {
    $cachePath = $this->getCachePath();

    if (file_exists($cachePath)) {
      return $cachePath;
    }

    if (!wp_mkdir_p($cachePath)) {
      throw new Exception('Couldn\'t create cache directory for latte on ' . $cache_path);
    }

    return $cachePath;
  }

  /**
   * Removes cache directory path
   */
  private function removeCachePath() {
    require_once (ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php');
    require_once (ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php');
    $cachePath = $this->getCachePath();
    $fileSystemDirect = new \WP_Filesystem_Direct(false);
    $fileSystemDirect->rmdir($cachePath, true);
  }

  /**
   * Gets instance of the self
   * @return self
   */
  public static function getInstance() {
      if (self::$instance === null) {
          self::$instance = new self;
      }
      return self::$instance;
  }

  /**
   * Plugin initialization
   */
  public static function initialize() {
    self::getInstance();
  }

  /**
   * Plugin activation
   */
  public static function activate() {
    self::getInstance()->createCachePath();
  }

  /**
   * Plugin deactivation
   */
  public static function deactivate() {
    self::getInstance()->removeCachePath();
  }

  /**
   * Renders $template
   * @param string Template path
   * @param array Params for template
   */
  public static function render($template, array $params = []) {
    self::getInstance()->templateInclude($template, $params);
  }

  /**
   * Adds filter to Latte
   * @param string Filter tag
   * @param callable Filter handler
   */
  public static function addFilter($tag, $callback) {
    self::getInstance()->addFilter($tag, $function);
  }

  /**
   * Adds macro to Latte
   * @param string Macro tag
   * @param string Code at the beginning
   * @param string|null Code at the end
   */
  public static function addMacro($tag, $start, $end = null) {
    self::getInstance()->set->addMacro($tag, $start, $end);
  }

  /**
   * Adds latte templates to array of templates
   * @param string[] Current templates
   * @return string[] Templates with Latte alternative
   */
  public function addLatteTemplate($templates) {
    $withLatte = [];

    foreach ($templates as $template) {
      $templateName = preg_replace('/.php$/', '', $template);

      if (array_search($templateName . '.latte', $templates) === false) {
        $withLatte[] = $templateName . '.latte';
      }

      $withLatte[] = $template;
    }

    return $withLatte;
  }

  /**
   * Renders template
   * @param string Template path
   * @param array|null Additional params to the template
   * @return string PHP Template path
   */
  public function templateInclude($template, array $additionalParams = []) {
    if (preg_match('/\.latte$/m', $template)) {
      // https://developer.wordpress.org/reference/functions/load_template/
      global $posts, $post, $wp_did_header, $wp_query, $wp_rewrite, $wpdb, $wp_version, $wp, $id, $comment, $user_ID;
      $params = compact('posts', 'post', 'wp_did_header', 'wp_query', 'wp_rewrite', 'wpdb', 'wp_version', 'wp', 'id', 'comment', 'user_ID');

      if (is_array($wp_query->query_vars)) {
        $params = array_merge($wp_query->query_vars, $params);
      }

      $this->engine->render($template, array_merge($params, $additionalParams));

      return $this->emptyTemplate;
    }

    return $template;
  }

  /**
   * Renders comment template
   * @param string Template path
   * @return string PHP Template path
   */
  public function commentsTemplate($commentTemplate) {
    if (preg_match('/\.latte$/m', $commentTemplate)) {
      $latteTemplate = $commentTemplate;
    } else {
      $latteTemplate = preg_replace('/.php$/', '.latte', $commentTemplate);
    }

    if (file_exists($latteTemplate)) {
      $commentTemplate = $this->templateInclude($latteTemplate);
    }

    return $commentTemplate;
  }
}
