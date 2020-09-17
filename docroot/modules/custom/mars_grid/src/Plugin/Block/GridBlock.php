<?php

namespace Drupal\mars_grid\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class GridBlock.
 *
 * @Block(
 *   id = "grid_block",
 *   admin_label = @Translation("Grid block"),
 *   category = @Translation("Grid")
 * )
 *
 * @package Drupal\mars_recipes\Plugin\Block
 */
class GridBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    // TODO: Implement build() method.
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    // TODO: Add building of \Drupal\mars_grid\Form\GridSettingsForm.
    return parent::buildConfigurationForm($form, $form_state);
  }

}
