<?php

namespace Drupal\linkfactory_common\Plugin\Block;

use Drupal\Core\Block\Annotation\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'ArticleNodesBlock' block.
 *
 * @Block(
 *  id = "article_nodes_block",
 *  admin_label = @Translation("Article nodes block"),
 * )
 */
class ArticleNodesBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->entityTypeManager = $container->get('entity_type.manager');

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $articles = $this->getCustomArticles();
    $build = $this->entityTypeManager
      ->getViewBuilder('node')
      ->viewMultiple($articles, 'teaser');

    // First way to set the cache tags.
    // $build['#cache']['tags'] = $this->CustomArticlesCacheTags();

    return $build;
  }

  public function getCustomArticles(): array {
    return $this->entityTypeManager
      ->getStorage('node')
      ->loadByProperties([
        'type' => 'custom_article',
        'status' => 1,
      ]);
  }

  public function CustomArticlesCacheTags(): array {
    $articles = $this->getCustomArticles();
    $tags = [];
    foreach ($articles as $article) {
      $tags[] = "node:" . $article->id();
    }

    // Array for better demonstration in the Network tab.
//    $tags = [
//      "node:555555",
//      "node:666666",
//      "node:777777",
//    ];

    return $tags;
  }

  /**
   * The another way to set the cache tags.
   *
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return Cache::mergeTags(parent::getCacheTags(), $this->CustomArticlesCacheTags());
  }

}
