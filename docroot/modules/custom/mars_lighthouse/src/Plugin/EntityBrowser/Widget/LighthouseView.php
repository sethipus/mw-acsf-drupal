<?php

namespace Drupal\mars_lighthouse\Plugin\EntityBrowser\Widget;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\entity_browser\WidgetBase;
use Drupal\entity_browser\WidgetValidationManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Drupal\mars_lighthouse\LighthouseInterface;

/**
 * Uses a lighthouse requests to provide entity listing in a browser's widget.
 *
 * @EntityBrowserWidget(
 *   id = "lighthouse_view",
 *   label = @Translation("Lighthouse View"),
 *   description = @Translation("Uses a lighthouse requests to provide entity
 *   listing in a browser's widget."),
 *   auto_select = TRUE
 * )
 */
class LighthouseView extends WidgetBase implements ContainerFactoryPluginInterface {

  /**
   * Number of columns in grid.
   */
  const COLUMN_NUM = 3;

  /**
   * Lighthouse adapter.
   *
   * @var \Drupal\mars_lighthouse\LighthouseInterface
   */
  protected $lighthouseAdapter;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EventDispatcherInterface $event_dispatcher, EntityTypeManagerInterface $entity_type_manager, WidgetValidationManager $validation_manager, LighthouseInterface $lighthouse) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $event_dispatcher, $entity_type_manager, $validation_manager);
    $this->lighthouseAdapter = $lighthouse;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('event_dispatcher'),
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.entity_browser.widget_validation'),
      $container->get('lighthouse.adapter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function validate(array &$form, FormStateInterface $form_state) {
    // TODO: Add a validation.
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareEntities(array $form, FormStateInterface $form_state) {
    $selected_rows = array_filter($form_state->cleanValues()
      ->getUserInput()['checkboxes']);

    $entities = [];
    foreach ($selected_rows as $row) {
      $entities[] = $this->lighthouseAdapter->getMediaEntity($row);
    }
    return $entities;
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(array &$original_form, FormStateInterface $form_state, array $additional_widget_parameters) {
    $form = parent::getForm($original_form, $form_state, $additional_widget_parameters);
    $form['#attached']['library'] = ['entity_browser/view'];

    $form['filter']['text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Text'),
      '#size' => 60,
    ];
    $form['filter']['submit'] = [
      '#type' => 'button',
      '#value' => $this->t('Filter'),
      '#ajax' => [
        'callback' => [$this, 'searchCallback'],
        'wrapper' => 'gallery-view',
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Searching...'),
        ],
      ],
    ];

    $data = $this->lighthouseAdapter->getMediaDataList();
    // Split into rows.
    $grid = array_chunk($data, $this::COLUMN_NUM);

    $form['view']['data'] = [
      '#theme' => 'lighthouse_gallery',
      '#data' => $grid,
      '#prefix' => '<div id="gallery-view">',
      '#suffix' => '</div>',
    ];
    // TODO: create a better view.
    $form['view']['checkboxes'] = [
      '#type' => 'checkboxes',
      '#options' => array_flip(array_column($data, 'assetId')),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array &$element, array &$form, FormStateInterface $form_state) {
    $entities = $this->prepareEntities($form, $form_state);
    $this->selectEntities($entities, $form_state);
  }

  /**
   * Ajax search response.
   */
  public function searchCallback(array $form, FormStateInterface $form_state) {
    $text = $form_state->getValue('text');

    $data = $this->lighthouseAdapter->getMediaDataList($text);
    // Split into rows.
    $data = array_chunk($data, $this::COLUMN_NUM);

    return [
      '#theme' => 'lighthouse_gallery',
      '#data' => $data,
      '#prefix' => '<div id="gallery-view">',
      '#suffix' => '</div>',
    ];
  }

}
