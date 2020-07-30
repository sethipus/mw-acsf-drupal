<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\media\Entity\Media;

/**
 * Provides a content feature module block.
 *
 * @Block(
 *   id = "mars_common_content_feature_module",
 *   admin_label = @Translation("Content Feature Module"),
 *   category = @Translation("Custom")
 * )
 */
class ContentFeatureModuleBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build['content'] = [
      '#markup' => $this->t(''),
    ];
    return $build;
  }

  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }

  public function defaultConfiguration(): array {
    return [
      'explore_cta' => $this->t('Explore'),
    ];
  }

  public function blockForm($form, FormStateInterface $form_state): array {
    $form['eyebrow'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Eyebrow'),
      '#maxlength' => 15,
      '#description' => $this->t(''),
      '#default_value' => '',
      '#required' => TRUE,
    ];
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#maxlength' => 35,
      '#description' => $this->t(''),
      '#default_value' => '',
      '#required' => TRUE,
    ];
    $form['background'] = [
      '#type' => 'entity_autocomplete',
      '#title' => 'Background',
      '#target_type' => 'media',
      //      '#default_value' => $media,
      '#required' => TRUE,
    ];
    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#maxlength' => 65,
      '#description' => $this->t(''),
      '#default_value' => '',
      '#required' => TRUE,
    ];
    $form['explore_cta'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Explore CTA'),
      '#maxlength' => 15,
      '#default_value' => $this->configuration['explore_cta'],
      '#required' => FALSE,
    ];

    return $form;
  }

}
