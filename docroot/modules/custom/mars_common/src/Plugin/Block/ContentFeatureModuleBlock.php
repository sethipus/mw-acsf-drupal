<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityInterface;
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
      'explore_cta' => $this->configuration['explore_cta'] ?? $this->t('Explore'),
    ];
  }

  public function blockForm($form, FormStateInterface $form_state): array {
    $form['eyebrow'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Eyebrow'),
      '#maxlength' => 15,
      '#default_value' => $this->configuration['eyebrow'] ?? '',
      '#required' => TRUE,
    ];
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#maxlength' => 35,
      '#default_value' => $this->configuration['label'] ?? '',
      '#required' => TRUE,
    ];
    $form['background'] = [
      '#type' => 'entity_autocomplete',
      '#title' => 'Background',
      '#target_type' => 'media',
      '#default_value' => $this->getBackgroundEntity(),
      '#required' => TRUE,
    ];
    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#maxlength' => 65,
      '#default_value' => $this->configuration['description'] ?? '',
      '#required' => TRUE,
    ];
    $form['explore_group'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Explore CTA'),
      'explore_cta' => [
        '#type' => 'textfield',
        '#title' => $this->t('Button Label'),
        '#maxlength' => 15,
        '#default_value' => $this->configuration['explore_group']['explore_cta'],
        '#required' => FALSE,
      ],
      'explore_cta_link' => [
        '#type' => 'textfield',
        '#title' => $this->t('URL'),
        '#default_value' => $this->configuration['explore_group']['explore_cta_link'] ?? '',
        '#required' => FALSE,
      ],
    ];

    return $form;
  }

  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration = $form_state->getValues();
  }

  private function getBackgroundEntity(): ?EntityInterface {
    $backgroundEntityId = $this->configuration['background'] ?? NULL;
    if (!$backgroundEntityId) {
      return NULL;
    }

    return Media::load($backgroundEntityId);
  }

}
