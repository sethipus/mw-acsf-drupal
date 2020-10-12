<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a parent page header block.
 *
 * @Block(
 *   id = "parent_page_header",
 *   admin_label = @Translation("Parent Page Header"),
 *   category = @Translation("Custom")
 * )
 */
class ParentPageHeaderBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Media storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $mediaStorage;

  /**
   * File storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $fileStorage;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $entity_type_manager = $container->get('entity_type.manager');
    $entity_storage = $entity_type_manager->getStorage('media');
    $fileStorage = $entity_type_manager->getStorage('file');

    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $entity_storage,
      $fileStorage
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
    EntityStorageInterface $fileStorage
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->mediaStorage = $entity_storage;
    $this->fileStorage = $fileStorage;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $conf = $this->getConfiguration();

    $build['#eyebrow'] = $conf['eyebrow'] ?? '';
    $build['#label'] = $conf['title'] ?? '';
    $build['#background'] = $this->getBackgroundEntity();
    $build['#description'] = $conf['description'] ?? '';

    $build['#theme'] = 'parent_page_header_block';

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'label_display' => FALSE,
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
    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#maxlength' => 45,
      '#default_value' => $this->configuration['title'] ?? '',
      '#required' => TRUE,
    ];
    $form['background'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Background media'),
      '#target_type' => 'media',
      '#default_value' => $this->getBackgroundEntity(),
    ];
    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#maxlength' => 65,
      '#default_value' => $this->configuration['description'] ?? '',
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $this->configuration['eyebrow'] = $form_state->getValue('eyebrow');
    $this->configuration['title'] = $form_state->getValue('title');
    $this->configuration['background'] = $form_state->getValue('background');
    $this->configuration['description'] = $form_state->getValue('description');
  }

  /**
   * Returns the entity that's saved to the block.
   */
  private function getBackgroundEntity(): ?EntityInterface {
    $backgroundEntityId = $this->getConfiguration()['background'] ?? NULL;
    if (!$backgroundEntityId) {
      return NULL;
    }

    return $this->mediaStorage->load($backgroundEntityId);
  }

}
