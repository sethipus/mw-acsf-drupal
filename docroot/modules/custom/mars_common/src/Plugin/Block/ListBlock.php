<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_media\MediaHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\mars_lighthouse\Traits\EntityBrowserFormTrait;

/**
 * Class ListBlock is responsible for List component logic.
 *
 * @Block(
 *   id = "list_block",
 *   admin_label = @Translation("MARS: List component"),
 *   category = @Translation("Page components"),
 * )
 *
 * @package Drupal\mars_common\Plugin\Block
 */
class ListBlock extends BlockBase implements ContextAwarePluginInterface, ContainerFactoryPluginInterface {

  use EntityBrowserFormTrait;

  /**
   * Lighthouse entity browser id.
   */
  const LIGHTHOUSE_ENTITY_BROWSER_ID = 'lighthouse_browser';

  /**
   * Mars Media Helper service.
   *
   * @var \Drupal\mars_media\MediaHelper
   */
  protected $mediaHelper;

  /**
   * The configFactory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Language helper service.
   *
   * @var \Drupal\mars_common\LanguageHelper
   */
  private $languageHelper;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ConfigFactoryInterface $config_factory,
    LanguageHelper $language_helper,
    MediaHelper $media_helper
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->languageHelper = $language_helper;
    $this->mediaHelper = $media_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('mars_common.language_helper'),
      $container->get('mars_media.media_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    $list_items = $config['list'];
    $ol_items = [];
    $build = [];
    foreach ($list_items as $item_value) {
      $item = [
        'content' => $this->languageHelper->translate($item_value['description']),
        'item_number' => $this->languageHelper->translate($item_value['number']),
      ];

      if (!empty($item_value['image'])) {
        $media_id = $this->mediaHelper->getIdFromEntityBrowserSelectValue($item_value['image']);
        $media_params = $this->mediaHelper->getMediaParametersById($media_id);
        if (!($media_params['error'] ?? FALSE) && ($media_params['src'] ?? FALSE)) {
          $item['image'] = [
            'src' => $media_params['src'],
            'alt' => $media_params['alt'],
            'title' => $media_params['title'],
          ];
        }
      }

      $ol_items[] = $item;
    }
    $build['#label'] = $this->languageHelper->translate($config['list_label']) ?? '';
    $build['#ol_items'] = $ol_items;
    $build['#theme'] = 'list_component';
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $config = $this->getConfiguration();
    $character_limit_config = $this->configFactory->getEditable('mars_common.character_limit_page');
    $form['list_label'] = [
      '#title'         => $this->t('List title'),
      '#type'          => 'textfield',
      '#default_value' => $config['list_label'],
      '#maxlength' => !empty($character_limit_config->get('list_component_title')) ? $character_limit_config->get('list_component_title') : 55,
    ];

    $form['list'] = [
      '#type' => 'fieldset',
      '#tree' => TRUE,
      '#title' => $this->t('List items'),
      '#prefix' => '<div id="list-wrapper">',
      '#suffix' => '</div>',
    ];

    $list_settings = !empty($config['list']) ? $config['list'] : '';
    $list_storage = $form_state->get('list_storage');
    if (!isset($list_storage)) {
      if (!empty($list_settings)) {
        $list_storage = array_keys($list_settings);
      }
      else {
        $list_storage = [];
      }
      $form_state->set('list_storage', $list_storage);
    }

    $triggered = $form_state->getTriggeringElement();
    if (isset($triggered['#parents'][3]) && $triggered['#parents'][3] == 'remove_item') {
      $list_storage = $form_state->get('list_storage');
      $id = $triggered['#parents'][2];
      unset($list_storage[$id]);
    }

    foreach ($list_storage as $key => $value) {
      $form['list'][$key] = [
        '#type'  => 'details',
        '#title' => $this->t('List items'),
        '#open'  => TRUE,
      ];

      $form['list'][$key]['number'] = [
        '#title'         => $this->t('List element number'),
        '#type'          => 'textfield',
        '#required'      => TRUE,
        '#default_value' => $config['list'][$key]['number'],
        '#maxlength'     => !empty($character_limit_config->get('list_component_element_number')) ? $character_limit_config->get('list_component_element_number') : 5,
      ];
      $form['list'][$key]['description'] = [
        '#title'         => $this->t('List item description'),
        '#type'          => 'textarea',
        '#required'      => TRUE,
        '#default_value' => $config['list'][$key]['description'],
      ];

      $form['list'][$key]['image'] = $this->getEntityBrowserForm(self::LIGHTHOUSE_ENTITY_BROWSER_ID,
        $config['list'][$key]['image'], $form_state, 1, 'thumbnail', FALSE);
      $form['list'][$key]['image']['#type'] = 'details';
      $form['list'][$key]['image']['#title'] = $this->t('List item image');
      $form['list'][$key]['image']['#open'] = TRUE;

      $form['list'][$key]['remove_item'] = [
        '#type'  => 'button',
        '#name'  => 'list_' . $key,
        '#value' => $this->t('Remove list item'),
        '#ajax'  => [
          'callback' => [$this, 'ajaxRemoveListItemCallback'],
          'wrapper'  => 'list-wrapper',
        ],
      ];
    }

    $form['list']['add_item'] = [
      '#type'  => 'submit',
      '#value' => $this->t('Add new list item'),
      '#ajax'  => [
        'callback' => [$this, 'ajaxAddListItemCallback'],
        'wrapper'  => 'list-wrapper',
      ],
      '#limit_validation_errors' => [],
      '#submit' => [[$this, 'addListItemSubmitted']],
    ];

    return $form;
  }

  /**
   * Add new list item callback.
   *
   * @param array $form
   *   Theme settings form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Theme settings form state.
   *
   * @return array
   *   List container of configuration settings.
   */
  public function ajaxAddListItemCallback(array $form, FormStateInterface $form_state) {
    return $form['settings']['list'];
  }

  /**
   * Add remove list item callback.
   *
   * @param array $form
   *   Theme settings form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Theme settings form state.
   *
   * @return array
   *   List container of configuration settings.
   */
  public function ajaxRemoveListItemCallback(array $form, FormStateInterface $form_state) {
    return $form['settings']['list'];
  }

  /**
   * Custom submit list configuration settings form.
   *
   * @param array $form
   *   Theme settings form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Theme settings form state.
   */
  public function addListItemSubmitted(array $form, FormStateInterface $form_state) {
    $storage = $form_state->get('list_storage');
    array_push($storage, 1);
    $form_state->set('list_storage', $storage);
    $form_state->setRebuild(TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    unset($values['list']['add_item']);
    $this->setConfiguration($values);
    if (isset($values['list']) && !empty($values['list'])) {
      foreach ($values['list'] as $key => $item) {
        $this->configuration['list'][$key]['image'] = $this->getEntityBrowserValue($form_state, [
          'list',
          $key,
          'image',
        ]);
      }
    }
  }

}
