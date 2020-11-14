<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\mars_common\MediaHelper;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\mars_lighthouse\Traits\EntityBrowserFormTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CarouselBlock.
 *
 * @Block(
 *   id = "carousel_block",
 *   admin_label = @Translation("MARS: Carousel component"),
 *   category = @Translation("Page components"),
 * )
 *
 * @package Drupal\mars_common\Plugin\Block
 */
class CarouselBlock extends BlockBase implements ContextAwarePluginInterface, ContainerFactoryPluginInterface {

  use EntityBrowserFormTrait;

  /**
   * Lighthouse entity browser id.
   */
  const LIGHTHOUSE_ENTITY_BROWSER_ID = 'lighthouse_browser';

  /**
   * Lighthouse entity browser video id.
   */
  const LIGHTHOUSE_ENTITY_BROWSER_VIDEO_ID = 'lighthouse_video_browser';

  /**
   * Key option video.
   */
  const KEY_OPTION_VIDEO = 'video';

  /**
   * Key option image.
   */
  const KEY_OPTION_IMAGE = 'image';

  /**
   * Mars Media Helper service.
   *
   * @var \Drupal\mars_common\MediaHelper
   */
  protected $mediaHelper;

  /**
   * Theme configurator parser.
   *
   * @var \Drupal\mars_common\ThemeConfiguratorParser
   */
  protected $themeConfiguratorParser;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    MediaHelper $media_helper,
    ThemeConfiguratorParser $theme_configurator_parser
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->mediaHelper = $media_helper;
    $this->themeConfiguratorParser = $theme_configurator_parser;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('mars_common.media_helper'),
      $container->get('mars_common.theme_configurator_parser')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    $items = [];
    $build = [];
    foreach ($config['carousel'] as $item_value) {
      if ($item_value['item_type'] == self::KEY_OPTION_IMAGE) {
        $media_id = $this->mediaHelper->getIdFromEntityBrowserSelectValue($item_value['image']);
      }
      elseif ($item_value['item_type'] == self::KEY_OPTION_VIDEO) {
        $media_id = $this->mediaHelper->getIdFromEntityBrowserSelectValue($item_value['video']);
      }
      $media_params = $this->mediaHelper->getMediaParametersById($media_id);
      if (!($media_params['error'] ?? FALSE) && ($media_params['src'] ?? FALSE)) {
        $item = [
          'src' => $media_params['src'],
          'content' => $item_value['description'],
          'video' => ($item_value['item_type'] == self::KEY_OPTION_VIDEO),
          'image' => ($item_value['item_type'] == self::KEY_OPTION_IMAGE),
          'alt' => NULL,
          'title' => NULL,
        ];
        $items[] = $item;
      }
    }

    $build['#brand_borders'] = $this->themeConfiguratorParser->getBrandBorder('carousel');

    $build['#title'] = $config['carousel_label'] ?? '';
    $build['#items'] = $items;
    $build['#theme'] = 'carousel_component';
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $config = $this->getConfiguration();
    $form['carousel_label'] = [
      '#title'         => $this->t('Carousel title'),
      '#type'          => 'textfield',
      '#default_value' => $config['carousel_label'],
      '#maxlength' => 55,
    ];

    $form['carousel'] = [
      '#type' => 'fieldset',
      '#tree' => TRUE,
      '#title' => $this->t('Carousel items'),
      '#prefix' => '<div id="carousel-wrapper">',
      '#suffix' => '</div>',
    ];

    $carousel_settings = !empty($config['carousel']) ? $config['carousel'] : '';
    $carousel_storage = $form_state->get('carousel_storage');
    if (!isset($carousel_storage)) {
      if (!empty($carousel_settings)) {
        $carousel_storage = array_keys($carousel_settings);
      }
      else {
        $carousel_storage = [];
      }
      $form_state->set('carousel_storage', $carousel_storage);
    }

    $triggered = $form_state->getTriggeringElement();
    if (isset($triggered['#parents'][3]) && $triggered['#parents'][3] == 'remove_item') {
      $carousel_storage = $form_state->get('carousel_storage');
      $id = $triggered['#parents'][2];
      unset($carousel_storage[$id]);
    }

