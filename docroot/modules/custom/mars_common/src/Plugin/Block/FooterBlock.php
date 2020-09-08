<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Menu\MenuLinkTreeInterface;

/**
 * Footer Block.
 *
 * @Block(
 *   id = "footer_block",
 *   admin_label = @Translation("Footer block"),
 *   category = @Translation("Global elements"),
 * )
 */
class FooterBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
   * ThemeConfiguratorParser.
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
    MenuLinkTreeInterface $menu_link_tree,
    EntityTypeManagerInterface $entity_type_manager,
    ThemeConfiguratorParser $themeConfiguratorParser
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->menuLinkTree = $menu_link_tree;
    $this->menuStorage = $entity_type_manager->getStorage('menu');
    $this->themeConfiguratorParser = $themeConfiguratorParser;
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
      $container->get('mars_common.theme_configurator_parser')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $conf = $this->getConfiguration();
    $build['#logo'] = $this->themeConfiguratorParser->getLogoFromTheme();

    // Get brand border path.
    $build['#border'] = $this->themeConfiguratorParser->getFileWithId('brand_borders', 'footer-border');

    $build['#top_footer_menu'] = $this->buildMenu($conf['top_footer_menu']);
    $build['#legal_links'] = $this->buildMenu($conf['legal_links']);
    $build['#marketing'] = $conf['marketing']['value'];
    $build['#corporate_tout'] = $conf['corporate_tout']['title'];

    $build['#social_links'] = [];
    if ($conf['social_links_toggle']) {
      $build['#social_links'] = $this->themeConfiguratorParser->socialLinks();
    }

    if ($conf['region_selector_toggle']) {
      // TODO add region selector.
      $build['#region_selector'] = [
        ['title' => $this->t('North America: USA')],
        ['title' => $this->t('United Kingdom')],
      ];
    }

    $build['#theme'] = 'footer_block';
    return $build;
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
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $config = $this->getConfiguration();

    $form['top_footer_menu'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'menu',
      '#title' => $this->t('Top Footer menu'),
      '#required' => TRUE,
      '#default_value' => isset($config['top_footer_menu']) ? $this->menuStorage->load($this->configuration['top_footer_menu']) : NULL,
    ];
    $form['legal_links'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'menu',
      '#title' => $this->t('Legal links menu'),
      '#required' => TRUE,
      '#default_value' => isset($config['legal_links']) ? $this->menuStorage->load($this->configuration['legal_links']) : NULL,
    ];
    $form['marketing'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Marketing & Copyright Messaging'),
      '#default_value' => $config['marketing']['value'] ?? '',
      '#format' => $config['marketing']['format'] ?? 'plain_text',
    ];
    $form['corporate_tout'] = [
      '#type' => 'details',
      '#title' => $this->t('Mars corporate tout'),
      '#open' => TRUE,
    ];
    $form['corporate_tout']['url'] = [
      '#type' => 'url',
      '#title' => $this->t('Link URL'),
      '#maxlength' => 2048,
      '#required' => TRUE,
      '#default_value' => $config['corporate_tout']['url'] ?? '',
    ];
    $form['corporate_tout']['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Link Title'),
      '#maxlength' => 2048,
      '#required' => TRUE,
      '#default_value' => $config['corporate_tout']['title'] ?? '',
    ];
    $form['social_links_toggle'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display social links'),
      '#default_value' => $config['social_links_toggle'] ?? TRUE,
    ];
    $form['region_selector_toggle'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display region selector'),
      '#default_value' => $config['region_selector_toggle'] ?? TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfiguration($form_state->getValues());
  }

}
