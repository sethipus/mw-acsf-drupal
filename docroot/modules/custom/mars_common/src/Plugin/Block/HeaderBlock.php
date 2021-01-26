<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityMalformedException;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Url;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_common\ThemeConfiguratorParser;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
   * Drupal\Core\Routing\CurrentRouteMatch definition.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $currentRouteMatch;

  /**
   * The path matcher.
   *
   * @var \Drupal\Core\Path\PathMatcherInterface
   */
  protected $pathMatcher;

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
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * Language helper service.
   *
   * @var \Drupal\mars_common\LanguageHelper
   */
  private $languageHelper;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Theme configurator parser service.
   *
   * @var \Drupal\mars_common\ThemeConfiguratorParser
   */
  private $themeConfiguratorParser;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    CurrentRouteMatch $current_route_match,
    PathMatcherInterface $path_matcher,
    MenuLinkTreeInterface $menu_link_tree,
    EntityTypeManagerInterface $entity_type_manager,
    ConfigFactoryInterface $config_factory,
    FormBuilderInterface $form_builder,
    LanguageHelper $language_helper,
    RendererInterface $renderer,
    ThemeConfiguratorParser $theme_configurator_parser
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentRouteMatch = $current_route_match;
    $this->pathMatcher = $path_matcher;
    $this->menuLinkTree = $menu_link_tree;
    $this->menuStorage = $entity_type_manager->getStorage('menu');
    $this->fileStorage = $entity_type_manager->getStorage('file');
    $this->config = $config_factory;
    $this->formBuilder = $form_builder;
    $this->languageHelper = $language_helper;
    $this->renderer = $renderer;
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
      $container->get('current_route_match'),
      $container->get('path.matcher'),
      $container->get('menu.link_tree'),
      $container->get('entity_type.manager'),
      $container->get('config.factory'),
      $container->get('form_builder'),
      $container->get('mars_common.language_helper'),
      $container->get('renderer'),
      $container->get('mars_common.theme_configurator_parser')
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
      '#type' => 'details',
      '#title' => $this->t('Alert banner'),
      '#open' => TRUE,
    ];
    $form['alert_banner']['alert_banner_text'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Alert Banner text'),
      '#description' => $this->t('This text will appear in Alert Banner.'),
      '#default_value' => $config['alert_banner']['alert_banner_text']['value'] ?? '',
      '#format' => $config['alert_banner']['alert_banner_text']['format'] ?? 'plain_text',
      '#maxlength' => 100,
    ];
    $form['alert_banner']['alert_banner_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Alert Banner link'),
      '#description' => $this->t('Ex. http://mars.com, /products'),
      '#default_value' => $config['alert_banner']['alert_banner_url'] ?? '',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
    if ($alert_banner_url = $form_state->getValue('alert_banner')['alert_banner_url']) {
      // Check if textfield contains relative or absolute url.
      if (!(UrlHelper::isValid($alert_banner_url, TRUE) ||
        UrlHelper::isValid($alert_banner_url))) {
        $message = $this->t('Please check url (internal or external)');
        $form_state->setErrorByName('alert_banner_url', $message);
      }
    }
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

    $theme_logo_alt = $this->themeConfiguratorParser->getLogoAltFromTheme();
    $build['#logo_alt'] = ($theme_logo_alt)
      ? $this->languageHelper->translate($theme_logo_alt)
      : $theme_logo_alt;

    $build['#alert_banner_text'] = $this->languageHelper->translate($config['alert_banner']['alert_banner_text']['value']);
    $build['#alert_banner_url'] = $this->languageHelper->translate($config['alert_banner']['alert_banner_url']);
    $build['#primary_menu'] = $this->buildMenu($config['primary_menu'], 2);
    $build['#secondary_menu'] = $this->buildMenu($config['secondary_menu']);

    $current_language_id = $this->languageHelper->getCurrentLanguageId();
    $build['#language_selector_current'] = mb_strtoupper($current_language_id);
    $build['#language_selector_label'] = $this->languageHelper->translate('Select language');
    $language_selector_items = [];
    try {
      $language_selector_items = $this->getLanguageLinks();
    }
    catch (EntityMalformedException $entity_malformed_exception) {
    }
    $build['#language_selector_items'] = $language_selector_items;
    $build['#language_selector'] = $config['language_selector'] && count($build['#language_selector_items']);

    $build['#theme'] = 'header_block';

    $build['#search_form'] = $this->buildSearchForm();
    $build['#search_enabled'] = $config['search_block'] ?? TRUE;
    $build['#search_close_label'] = $this->config->get('mars_common.site_labels')->get('header_search_overlay_close');
    $build['#search_title'] = $this->config->get('mars_common.site_labels')->get('header_search_overlay');

    $build['#brand_border'] = $this->themeConfiguratorParser->getBrandBorder();

    return $build;
  }

  /**
   * Get language links.
   *
   * @return array
   *   Language selector links.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  protected function getLanguageLinks() {
    $languageManager = $this->languageHelper->getLanguageManager();
    $languages = $languageManager->getLanguages();
    $render_links = [];

    if (count($languages) > 1) {
      $derivative_id = LanguageInterface::TYPE_URL;
      $page_entity = $this->getPageEntity();
      $route = $this->pathMatcher->isFrontPage() ? '<front>' : '<current>';

      $current_language = $languageManager->getCurrentLanguage($derivative_id)->getId();
      $default_language = $languageManager->getDefaultLanguage()->getId();
      $links = $languageManager->getLanguageSwitchLinks($derivative_id, Url::fromRoute($route))->links;

      ksort($links);
      if (isset($links[$current_language])) {
        $links[$current_language]['url'] = Url::fromRoute('<current>');
        $links[$current_language]['selected'] = TRUE;
      }
      if (isset($links[$default_language])) {
        $links = [$default_language => $links[$default_language]] + $links;
      }

      foreach ($links as $link_key => $link_data) {
        $url = $page_entity ?
          $page_entity->toUrl('canonical', ['language' => $link_data['language']])->toString()
          : Url::fromRoute('<current>', [], ['language' => $link_data['language']]);
        $render_links[] = [
          'title' => $link_data['title'],
          'abbr' => mb_strtoupper($link_key),
          'url' => $url,
          'selected' => $link_data['selected'] ?? FALSE,
        ];
      }
    }
    return $render_links;
  }

  /**
   * Retrieves the current page entity.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface|bool
   *   The retrieved entity, or FALSE if none found.
   */
  protected function getPageEntity() {
    $params = $this->currentRouteMatch->getParameters()->all();

    foreach ($params as $param) {
      if ($param instanceof ContentEntityInterface) {
        return $param;
      }
    }
    return FALSE;
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
   * @param int $max_depth
   *   The max menu depth to render.
   *
   * @return array
   *   Rendered menu.
   */
  protected function buildMenu(string $menu_name, int $max_depth = 1) {
    $menu_parameters = new MenuTreeParameters();
    $menu_parameters->setMaxDepth($max_depth);
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
        $children = [];
        if (!empty($item['below'])) {
          foreach ($item['below'] as $child) {
            $children[] = [
              'title' => $child['title'],
              'url' => $child['url']->setAbsolute()->toString(),
            ];
          }
        }
        $menu_links[] = [
          'title' => $item['title'],
          'url' => $item['url']->setAbsolute()->toString(),
          'below' => $children,
        ];
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
    $form = $this->formBuilder->getForm('\Drupal\mars_search\Form\SearchOverlayForm');
    $form['actions']['submit']['#attributes']['class'][] = 'visually-hidden';
    $form['#input_form']['search']['#attributes']['class'][] = 'data-layer-search-form-input';

    return $this->renderer->render($form);
  }

}
