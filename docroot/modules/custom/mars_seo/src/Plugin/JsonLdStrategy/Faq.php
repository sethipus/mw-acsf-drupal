<?php

namespace Drupal\mars_seo\Plugin\JsonLdStrategy;

use Drupal\Core\Render\Element;
use Drupal\mars_seo\JsonLdStrategyPluginBase;
use Spatie\SchemaOrg\Schema;

/**
 * Plugin implementation of the Mars JSON LD Strategy for Recipes.
 *
 * @JsonLdStrategy(
 *   id = "faq",
 *   label = @Translation("FAQ Page"),
 *   description = @Translation("Plugin for bundles that support FAQPage schema."),
 *   context_definitions = {
 *     "node" = @ContextDefinition("entity:node", label = @Translation("Node"), required = TRUE),
 *     "build" = @ContextDefinition("any", label = @Translation("Build array"))
 *   }
 * )
 */
class Faq extends JsonLdStrategyPluginBase {

  /**
   * FAQ view render element.
   *
   * @var array
   */
  protected $component;

  /**
   * {@inheritdoc}
   */
  protected $supportedBundles = [];

  /**
   * {@inheritdoc}
   */
  public function isApplicable() {
    if (!parent::isApplicable()) {
      return FALSE;
    }

    $build = $this->getContextValue('build');

    return isset($build['_layout_builder']) && !empty($this->component = $this->getFaqComponent($build['_layout_builder']));
  }

  /**
   * {@inheritdoc}
   */
  public function getStructuredData() {
    $build = $this->getContextValue('build');

    if (!$this->component && !($this->component = $this->getFaqComponent($build['_layout_builder']))) {
      return NULL;
    }

    if (empty($this->component['content']['#qa_items'])) {
      return NULL;
    }

    return Schema::fAQPage()
      ->mainEntity(array_map(function ($value) {
        return Schema::question()
          ->name($value['content']['question'])
          ->acceptedAnswer(
            Schema::answer()->text($value['content']['answer'])
          );
      }, array_values($this->component['content']['#qa_items'])));
  }

  /**
   * Helper method that gets FAQ view component from layout builder config.
   *
   * @param array $element
   *   Node view render array.
   *
   * @return array
   *   FAQ view component render array.
   */
  protected function getFaqComponent(array $element) {
    foreach (Element::children($element) as $delta) {
      $section = $element[$delta];

      if (!Element::isEmpty($section)) {
        /** @var \Drupal\Core\Layout\LayoutDefinition $layout */
        $layout = $section['#layout'];
        $regions = $layout->getRegionNames();

        foreach ($regions as $region) {
          if (isset($section[$region])) {
            foreach ($section[$region] as $component) {
              if (isset($component['#plugin_id']) && $component['#plugin_id'] == 'search_faq_block') {
                return $component;
              }
            }
          }
        }
      }
    }

    return NULL;
  }

}
