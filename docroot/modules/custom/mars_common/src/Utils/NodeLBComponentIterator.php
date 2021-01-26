<?php

namespace Drupal\mars_common\Utils;

use Drupal\node\NodeInterface;

/**
 * Iterator class for a node's layout builder components.
 */
class NodeLBComponentIterator implements \IteratorAggregate {

  /**
   * The node.
   *
   * @var \Drupal\node\NodeInterface
   */
  private $node;

  /**
   * NodeLBComponentIterator constructor.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node.
   */
  public function __construct(NodeInterface $node) {
    $this->node = $node;
  }

  /**
   * {@inheritdoc}
   */
  public function getIterator() {
    if (!$this->node->hasField('layout_builder__layout')) {
      yield from [];
    }
    else {
      /** @var \Drupal\layout_builder\Field\LayoutSectionItemList $layoutBuilderField */
      $layoutBuilderField = $this->node->get('layout_builder__layout');
      /** @var \Drupal\layout_builder\Section[] $sections */
      $sections = $layoutBuilderField->getSections();

      foreach ($sections as $section) {
        foreach ($section->getComponents() as $component) {
          yield $component;
        }
      }
    }
  }

}
