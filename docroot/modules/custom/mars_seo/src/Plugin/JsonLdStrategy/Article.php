<?php

namespace Drupal\mars_seo\Plugin\JsonLdStrategy;

use Drupal\mars_seo\JsonLdStrategyPluginBase;

/**
 * Plugin implementation of the Mars JSON LD Strategy for Articles.
 *
 * @JsonLdStrategy(
 *   id = "news_article",
 *   label = @Translation("News Article"),
 *   description = @Translation("Plugin for bundles that support NewsArticle schema."),
 *   bundles = {
 *     "article"
 *   },
 *   context_definitions = {
 *     "node" = @ContextDefinition("entity:node", label = @Translation("Node"), required = TRUE),
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

    $data = [
      '@context' => 'https://schema.org',
      '@type' => 'NewsArticle',
    ];

    $data['headline'] = $node->getTitle();

    $changed_time = new \DateTime();
    $changed_time->setTimestamp($node->getChangedTime());
    $data['dateModified'] = $changed_time->format(\DateTime::ISO8601);

    if ($node->field_article_image->target_id && ($url = $this->getMediaUrl($node->field_article_image->entity))) {
      $data['image'][] = $url;
    }

    return $data;
  }

}
