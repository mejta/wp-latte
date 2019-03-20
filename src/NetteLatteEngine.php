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
  private function __construct()
  {
    $this->engine = new \Latte\Engine;
    $this->engine->setTempDirectory($this->createCachePath());
    $this->set = new \Latte\Macros\MacroSet($this->engine->getCompiler());

    foreach ($this->types as $type) {
      add_filter($type . '_template_hierarchy', [$this, 'addLatteTemplate']);
    }

    add_filter('template_include', [$this, 'templateInclude']);
    add_filter('comments_template', [$this, 'commentsTemplate']);
    add_filter('theme_page_templates', [$this, 'registerCustomTemplates'], 10, 3);
  }

  /**
   * Gets cache path for Latte
   */
  private function getCachePath(): string
  {
    return wp_get_upload_dir()['basedir'] . '/.latte-cache';
  }

  /**
   * Creates cache directory for Latte
   */
  private function createCachePath(): string
  {
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
  private function removeCachePath(): void
  {
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
  public static function getInstance(): self
  {
      if (self::$instance === null) {
          self::$instance = new self;
      }
      return self::$instance;
  }

  /**
   * Plugin initialization
   */
  public static function initialize(): void
  {
    self::getInstance();
  }

  /**
   * Plugin activation
   */
  public static function activate(): void
  {
    self::getInstance()->createCachePath();
  }

  /**
   * Plugin deactivation
   */
  public static function deactivate(): void
  {
    self::getInstance()->removeCachePath();
  }

  /**
   * Renders $template
   */
  public static function render(string $template, array $params = []): void
  {
    self::getInstance()->templateInclude($template, $params);
  }

  /**
   * Adds filter to Latte
   */
  public static function addFilter(string $tag, callable $callback): void
  {
    self::getInstance()->addFilter($tag, $function);
  }

  /**
   * Adds macro to Latte
   */
  public static function addMacro(string $tag, string $start, string $end = null): void
  {
    self::getInstance()->set->addMacro($tag, $start, $end);
  }

  /**
   * Adds latte templates to array of templates
   */
  public function addLatteTemplate(array $templates): array
  {
    $withLatte = [];

    foreach ($templates as $template) {
      $templateName = preg_replace('/\.\w+$/', '', $template);

      if (array_search($templateName . '.latte', $templates) === false) {
        $withLatte[] = $templateName . '.latte';
      }

      $withLatte[] = $template;
    }

    return $withLatte;
  }

  /**
   * Return custom templates from theme directory
   * https://developer.wordpress.org/reference/classes/wp_theme/get_post_templates/
   */
  public function registerCustomTemplates(array $page_templates, \WP_Theme $theme, \WP_Post $post): array
  {
    $files = $theme->get_files('latte', 1);
    $postType = get_post_type($post);

    foreach ($files as $file) {
      $headers = get_file_data($file, [
        'templateName' => 'Template Name',
        'postType' => 'Template Post Type',
      ]);

      if (!$headers['templateName']) {
        continue;
      }

      if (!$headers->postType) {
        $headers['postType'] = 'page';
      }

      $templatePostTypes = explode(',', $headers['postType']);

      foreach ($templatePostTypes as $templatePostType) {
        if (trim($templatePostType) === $postType) {
          $templateName = preg_replace('/^' . preg_quote(get_template_directory() . '/', '/') . '/', '', $file);
          $page_templates[$templateName] = $headers['templateName'];
        }
      }
    }

    return $page_templates;
  }

  /**
   * Renders template
   */
  public function templateInclude(string $template, array $additionalParams = []): string
  {
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
   */
  public function commentsTemplate(string $commentTemplate): string
  {
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
