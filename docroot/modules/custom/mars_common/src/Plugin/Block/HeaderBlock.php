<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\EntityMalformedException;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Url;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_common\MenuBuilder;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\mars_common\Traits\OverrideThemeTextColorTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\Config;

/**
 * Provides a Header block.
 *
 * @Block(
 *   id = "header_block",
 *   admin_label = @Translation("MARS: Header block")
 * )
 */
class HeaderBlock extends BlockBase implements ContainerFactoryPluginInterface {

  use OverrideThemeTextColorTrait;

  /**
   * The 'emulsifymars.settings' config.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config_color;

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
   * Menu storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $menuStorage;

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
   * Menu builder service.
   *
   * @var \Drupal\mars_common\MenuBuilder
   */
  private $menuBuilder;

  /**
   * Configuration object that stores site level labels.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private $labelConfig;

  /**
   * Minimum count of languages.
   */
  const MINIMUM_LANGUAGES = 1;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    Config $config_color,
    CurrentRouteMatch $current_route_match,
    PathMatcherInterface $path_matcher,
    MenuBuilder $menu_builder,
    EntityTypeManagerInterface $entity_type_manager,
    FormBuilderInterface $form_builder,
    LanguageHelper $language_helper,
    RendererInterface $renderer,
    ThemeConfiguratorParser $theme_configurator_parser,
    ImmutableConfig $label_config
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configColor = $config_color;
    $this->currentRouteMatch = $current_route_match;
    $this->pathMatcher = $path_matcher;
    $this->menuBuilder = $menu_builder;
    $this->menuStorage = $entity_type_manager->getStorage('menu');
    $this->formBuilder = $form_builder;
    $this->languageHelper = $language_helper;
    $this->renderer = $renderer;
    $this->themeConfiguratorParser = $theme_configurator_parser;
    $this->labelConfig = $label_config;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = $container->get('config.factory');
    $label_config = $config_factory->get('mars_common.site_labels');
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory')->getEditable('emulsifymars.settings'),
      $container->get('current_route_match'),
      $container->get('path.matcher'),
      $container->get('mars_common.menu_builder'),
      $container->get('entity_type.manager'),
      $container->get('form_builder'),
      $container->get('mars_common.language_helper'),
      $container->get('renderer'),
      $container->get('mars_common.theme_configurator_parser'),
      $label_config
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $config = $this->getConfiguration();
    $character_limit_config = \Drupal::config('mars_common.character_limit_page');

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
    $form['disable_mobile_menu_view'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Disable mobile hamburger menu view'),
      '#default_value' => $config['disable_mobile_menu_view'] ?? FALSE,
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
      '#maxlength' => !empty($character_limit_config->get('header_block_alert_banner_text')) ? $character_limit_config->get('header_block_alert_banner_text') : 100,
    ];
    $form['alert_banner']['alert_banner_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Alert Banner link'),
      '#description' => $this->t('Ex. http://mars.com, /products'),
      '#default_value' => $config['alert_banner']['alert_banner_url'] ?? '',
    ];
    $form['alert_banner']['override_color_scheme'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Override Alert Banner Color scheme'),
      '#default_value' => $config['alert_banner']['override_color_scheme'] ?? NULL,
    ];
    $form['alert_banner']['bg_color'] = [
      '#type' => 'jquery_colorpicker',
      '#title' => $this->t('Override banner background color'),
      '#default_value' => $config['alert_banner']['bg_color'] ?? NULL,
      '#states' => [
        'visible' => [
          [':input[name="settings[alert_banner][override_color_scheme]"]' => ['checked' => TRUE]],
        ],
      ],
    ];
    $form['alert_banner']['text_color'] = [
      '#type' => 'jquery_colorpicker',
      '#title' => $this->t('Override banner text color'),
      '#default_value' => $config['alert_banner']['text_color'] ?? NULL,
      '#states' => [
        'visible' => [
          [':input[name="settings[alert_banner][override_color_scheme]"]' => ['checked' => TRUE]],
        ],
      ],
    ];

    $this->buildOverrideColorElement($form, $config);

    $form['override_text_color']['сhoose_override_hover'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Сhoose an alternative color to override the on-hover'),
      '#default_value' => $config['override_text_color']['сhoose_override_hover'] ?? NULL,
    ];

    $form['override_text_color']['hover_color'] = [
      '#type' => 'jquery_colorpicker',
      '#title' => $this->t('Сhoose color B on-hover'),
      '#default_value' => $config['override_text_color']['hover_color'] ?? NULL,
      '#states' => [
        'visible' => [
          [':input[name="settings[override_text_color][сhoose_override_hover]"]' => ['checked' => TRUE]],
        ],
      ],
    ];

    $form['override_text_color']['override_mobile_color'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Сhoose an alternative color for mobile Close button and sub-menu and Desktop dropdown icon'),
      '#default_value' => $config['override_text_color']['override_mobile_color'] ?? NULL,
    ];

    $form['override_text_color']['mobile_main_menu_items_color'] = [
      '#type' => 'jquery_colorpicker',
      '#title' => $this->t('Override main menu items color'),
      '#default_value' => $config['override_text_color']['mobile_main_menu_items_color'] ?? NULL,
      '#states' => [
        'visible' => [
          [':input[name="settings[override_text_color][override_mobile_color]"]' => ['checked' => TRUE]],
        ],
      ],
    ];

    $form['override_text_color']['mobile_cross_hamburger'] = [
      '#type' => 'jquery_colorpicker',
      '#title' => $this->t('Override hamburger menu & cross icons color'),
      '#default_value' => $config['override_text_color']['mobile_cross_hamburger'] ?? NULL,
      '#states' => [
        'visible' => [
          [':input[name="settings[override_text_color][override_mobile_color]"]' => ['checked' => TRUE]],
        ],
      ],
    ];

    $form['override_text_color']['mobile_sub_menu_items_color'] = [
      '#type' => 'jquery_colorpicker',
      '#title' => $this->t('Override menu sub-items color including "Expand" icon color'),
      '#default_value' => $config['override_text_color']['mobile_sub_menu_items_color'] ?? NULL,
      '#states' => [
        'visible' => [
          [':input[name="settings[override_text_color][override_mobile_color]"]' => ['checked' => TRUE]],
        ],
      ],
    ];

    $form['override_text_color']['mobile_search_right_menu_section'] = [
      '#type' => 'jquery_colorpicker',
      '#title' => $this->t('Override search bar and right section menu elements color'),
      '#default_value' => $config['override_text_color']['mobile_search_right_menu_section'] ?? NULL,
      '#states' => [
        'visible' => [
          [':input[name="settings[override_text_color][override_mobile_color]"]' => ['checked' => TRUE]],
        ],
      ],
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

    $build['#logo'] = $this->themeConfiguratorParser->getLogoFromTheme();

    $theme_logo_alt = $this->themeConfiguratorParser->getLogoAltFromTheme();
    $build['#logo_alt'] = ($theme_logo_alt)
      ? $this->languageHelper->translate($theme_logo_alt)
      : $theme_logo_alt;

    $build['#alert_banner_text'] = $this->languageHelper->translate($config['alert_banner']['alert_banner_text']['value']);
    $build['#alert_banner_url'] = $this->languageHelper->translate($config['alert_banner']['alert_banner_url']);
    $build['#primary_menu'] = $this->menuBuilder->getMenuItemsArray($config['primary_menu'], 2);
    $build['#secondary_menu'] = $this->menuBuilder->getMenuItemsArray($config['secondary_menu']);
    $build['#disable_mobile_menu_view'] = $config['disable_mobile_menu_view'] ?? FALSE;

    $derivative_id = LanguageInterface::TYPE_URL;
    $current_language_id = $this->languageHelper->getLanguageManager()->getCurrentLanguage($derivative_id)->getId();
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

    $build['#search_close_label'] = $this->languageHelper->translate($this->labelConfig->get('header_search_overlay_close'));
    $build['#search_title'] = $this->languageHelper->translate($this->labelConfig->get('header_search_overlay'));

    $build['#brand_border'] = $this->themeConfiguratorParser->getBrandBorder();
    $build['#text_color_override'] = FALSE;
    if (!empty($config['override_text_color']['override_color'])) {
      $build['#text_color_override'] = static::$overrideColor;
    }

    $build['#hover_color'] = FALSE;
    if (!empty($config['override_text_color']['сhoose_override_hover']) &&
      !empty($config['override_text_color']['hover_color'])
    ) {
      $build['#hover_color'] = $config['override_text_color']['hover_color'];
    }

    $build['#override_mobile_menu_colors'] = FALSE;
    $build['#mobile_main_menu_items_color'] = FALSE;
    $build['#mobile_cross_hamburger'] = FALSE;
    $build['#mobile_sub_menu_items_color'] = FALSE;
    $build['#mobile_search_right_menu_section'] = FALSE;
    if (!empty($config['override_text_color']['override_mobile_color'])) {
      $build['#override_mobile_menu_colors'] = TRUE;
      $build['#mobile_main_menu_items_color'] = !empty($config['override_text_color']['mobile_main_menu_items_color']) ? $config['override_text_color']['mobile_main_menu_items_color'] : FALSE;
      $build['#mobile_cross_hamburger'] = !empty($config['override_text_color']['mobile_cross_hamburger']) ? $config['override_text_color']['mobile_cross_hamburger'] : FALSE;
      $build['#mobile_sub_menu_items_color'] = !empty($config['override_text_color']['mobile_sub_menu_items_color']) ? $config['override_text_color']['mobile_sub_menu_items_color'] : FALSE;
      $build['#mobile_search_right_menu_section'] = !empty($config['override_text_color']['mobile_search_right_menu_section']) ? $config['override_text_color']['mobile_search_right_menu_section'] : FALSE;
    }

    $build['#alert_banner_override_color'] = FALSE;
    $build['#alert_banner_bg_color'] = FALSE;
    $build['#alert_banner_text_color'] = FALSE;
    // Newsletter bg color set from alert banner bg color.
    $this->configColor->set('newsletter_bg_color', !empty($config['alert_banner']['bg_color']) ? $config['alert_banner']['bg_color'] : $this->configColor->get('color_b'));
    $this->configColor->save(TRUE);

    if (!empty($config['alert_banner']['override_color_scheme'])) {
      $build['#alert_banner_override_color'] = TRUE;
      $build['#alert_banner_bg_color'] = !empty($config['alert_banner']['bg_color']) ? $config['alert_banner']['bg_color'] : FALSE;
      $build['#alert_banner_text_color'] = !empty($config['alert_banner']['text_color']) ? $config['alert_banner']['text_color'] : FALSE;
    }

    CacheableMetadata::createFromRenderArray($build)
      ->addCacheableDependency($this->labelConfig)
      ->applyTo($build);

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

    if (count($languages) > static::MINIMUM_LANGUAGES) {
      $derivative_id = LanguageInterface::TYPE_URL;
      $route = $this->pathMatcher->isFrontPage() ? '<front>' : '<current>';

      $current_language = $languageManager->getCurrentLanguage($derivative_id)->getId();
      $default_language = $languageManager->getDefaultLanguage()->getId();
      $links = $languageManager->getLanguageSwitchLinks($derivative_id, Url::fromRoute($route))->links;

      ksort($links);
      if (isset($links[$current_language])) {
        $links[$current_language]['url'] = Url::fromRoute('<front>');
        $links[$current_language]['selected'] = TRUE;
      }
      if (isset($links[$default_language])) {
        $links = [$default_language => $links[$default_language]] + $links;
      }
      foreach ($links as $link_key => $link_data) {
        $url = Url::fromRoute('<front>', [], ['language' => $link_data['language']]);
        $render_links[] = [
          'title' => $this->languageHelper->translate($link_data['title']),
          'abbr' => mb_strtoupper($link_key),
          'url' => $url,
          'selected' => $link_data['selected'] ?? FALSE,
        ];
      }
    }
    return $render_links;
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

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['url.path']);
  }

}
