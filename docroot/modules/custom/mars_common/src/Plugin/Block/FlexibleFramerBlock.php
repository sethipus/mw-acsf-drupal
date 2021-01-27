<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_common\MediaHelper;
use Drupal\mars_lighthouse\Traits\EntityBrowserFormTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\mars_common\ThemeConfiguratorParser;

/**
 * Provides a Flexible Framer component block.
 *
 * @Block(
 *   id = "flexible_framer_block",
 *   admin_label = @Translation("MARS: Flexible Framer block"),
 *   category = @Translation("Page components"),
 * )
 */
class FlexibleFramerBlock extends BlockBase implements ContainerFactoryPluginInterface {

  use EntityBrowserFormTrait;

  /**
   * Mars Media Helper service.
   *
   * @var \Drupal\mars_common\MediaHelper
   */
  protected $mediaHelper;

  /**
   * Language helper service.
   *
   * @var \Drupal\mars_common\LanguageHelper
   */
  private $languageHelper;

  /**
   * Mars Theme Configurator Parserr service.
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
    LanguageHelper $language_helper,
    ThemeConfiguratorParser $theme_configurator_parser
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->mediaHelper = $media_helper;
    $this->languageHelper = $language_helper;
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
      $container->get('mars_common.language_helper'),
      $container->get('mars_common.theme_configurator_parser')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $config = $this->getConfiguration();

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Header'),
      '#maxlength' => 55,
      '#default_value' => $config['title'] ?? '',
    ];

    $form['with_cta'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('With/without CTA'),
      '#default_value' => $config['with_cta'] ?? TRUE,
    ];

    $form['with_description'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('With/without description'),
      '#default_value' => $config['with_description'] ?? TRUE,
    ];

    $form['with_image'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('With/without image'),
      '#default_value' => $config['with_image'] ?? TRUE,
    ];

    $form['items'] = [
      '#type' => 'fieldset',
      '#tree' => TRUE,
      '#title' => $this->t('Setup items'),
      '#description' => $this->t('Up to 4 additional items.'),
      '#prefix' => '<div id="items-wrapper">',
      '#suffix' => '</div>',
    ];

    $items_settings = !empty($config['items']) ? $config['items'] : '';
    $items_storage = $form_state->get('items_storage');
    if (!isset($items_storage)) {
      if (!empty($items_settings)) {
        $items_storage = array_keys($items_settings);
      }
      else {
        $items_storage = [];
      }
      $form_state->set('items_storage', $items_storage);
    }

    $triggered = $form_state->getTriggeringElement();
    if (isset($triggered['#parents'][3]) && $triggered['#parents'][3] == 'remove_item') {
      $items_storage = $form_state->get('items_storage');
      $id = $triggered['#parents'][2];
      unset($items_storage[$id]);
      $form_state->set('items_storage', $items_storage);
    }

    foreach ($items_storage as $key => $value) {
      $form['items'][$key] = [
        '#type' => 'details',
        '#title' => $this->t('Flexible framer item'),
        '#open' => TRUE,
      ];
      $form['items'][$key]['title'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Item title'),
        '#maxlength' => 60,
        '#default_value' => $config['items'][$key]['title'] ?? '',
        '#required' => TRUE,
      ];
      $form['items'][$key]['cta']['title'] = [
        '#type' => 'textfield',
        '#title' => $this->t('CTA Link Title'),
        '#maxlength' => 15,
        '#default_value' => $config['items'][$key]['cta']['title'] ?? $this->t('Explore'),
      ];
      $form['items'][$key]['cta']['url'] = [
        '#type' => 'textfield',
        '#title' => $this->t('CTA Link URL'),
        '#description' => $this->t('Please check if string starts with: "/", "http://", "https://".'),
        '#maxlength' => 2048,
        '#default_value' => $config['items'][$key]['cta']['url'] ?? '',
      ];
      $form['items'][$key]['description'] = [
        '#type' => 'textarea',
        '#title' => $this->t('Item description'),
        '#default_value' => $config['items'][$key]['description'] ?? '',
        '#maxlength' => 255,
      ];

      $item_image = isset($config['items'][$key]['item_image']) ? $config['items'][$key]['item_image'] : NULL;
      $form['items'][$key]['item_image'] = $this->getEntityBrowserForm(ImageVideoBlockBase::LIGHTHOUSE_ENTITY_BROWSER_IMAGE_ID,
        $item_image, $form_state, 1, 'thumbnail', FALSE);
      // Convert the wrapping container to a details element.
      $form['items'][$key]['item_image']['#type'] = 'details';
      $form['items'][$key]['item_image']['#title'] = $this->t('Item Image');
      $form['items'][$key]['item_image']['#open'] = TRUE;

      $form['items'][$key]['remove_item'] = [
        '#type'  => 'button',
        '#name' => 'item_' . $key,
        '#value' => $this->t('Remove item'),
        '#ajax'  => [
          'callback' => [$this, 'ajaxRemoveItemCallback'],
          'wrapper' => 'items-wrapper',
        ],
      ];
    }
    if (count($items_storage) < 4) {
      $form['items']['add_item'] = [
        '#type' => 'submit',
        '#value' => $this->t('Add item'),
        '#ajax' => [
          'callback' => [$this, 'ajaxAddItemCallback'],
          'wrapper' => 'items-wrapper',
        ],
        '#limit_validation_errors' => [],
        '#submit' => [[$this, 'addItemSubmitted']],
      ];
    }

    return $form;
  }

  /**
   * Add new item link callback.
   *
   * @param array $form
   *   Theme settings form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Theme settings form state.
   *
   * @return array
   *   Item container of configuration settings.
   */
  public function ajaxAddItemCallback(array $form, FormStateInterface $form_state) {
    return $form['settings']['items'];
  }

