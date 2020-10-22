<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\mars_common\MediaHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\mars_lighthouse\Traits\EntityBrowserFormTrait;

/**
 * Class ListBlock.
 *
 * @Block(
 *   id = "list_block",
 *   admin_label = @Translation("List component"),
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
   * A view builder instance.
   *
   * @var \Drupal\Core\Entity\EntityViewBuilderInterface
   */
  protected $viewBuilder;

  /**
   * Node storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * ThemeConfiguratorParser.
   *
   * @var \Drupal\mars_common\ThemeConfiguratorParser
   */
  protected $themeConfiguratorParser;

  /**
   * Mars Media Helper service.
   *
   * @var \Drupal\mars_common\MediaHelper
   */
  protected $mediaHelper;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    MediaHelper $media_helper
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->viewBuilder = $entity_type_manager->getViewBuilder('node');
    $this->nodeStorage = $entity_type_manager->getStorage('node');
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
      $container->get('entity_type.manager'),
      $container->get('mars_common.media_helper')
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
        'content' => $item_value['description'],
        'item_number' => $item_value['number'],
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
    $build['#label'] = $config['list_label'];
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
    $form['list_label'] = [
      '#title'         => $this->t('List title'),
      '#type'          => 'textfield',
      '#default_value' => $config['list_label'],
      '#maxlength' => 55,
    ];

    $form['list'] = [
      '#type' => 'fieldset',
      '#tree' => TRUE,
      '#title' => $this->t('Setup list items'),
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
        '#maxlength'     => 5,
      ];
      $form['list'][$key]['description'] = [
        '#title'         => $this->t('List item description'),
        '#type'          => 'textfield',
        '#required'      => TRUE,
        '#default_value' => $config['list'][$key]['description'],
        '#maxlength'     => 55,
      ];

      $form['list'][$key]['image'] = $this->getEntityBrowserForm(self::LIGHTHOUSE_ENTITY_BROWSER_ID,
        $config['list'][$key]['image'], 1, 'thumbnail');
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
   * Add remove card callback.
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
      foreach ($values['list'] as $key => $card) {
        $this->configuration['list'][$key]['image'] = $this->getEntityBrowserValue($form_state, [
          'list',
          $key,
          'image',
        ]);
      }
    }
  }

}
