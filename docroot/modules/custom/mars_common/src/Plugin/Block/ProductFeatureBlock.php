<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\mars_common\MediaHelper;
use Drupal\mars_lighthouse\Traits\EntityBrowserFormTrait;
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

  use EntityBrowserFormTrait;

  /**
   * Lighthouse entity browser image id.
   */
  const LIGHTHOUSE_ENTITY_BROWSER_IMAGE_ID = 'lighthouse_browser';

  /**
   * Mars Media Helper service.
   *
   * @var \Drupal\mars_common\MediaHelper
   */
  protected $mediaHelper;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('mars_common.media_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    MediaHelper $media_helper
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->mediaHelper = $media_helper;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $conf = $this->getConfiguration();

    $build['#eyebrow'] = $conf['eyebrow'] ?? '';
    $build['#title'] = $conf['title'] ?? '';
    $build['#background_color'] = $conf['background_color'] ?? '';
    if (!empty($conf['image'])) {
      $media_id = $this->mediaHelper->getIdFromEntityBrowserSelectValue($conf['image']);
      $media_params = $this->mediaHelper->getMediaParametersById($media_id);
      if (!($media_params['error'] ?? FALSE) && ($media_params['src'] ?? FALSE)) {
        $build['#image_src'] = $media_params['src'];
        $build['#image_alt'] = $media_params['alt'];
      }
    }
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
    $config = $this->getConfiguration();

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
      '#maxlength' => 55,
      '#default_value' => $this->configuration['title'] ?? '',
      '#required' => TRUE,
    ];
    $form['background_color'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Background Color Override'),
      '#maxlength' => 7,
      '#default_value' => $this->configuration['background_color'] ?? '',
      '#required' => FALSE,
    ];

    $image_default = isset($config['image']) ? $config['image'] : NULL;
    // Entity Browser element for background image.
    $form['image'] = $this->getEntityBrowserForm(self::LIGHTHOUSE_ENTITY_BROWSER_IMAGE_ID, $image_default, 1, 'thumbnail');
    // Convert the wrapping container to a details element.
    $form['image']['#type'] = 'details';
    $form['image']['#title'] = $this->t('Image');
    $form['image']['#open'] = TRUE;
    $form['image']['#required'] = TRUE;

    $form['explore_group'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Explore CTA'),
      'explore_cta' => [
        '#type' => 'textfield',
        '#title' => $this->t('Button Label'),
        '#maxlength' => 15,
        '#default_value' => $this->configuration['explore_cta'],
        '#required' => TRUE,
      ],
      'explore_cta_link' => [
        '#type' => 'textfield',
        '#title' => $this->t('URL'),
        '#default_value' => $this->configuration['explore_cta_link'] ?? '',
        '#required' => TRUE,
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $triggered = $form_state->getTriggeringElement();
    if ($triggered['#name'] === 'op' &&empty($values['image']['selected'])) {
      $form_state->setError(
        $form['image'],
        $this->t('@name field is required.', ['@name' => $this->t('Image')])
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $this->configuration['eyebrow'] = $form_state->getValue('eyebrow');
    $this->configuration['title'] = $form_state->getValue('title');
    $this->configuration['background_color'] = $form_state->getValue('background_color');
    $this->configuration['image'] = $this->getEntityBrowserValue($form_state, 'image');
    $this->configuration['explore_cta'] = $form_state->getValue('explore_group')['explore_cta'];
    $this->configuration['explore_cta_link'] = $form_state->getValue('explore_group')['explore_cta_link'];
  }

}
