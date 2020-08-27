<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a contact popup block.
 *
 * @Block(
 *   id = "contact_popup_block",
 *   admin_label = @Translation("MARS: Contact Popup Block"),
 *   category = @Translation("Mars Common")
 * )
 */
class ContactPopupBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Media storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $mediaStorage;

  /**
   * The configFactory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

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
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    ConfigFactoryInterface $config_factory
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->mediaStorage = $entity_type_manager->getStorage('media');
    $this->fileStorage = $entity_type_manager->getStorage('file');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build['#social_menu_items'] = $this->socialLinks();
    $build['#theme'] = 'contact_popup_block';

    return $build;
  }

  /**
   * Prepare social links data.
   *
   * @return array
   *   Rendered menu.
   */
  protected function socialLinks() {
    $social_menu_items = [];
    $theme_settings = $this->configFactory->get('emulsifymars.settings')->get();

    foreach ($theme_settings['social'] as $key => $social_settings) {
      $social_menu_items[$key]['title'] = $social_settings['name'];
      $social_menu_items[$key]['url'] = $social_settings['link'];
      if (!empty($social_settings['icon'])) {
        $fid = reset($social_settings['icon']);
        $file = $this->fileStorage->load($fid);
      }
      $social_menu_items[$key]['icon'] = !empty($file) ? $file->createFileUrl() : '';
    }
    return $social_menu_items;
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
      '#maxlength' => 45,
      '#default_value' => $this->configuration['label'] ?? '',
      '#required' => TRUE,
    ];
    $form['background'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Background media'),
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

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $this->configuration['eyebrow'] = $form_state->getValue('eyebrow');
    $this->configuration['label'] = $form_state->getValue('label');
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
