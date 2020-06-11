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
    // TODO add validation.
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareEntities(array $form, FormStateInterface $form_state) {
    // TODO write the function.
    // Copied from \Drupal\entity_browser\Plugin\EntityBrowser\Widget\View::prepareEntities.
    if (is_array($form_state->getUserInput()['entity_browser_select'])) {
      $selected_rows = array_values(array_filter($form_state->getUserInput()['entity_browser_select']));
    }
    else {
      $selected_rows = [$form_state->getUserInput()['entity_browser_select']];
    }

    $entities = [];
    foreach ($selected_rows as $row) {
      $item = explode(':', $row);
      if (count($item) == 2) {
        [$type, $id] = $item;
        $storage = $this->entityTypeManager->getStorage($type);
        if ($entity = $storage->load($id)) {
          $entities[] = $entity;
        }
      }
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

    $data = $this->lighthouseAdapter->getMediaDataList('cat');
    // Split into rows.
    $data = array_chunk($data, 3);

    $form['view'] = [
      '#theme' => 'lighthouse_gallery',
      '#data' => $data,
      '#prefix' => '<div id="gallery-view">',
      '#suffix' => '</div>',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['view']['view'] = [
      '#markup' => 'Test markup',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array &$element, array &$form, FormStateInterface $form_state) {
    // TODO write submit function.
    parent::submit($element, $form, $form_state);
  }

  /**
   * Ajax search response.
   */
  public function searchCallback(array $form, FormStateInterface $form_state) {
    $text = $form_state->getValue('text');

    $data = $this->lighthouseAdapter->getMediaDataList($text);
    // Split into rows.
    $data = array_chunk($data, 3);

    return [
      '#theme' => 'lighthouse_gallery',
      '#data' => $data,
      '#prefix' => '<div id="gallery-view">',
      '#suffix' => '</div>',
    ];
  }

}
