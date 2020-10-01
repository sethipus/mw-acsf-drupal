<?php

namespace Drupal\mars_seo\Plugin\JsonLdStrategy;

use Drupal\mars_seo\JsonLdStrategyPluginBase;
use Spatie\SchemaOrg\NewsArticle;
use Spatie\SchemaOrg\Schema;

/**
 * Plugin implementation of the Mars JSON LD Strategy for Articles.
 *
 * @JsonLdStrategy(
 *   id = "news_article",
 *   label = @Translation("News Article"),
 *   description = @Translation("Plugin for bundles that support NewsArticle
 *   schema."), bundles = {
 *     "article"
 *   },
 *   context_definitions = {
 *     "node" = @ContextDefinition("entity:node", label = @Translation("Node"),
 *   required = TRUE),
 *     "build" = @ContextDefinition("any", label = @Translation("Build array"))
 *   }
 * )
 */
class Article extends JsonLdStrategyPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getStructuredData() {
    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->getContextValue('node');

    $changed_time = new \DateTime();
    $changed_time->setTimestamp($node->getChangedTime());

    $builder = Schema::newsArticle()
      ->headline($node->getTitle())
      ->dateModified($changed_time)
      ->if($node->field_article_image->target_id, function (NewsArticle $article) use ($node) {
        if ($url = $this->getMediaUrl($node->field_article_image->entity)) {
          $article->image([$url]);
        }
      });

    return $builder->toArray();
  }

}
