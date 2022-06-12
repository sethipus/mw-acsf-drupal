<?php

namespace Drupal\mars_product\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\mars_common\LanguageHelper;
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
   * Language helper service.
   *
   * @var \Drupal\mars_common\LanguageHelper
   */
  private $languageHelper;

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

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
          $container->get('mars_common.language_helper'),
          $entity_storage,
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
        LanguageHelper $language_helper,
        EntityStorageInterface $entity_storage
    ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityStorage = $entity_storage;
    $this->languageHelper = $language_helper;
    $this->configFactory = $config_factory;
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
    $myView->setArguments(
          [
            $this->languageHelper->translate($conf['title']) ?? '',
            $conf['with_brand_borders'] ?? NULL,
            $conf['overlaps_previous'] ?? NULL,
          ]
      );

    return $myView->render($conf['display']);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    $config = $this->getConfiguration();
    return [
      'label_display' => FALSE,
      'title' => $this->t('All Products'),
      'with_brand_borders' => $config['with_brand_borders'] ?? FALSE,
      'overlaps_previous' => $config['overlaps_previous'] ?? FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $conf = $this->getConfiguration();
    $form = parent::buildConfigurationForm($form, $form_state);
    $character_limit_config = $this->configFactory->get('mars_common.character_limit_page');

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

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#maxlength' => !empty($character_limit_config->get('grid_card_title')) ? $character_limit_config->get('grid_card_title') : 55,
      '#default_value' => $this->configuration['title'] ?? '',
      '#required' => TRUE,
    ];

    $form['with_brand_borders'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('With/without brand border'),
      '#default_value' => $this->configuration['with_brand_borders'] ?? FALSE,
    ];

    $form['overlaps_previous'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('With/without overlaps previous'),
      '#default_value' => $this->configuration['overlaps_previous'] ?? FALSE,
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
    $this->configuration['title'] = $form_state->getValue('title');
    $this->configuration['with_brand_borders'] = $form_state->getValue('with_brand_borders');
    $this->configuration['overlaps_previous'] = $form_state->getValue('overlaps_previous');
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
