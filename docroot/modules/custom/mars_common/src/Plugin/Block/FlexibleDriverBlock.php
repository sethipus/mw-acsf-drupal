<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\mars_common\ThemeConfiguratorParser;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Flexible driver block.
 *
 * @Block(
 *   id = "flexible_driver",
 *   admin_label = @Translation("Flexible driver"),
 *   category = @Translation("Flexible driver"),
 * )
 */
class FlexibleDriverBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Media storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $mediaStorage;

  /**
   * Theme Configurator service.
   *
   * @var \Drupal\mars_common\ThemeConfiguratorParser
   */
  private $themeConfigurator;

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    $theme_configurator = $container->get('mars_common.theme_configurator_parser');
    $entity_type_manager = $container->get('entity_type.manager');
    $entity_storage = $entity_type_manager->getStorage('media');

    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $entity_storage,
      $theme_configurator
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityStorageInterface $entity_storage,
    ThemeConfiguratorParser $themeConfigurator
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->mediaStorage = $entity_storage;
    $this->themeConfigurator = $themeConfigurator;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#theme' => 'flexible_driver_block',
      '#title' => $this->configuration['label'] ?? '',
      '#description' => $this->configuration['description'] ?? '',
      '#cta_label' => $this->configuration['cta_label'] ?? '',
      '#cta_link' => $this->configuration['cta_link'] ?? '',
      '#asset_1' => $this->getAsset('asset_1'),
      '#asset_2' => $this->getAsset('asset_2'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#maxlength' => 65,
      '#default_value' => $this->configuration['title'] ?? '',
      '#required' => TRUE,
    ];

    $form['description'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Description'),
      '#maxlength' => 65,
      '#default_value' => $this->configuration['description'] ?? '',
      '#required' => FALSE,
    ];

    $form['cta_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('CTA Label'),
      '#maxlength' => 15,
      '#default_value' => $this->configuration['cta_label'] ?? '',
      '#required' => TRUE,
    ];

    $form['cta_link'] = [
      '#type' => 'url',
      '#title' => $this->t('CTA Link'),
      '#default_value' => $this->configuration['cta_link'] ?? '',
      '#required' => TRUE,
    ];

    $form['asset_1'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Asset #1'),
      '#target_type' => 'media',
      '#default_value' => $this->getAsset('asset_1'),
      '#required' => TRUE,
    ];

    $form['asset_2'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Asset #2'),
      '#target_type' => 'media',
      '#default_value' => $this->getAsset('asset_2'),
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $this->configuration['title'] = $form_state->getValue('title');
    $this->configuration['description'] = $form_state->getValue('description');
    $this->configuration['cta_label'] = $form_state->getValue('cta_label');
    $this->configuration['cta_link'] = $form_state->getValue('cta_link');
    $this->configuration['asset_1'] = $form_state->getValue('asset_1');
    $this->configuration['asset_2'] = $form_state->getValue('asset_2');
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'label_display' => FALSE,
      'cta_label' => 'Learn more',
    ];
  }

  /**
   * Returns the entity that's saved to the block.
   *
   * @param string $assetId
   *   The config id where the asset is stored.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The asset entity or null if it's not found.
   */
  private function getAsset(string $assetId): ?EntityInterface {
    $backgroundEntityId = $this->getConfiguration()[$assetId] ?? NULL;
    if (!$backgroundEntityId) {
      return NULL;
    }

    return $this->mediaStorage->load($backgroundEntityId);
  }

}
