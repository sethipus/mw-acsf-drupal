<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a product feature block.
 *
 * @Block(
 *   id = "product_feature",
 *   admin_label = @Translation("MARS: Product Feature Block"),
 *   category = @Translation("Mars Common")
 * )
 */
class ProductFeatureBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Media storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $mediaStorage;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $entity_type_manager = $container->get('entity_type.manager');
    $entity_storage = $entity_type_manager->getStorage('media');

    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $entity_storage
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityStorageInterface $entity_storage
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->mediaStorage = $entity_storage;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $conf = $this->getConfiguration();

    $build['#eyebrow'] = $conf['eyebrow'] ?? '';
    $build['#label'] = $conf['label'] ?? '';
    $build['#background_color'] = $conf['background_color'] ?? '';
    $build['#image'] = $this->getImageEntity();
    $build['#explore_cta'] = $conf['explore_cta'] ?? '';
    $build['#explore_cta_link'] = $conf['explore_cta_link'] ?? '';

    $build['#theme'] = 'product_feature_block';

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    $conf = $this->getConfiguration();

    return [
      'label_display' => FALSE,
      'explore_cta' => $conf['explore_cta'] ?? $this->t('Explore'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

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
      '#maxlength' => 50,
      '#default_value' => $this->configuration['label'] ?? '',
      '#required' => TRUE,
    ];
    $form['background_color'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Background Color Override'),
      '#maxlength' => 7,
      '#default_value' => $this->configuration['background_color'] ?? '',
      '#required' => FALSE,
    ];
    $form['image'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Image'),
      '#target_type' => 'media',
      '#default_value' => $this->getImageEntity(),
      '#required' => TRUE,
    ];
    $form['explore_group'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Explore CTA'),
      'explore_cta' => [
        '#type' => 'textfield',
        '#title' => $this->t('Button Label'),
        '#maxlength' => 15,
        '#default_value' => $this->configuration['explore_cta'],
        '#required' => FALSE,
      ],
      'explore_cta_link' => [
        '#type' => 'textfield',
        '#title' => $this->t('URL'),
        '#default_value' => $this->configuration['explore_cta_link'] ?? '',
        '#required' => FALSE,
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $this->configuration['eyebrow'] = $form_state->getValue('eyebrow');
    $this->configuration['label'] = $form_state->getValue('label');
    $this->configuration['background_color'] = $form_state->getValue('background_color');
    $this->configuration['image'] = $form_state->getValue('image');
    $this->configuration['explore_cta'] = $form_state->getValue('explore_group')['explore_cta'];
    $this->configuration['explore_cta_link'] = $form_state->getValue('explore_group')['explore_cta_link'];
  }

  /**
   * Returns the entity that's saved to the block.
   */
  private function getImageEntity(): ?EntityInterface {
    $imageEntityId = $this->getConfiguration()['image'] ?? NULL;
    if (!$imageEntityId) {
      return NULL;
    }

    return $this->mediaStorage->load($imageEntityId);
  }

}
