<?php

namespace Drupal\mars_product\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a grid card block.
 *
 * @Block(
 *   id = "grid_card",
 *   admin_label = @Translation("MARS: Grid Card"),
 *   category = @Translation("Mars Product")
 * )
 */
class GridCardBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $entityStorage;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $entity_type_manager = $container->get('entity_type.manager');
    $entity_storage = $entity_type_manager->getStorage('node');

    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $entity_storage
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityStorageInterface $entity_storage
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityStorage = $entity_storage;
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $conf = $this->getConfiguration();
    $myView = Views::getView($conf['view']);
    if (!is_object($myView)) {
      return [];
    }

    $myView->setDisplay($conf['display']);
    $myView->preExecute();

    return $myView->render($conf['display']);
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
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $conf = $this->getConfiguration();
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['view'] = [
      '#title' => $this->t('View'),
      '#type' => 'select',
      '#options' => $this->getViews(),
      '#default_value' => $conf['view'] ?? NULL,
      '#required' => TRUE,
      '#ajax' => [
        'callback' => [$this, 'getViewDisplays'],
        'disable-refocus' => FALSE,
        'event' => 'change',
        'wrapper' => 'view-display-input',
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Please wait...'),
        ],
      ],
    ];

    $displayOptions = [];
    if ($conf['view'] && $conf['display']) {
      $displayOptions = $this->getDisplays($conf['view']);
    }
    $form['display'] = [
      '#title' => $this->t('Display'),
      '#type' => 'select',
      '#options' => $displayOptions,
      '#default_value' => $conf['display'] ?? NULL,
      '#required' => TRUE,
      '#validated' => TRUE,
      '#attributes' => [
        'id' => 'view-display-input',
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state): void {
    parent::blockSubmit($form, $form_state);
    $this->configuration['view'] = $form_state->getValue('view');
    $this->configuration['display'] = $form_state->getUserInput()['settings']['display'];
  }

  /**
   * Returns Drupal Views.
   *
   * @return array
   *   View items
   */
  private function getViews(): array {
    $views = Views::getEnabledViews();

    $result = [];
    foreach ($views as $view) {
      $result[$view->id()] = $view->label();
    }

    return $result;
  }

  /**
   * Returns View's displays.
   *
   * @return array
   *   Display items
   */
  public function getViewDisplays(array &$form, FormStateInterface $form_state): array {
    $userView = $form_state->getUserInput()['settings']['view'] ?? NULL;
    $options = $this->getDisplays($userView);

    $form['display'] = [
      '#type' => 'select',
      '#options' => $options,
      '#default_value' => NULL,
      '#required' => TRUE,
      '#validated' => TRUE,
      '#attributes' => [
        'id' => 'view-display-input',
        'name' => 'settings[display]',
      ],
    ];

    return $form['display'];
  }

  /**
   * Collects displays related to View.
   *
   * @param string $userView
   *   View.
   *
   * @return array
   *   Views
   */
  private function getDisplays($userView): array {
    $displays = Views::getApplicableViews('id');
    $options = [];
    foreach ($displays as $data) {
      [$view_id, $display_id] = $data;
      if ($view_id !== $userView) {
        continue;
      }

      $options[$display_id] = $display_id;
    }
    return $options;
  }

}