  /**
   * Add remove item callback.
   *
   * @param array $form
   *   Theme settings form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Theme settings form state.
   *
   * @return array
   *   Item container of configuration settings.
   */
  public function ajaxRemoveItemCallback(array $form, FormStateInterface $form_state) {
    return $form['settings']['items'];
  }

  /**
   * Custom submit item configuration settings form.
   *
   * @param array $form
   *   Theme settings form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Theme settings form state.
   */
  public function addItemSubmitted(array $form, FormStateInterface $form_state) {
    $storage = $form_state->get('items_storage');
    array_push($storage, 1);
    $form_state->set('items_storage', $storage);
    $form_state->setRebuild(TRUE);
  }

  /**
   * {@inheritdoc}
   *
   * This method processes the blockForm() form fields when the block
   * configuration form is submitted.
   *
   * The blockValidate() method can be used to validate the form submission.
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    unset($values['items']['add_item']);

    $this->setConfiguration($values);

    if (isset($values['items']) && !empty($values['items'])) {
      foreach ($values['items'] as $key => $item) {
        $this->configuration['items'][$key]['item_image'] = $this->getEntityBrowserValue($form_state, [
          'items',
          $key,
          'item_image',
        ]);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();

    $with_cta_flag = (bool) $config['with_cta'];
    $with_image_flag = (bool) $config['with_image'];
    $with_desc_flag = (bool) $config['with_description'];

    $ff_items = [];

    foreach ($config['items'] as $key => $item) {
      $ff_item = [
        'card__heading' => $this->languageHelper->translate($config['items'][$key]['title']) ?? NULL,
        'card__link__url' => ($with_cta_flag) ? $config['items'][$key]['cta']['url'] : NULL,
        'card__link__text' => ($with_cta_flag) ? $this->languageHelper->translate($config['items'][$key]['cta']['title']) : NULL,
        'card__body' => ($with_desc_flag) ? $this->languageHelper->translate($config['items'][$key]['description']) : NULL,
      ];

      if (!empty($config['items'][$key]['item_image']) && $with_image_flag) {

        $image_url = NULL;
        $media_id = $this->mediaHelper
          ->getIdFromEntityBrowserSelectValue($config['items'][$key]['item_image']);

        if ($media_id) {
          $media_params = $this->mediaHelper->getMediaParametersById($media_id);
          if (!isset($media_params['error'])) {
            $image_url = file_create_url($media_params['src']);
          }
        }
        $ff_item['card__image__src'] = $image_url;
        $ff_item['card__image__alt'] = $media_params['alt'] ?? NULL;
        $ff_item['card__image__title'] = $media_params['title'] ?? NULL;
      }
      $ff_items[] = $ff_item;
    }

    $file_divider_content = $this->themeConfiguratorParser->getGraphicDivider();
    $file_border_content = $this->themeConfiguratorParser->getBrandBorder2();

    $build['#items'] = $ff_items;
    $build['#grid_type'] = 'card';
    $build['#item_type'] = 'card';
    $build['#grid_label'] = $this->languageHelper->translate($config['title'] ?? NULL);
    $build['#divider'] = $file_divider_content ?? NULL;
    $build['#brand_borders'] = $file_border_content ?? NULL;
    $build['#theme'] = 'flexible_framer_block';

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
    if (!empty($form_state->get('items_storage')) && is_array($form_state->get('items_storage'))) {
      $keys = array_keys($form_state->get('items_storage'));
      foreach ($keys as $key) {
        $url = $form_state->getValue('items')[$key]['cta']['url'];
        if (!empty($url) && !(UrlHelper::isValid($url) && preg_match('/^(http:\/\/|https:\/\/|\/)/', $url))) {
          $form_state->setErrorByName('items][' . $key . '][cta][url', $this->t('The URL is not valid.'));
        }
      }
    }
  }

}
