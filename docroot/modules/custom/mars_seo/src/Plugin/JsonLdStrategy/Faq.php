<?php

namespace Drupal\mars_seo\Plugin\JsonLdStrategy;

use Drupal\Core\Render\Element;
use Drupal\mars_seo\JsonLdStrategyPluginBase;
use Drupal\views\ResultRow;
use Spatie\SchemaOrg\Schema;

/**
 * Plugin implementation of the Mars JSON LD Strategy for Recipes.
 *
 * @JsonLdStrategy(
 *   id = "faq",
 *   label = @Translation("FAQ Page"),
 *   description = @Translation("Plugin for bundles that support FAQPage
 *   schema."), bundles = {
 *     "page",
 *     "landing_page"
 *   },
 *   context_definitions = {
 *     "node" = @ContextDefinition("entity:node", label = @Translation("Node"),
 *   required = TRUE),
 *     "build" = @ContextDefinition("any", label = @Translation("Build array"))
 *   }
 * )
 */
class Faq extends JsonLdStrategyPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getStructuredData() {
    $build = $this->getContextValue('build');

    if (!isset($build['_layout_builder'])) {
      return [];
    }

    if (!($component = $this->getLayoutBuilderComponent($build['_layout_builder']))) {
      return [];
    }

    /** @var \Drupal\views\ViewExecutable $view */
    $view = $component['content']['#view'];

    $builder = Schema::fAQPage()
      ->mainEntity(array_map(function (ResultRow $row) {
        return Schema::question()
          ->name($row->_entity->field_qa_item_question->value)
          ->acceptedAnswer(
            Schema::answer()->text($row->_entity->field_qa_item_answer->value)
          );
      }, $view->result));

    return $builder->toArray();
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
  protected function getLayoutBuilderComponent(array $element) {
    foreach (Element::children($element) as $delta) {
      $section = $element[$delta];

      if (!Element::isEmpty($section)) {
        /** @var \Drupal\Core\Layout\LayoutDefinition $layout */
        $layout = $section['#layout'];
        $regions = $layout->getRegionNames();

        foreach ($regions as $region) {
          if (isset($section[$region])) {
            foreach ($section[$region] as $component) {
              if ('views_block' == ($component['#base_plugin_id'] ?? NULL) && 'faq_view' == ($component['content']['#name'] ?? NULL)) {
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
