<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
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
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MenuLinkTreeInterface $menu_link_tree, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->menuLinkTree = $menu_link_tree;
    $this->menuStorage = $entity_type_manager->getStorage('menu');
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
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $conf = $this->getConfiguration();

    $build['#top_footer_menu'] = $this->buildMenu($conf['top_footer_menu']);
    $build['#footer_menu'] = $this->buildMenu($conf['footer_menu']);
    $build['#copyright'] = [
      '#type' => 'processed_text',
      '#text' => $conf['copyright']['value'] ?? '',
      '#format' => $conf['copyright']['format'] ?? 'plain_text',
    ];
    $build['#corporate_tout'] = $conf['corporate_tout'];

    if ($conf['social_links_toggle']) {
      // TODO add social links.
      $build['#social_links'] = NULL;
    }

    if ($conf['region_selector_toggle']) {
      // TODO add region selector.
      $build['#region_selector'] = NULL;
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
    return $this->menuLinkTree->build($tree);
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
    $form['footer_menu'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'menu',
      '#title' => $this->t('Footer menu'),
      '#required' => TRUE,
      '#default_value' => isset($config['footer_menu']) ? $this->menuStorage->load($this->configuration['footer_menu']) : NULL,
    ];
    $form['copyright'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Marketing & copyright messaging'),
      '#default_value' => $config['copyright']['value'] ?? '',
      '#format' => $config['copyright']['format'] ?? 'plain_text',
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
