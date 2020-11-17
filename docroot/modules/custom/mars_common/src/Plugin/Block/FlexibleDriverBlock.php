<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\mars_common\MediaHelper;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\mars_lighthouse\Traits\EntityBrowserFormTrait;
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

  use EntityBrowserFormTrait;

  /**
   * Lighthouse entity browser id.
   */
  const LIGHTHOUSE_ENTITY_BROWSER_ID = 'lighthouse_browser';

  /**
   * Mars Media Helper service.
   *
   * @var \Drupal\mars_common\MediaHelper
   */
  protected $mediaHelper;

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
    $media_helper = $container->get('mars_common.media_helper');
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $media_helper,
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
    MediaHelper $media_helper,
    ThemeConfiguratorParser $themeConfigurator
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->mediaHelper = $media_helper;
    $this->themeConfigurator = $themeConfigurator;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $mediaId1 = $this->getMediaId('asset_1');
    $mediaId2 = $this->getMediaId('asset_2');
    return [
      '#theme' => 'flexible_driver_block',
      '#title' => $this->configuration['title'] ?? '',
      '#description' => $this->configuration['description'] ?? '',
      '#cta_label' => $this->configuration['cta_label'] ?? '',
      '#cta_link' => $this->configuration['cta_link'] ?? '',
      '#asset_1' => $this->mediaHelper->getMediaParametersById($mediaId1),
      '#asset_2' => $this->mediaHelper->getMediaParametersById($mediaId2),
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

    $form['asset_1'] = $this->getEntityBrowserForm(self::LIGHTHOUSE_ENTITY_BROWSER_ID,
      $this->configuration['asset_1'], 1, 'thumbnail');
    $form['asset_1']['#type'] = 'details';
    $form['asset_1']['#title'] = $this->t('Asset #1');
    $form['asset_1']['#open'] = TRUE;

    $form['asset_2'] = $this->getEntityBrowserForm(self::LIGHTHOUSE_ENTITY_BROWSER_ID,
      $this->configuration['asset_2'], 1, 'thumbnail');
    $form['asset_2']['#type'] = 'details';
    $form['asset_2']['#title'] = $this->t('Asset #2');
    $form['asset_2']['#open'] = TRUE;

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
    $this->configuration['asset_1'] = $this->getEntityBrowserValue($form_state,
      'asset_1');
    $this->configuration['asset_2'] = $this->getEntityBrowserValue($form_state,
      'asset_2');
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
   * @param string $assetKey
   *   The config id where the asset is stored.
   *
   * @return string|null
   *   The asset uri or null if it's not found.
   */
  private function getMediaId(string $assetKey): ?string {
    $entityBrowserSelectValue = $this->getConfiguration()[$assetKey] ?? NULL;
    return $this->mediaHelper->getIdFromEntityBrowserSelectValue($entityBrowserSelectValue);
  }

}