    foreach ($carousel_storage as $key => $value) {
      $form['carousel'][$key] = [
        '#type'  => 'details',
        '#title' => $this->t('Carousel items'),
        '#open'  => TRUE,
      ];

      $form['carousel'][$key]['item_type'] = [
        '#title'         => $this->t('Carousel item type'),
        '#type'          => 'select',
        '#required'      => TRUE,
        '#default_value' => $config['carousel'][$key]['number'],
        '#options' => [
          self::KEY_OPTION_IMAGE => $this->t('Image'),
          self::KEY_OPTION_VIDEO => $this->t('Video'),
        ],
        '#default_value' => $config['block_type'] ?? self::KEY_OPTION_IMAGE,
      ];
      $form['carousel'][$key]['description'] = [
        '#title'         => $this->t('Carousel item description'),
        '#type'          => 'textfield',
        '#default_value' => $config['carousel'][$key]['description'],
        '#maxlength'     => 120,
      ];

      $form['carousel'][$key]['image'] = $this->getEntityBrowserForm(self::LIGHTHOUSE_ENTITY_BROWSER_ID,
        $config['carousel'][$key]['image'], 1, 'thumbnail');
      $form['carousel'][$key]['image']['#type'] = 'details';
      $form['carousel'][$key]['image']['#title'] = $this->t('List item image');
      $form['carousel'][$key]['image']['#open'] = TRUE;
      $form['carousel'][$key]['image']['#states'] = [
        'visible' => [
          [':input[name="settings[carousel][' . $key . '][item_type]"]' => ['value' => self::KEY_OPTION_IMAGE]],
        ],
        'required' => [
          [':input[name="settings[carousel][' . $key . '][item_type]"]' => ['value' => self::KEY_OPTION_IMAGE]],
        ],
      ];

      $form['carousel'][$key]['video'] = $this->getEntityBrowserForm(self::LIGHTHOUSE_ENTITY_BROWSER_VIDEO_ID,
        $config['carousel'][$key]['video'], 1);
      $form['carousel'][$key]['video']['#type'] = 'details';
      $form['carousel'][$key]['video']['#title'] = $this->t('List item video');
      $form['carousel'][$key]['video']['#open'] = TRUE;
      $form['carousel'][$key]['video']['#states'] = [
        'visible' => [
          [':input[name="settings[carousel][' . $key . '][item_type]"]' => ['value' => self::KEY_OPTION_VIDEO]],
        ],
        'required' => [
          [':input[name="settings[carousel][' . $key . '][item_type]"]' => ['value' => self::KEY_OPTION_VIDEO]],
        ],
      ];

      $form['carousel'][$key]['remove_item'] = [
        '#type'  => 'button',
        '#name'  => 'carousel_' . $key,
        '#value' => $this->t('Remove carousel item'),
        '#ajax'  => [
          'callback' => [$this, 'ajaxRemoveCarouselItemCallback'],
          'wrapper'  => 'carousel-wrapper',
        ],
      ];
    }

    $form['carousel']['add_item'] = [
      '#type'  => 'submit',
      '#value' => $this->t('Add new carousel item'),
      '#ajax'  => [
        'callback' => [$this, 'ajaxAddCarouselItemCallback'],
        'wrapper'  => 'carousel-wrapper',
      ],
      '#limit_validation_errors' => [],
      '#submit' => [[$this, 'addCarouselItemSubmitted']],
    ];

    return $form;
  }

  /**
   * Add new carousel item callback.
   *
   * @param array $form
   *   Theme settings form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Theme settings form state.
   *
   * @return array
   *   List container of configuration settings.
   */
  public function ajaxAddCarouselItemCallback(array $form, FormStateInterface $form_state) {
    return $form['settings']['carousel'];
  }

  /**
   * Add remove carousel item callback.
   *
   * @param array $form
   *   Theme settings form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Theme settings form state.
   *
   * @return array
   *   List container of configuration settings.
   */
  public function ajaxRemoveCarouselItemCallback(array $form, FormStateInterface $form_state) {
    return $form['settings']['carousel'];
  }

  /**
   * Custom submit carousel configuration settings form.
   *
   * @param array $form
   *   Theme settings form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Theme settings form state.
   */
  public function addCarouselItemSubmitted(array $form, FormStateInterface $form_state) {
    $storage = $form_state->get('carousel_storage');
    array_push($storage, 1);
    $form_state->set('carousel_storage', $storage);
    $form_state->setRebuild(TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    unset($values['carousel']['add_item']);
    $this->setConfiguration($values);
    if (isset($values['carousel']) && !empty($values['carousel'])) {
      foreach ($values['carousel'] as $key => $item) {
        $this->configuration['carousel'][$key][$item['item_type']] = $this->getEntityBrowserValue($form_state, [
          'carousel',
          $key,
          $item['item_type'],
        ]);
      }
    }
  }

}
