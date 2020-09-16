<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a Header block.
 *
 * @Block(
 *   id = "header_block",
 *   admin_label = @Translation("MARS: Header block")
 * )
 */
class HeaderBlock extends BlockBase implements ContainerFactoryPluginInterface {
  /**
   * Menu link tree.
   *
   * @var \Drupal\Core\Menu\MenuLinkTreeInterface
   */
  protected $menuLinkTree;
  /**
   * Menu storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $menuStorage;
  /**
   * File storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $fileStorage;
  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;
  /**
   * Request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    MenuLinkTreeInterface $menu_link_tree,
    EntityTypeManagerInterface $entity_type_manager,
    ConfigFactoryInterface $config_factory,
    Request $request,
    FormBuilderInterface $form_builder,
    RendererInterface $renderer
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->menuLinkTree = $menu_link_tree;
    $this->menuStorage = $entity_type_manager->getStorage('menu');
    $this->fileStorage = $entity_type_manager->getStorage('file');
    $this->config = $config_factory;
    $this->request = $request;
    $this->formBuilder = $form_builder;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('menu.link_tree'),
      $container->get('entity_type.manager'),
      $container->get('config.factory'),
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('form_builder'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $config = $this->getConfiguration();

    $options = $this->getMenus();
    $form['primary_menu'] = [
      '#type' => 'select',
      '#title' => $this->t('Primary Menu'),
      '#description' => $this->t('Primary Menu'),
      '#options' => $options,
      '#default_value' => $config['primary_menu'] ?? '',
    ];
    $form['secondary_menu'] = [
      '#type' => 'select',
      '#title' => $this->t('Secondary Menu'),
      '#description' => $this->t('Secondary Menu'),
      '#options' => $options,
      '#default_value' => $config['secondary_menu'] ?? '',
    ];
    $form['search_block'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Search block'),
      '#description' => $this->t('Display Search block?'),
      '#default_value' => $config['search_block'] ?? TRUE,
    ];
    $form['language_selector'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Language Selector block'),
      '#description' => $this->t('Display Language Selector block?'),
      '#default_value' => $config['language_selector'] ?? TRUE,
    ];
    $form['alert_banner'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Alert banner'),
      '#description' => $this->t('This text will appear in Alert Banner.'),
      '#default_value' => $config['alert_banner']['value'] ?? '',
      '#format' => $config['alert_banner']['format'] ?? 'plain_text',
    ];

    return $form;
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
    $this->setConfiguration($form_state->getValues());
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    $theme_settings = $this->config->get('emulsifymars.settings')->get();

    $build['#logo'] = $theme_settings['logo']['path'] ?? '';

    $build['#alert_banner'] = $config['alert_banner']['value'];
    if ($config['search_block']) {
      $host = $this->request->getSchemeAndHttpHost();
      $build['#search_menu'] = [['title' => 'Search', 'url' => $host]];
    }
    $build['#primary_menu'] = $this->buildMenu($config['primary_menu']);
    $build['#secondary_menu'] = $this->buildMenu($config['secondary_menu']);

    $build['#theme'] = 'header_block';

    $build['#search_form'] = $this->buildSearchForm();

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getMenus() {
    $menus = $this->menuStorage->loadMultiple();

    $options = ['' => $this->t('None')];
    foreach ($menus as $menu) {
      $options[$menu->id()] = $menu->label();
    }

    return $options;
  }

  /**
   * Render menu by its name.
   *
   * @param string $menu_name
   *   Menu name.
   *
   * @return array
   *   Rendered menu.
   */
  protected function buildMenu($menu_name) {
    $menu_parameters = new MenuTreeParameters();
    $menu_parameters->setMaxDepth(1);
    // Get the tree.
    $tree = $this->menuLinkTree->load($menu_name, $menu_parameters);
    // Apply some manipulators (checking the access, sorting).
    $manipulators = [
      ['callable' => 'menu.default_tree_manipulators:checkNodeAccess'],
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ];
    $tree = $this->menuLinkTree->transform($tree, $manipulators);
    // And the last step is to actually build the tree.
    $menu = $this->menuLinkTree->build($tree);
    $menu_links = [];
    if (!empty($menu['#items'])) {
      foreach ($menu['#items'] as $item) {
        array_push($menu_links, ['title' => $item['title'], 'url' => $item['url']->setAbsolute()->toString()]);
      }
    }
    return $menu_links;
  }

  /**
   * Render search form.
   *
   * @return string
   *   Search form HTML.
   */
  protected function buildSearchForm() {
    $form = [];
    $view_id = 'acquia_search';
    $display_id = 'page';
    $view = Views::getView($view_id);
    if ($view) {
      $view->setDisplay($display_id);
      $view->initHandlers();
      $form_state = (new FormState())
        ->setStorage([
          'view' => $view,
          'display' => &$view->display_handler->display,
          'rerender' => TRUE,
        ])
        ->setMethod('get')
        ->setAlwaysProcess()
        ->disableRedirect();
      $form_state->set('rerender', NULL);
      $form = $this->formBuilder->buildForm('\Drupal\views\Form\ViewsExposedForm', $form_state);
      $form['search']['#attributes']['class'][] = 'mars-autocomplete-field';
      $form['search']['#attributes']['data-view_id'] = $view_id;
      $form['search']['#attributes']['data-display_id'] = $display_id;
      $form['search']['#title_display'] = 'none';
      $form['search']['#placeholder'] = $this->t('Search');
      unset($form['actions']['submit']);
    }

    return $this->renderer->render($form);
  }

}
