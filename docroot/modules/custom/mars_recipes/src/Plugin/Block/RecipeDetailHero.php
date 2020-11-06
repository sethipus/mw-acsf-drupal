<?php

namespace Drupal\mars_recipes\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\mars_common\MediaHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\Core\Utility\Token;

/**
 * Class RecipeDetailHero.
 *
 * @Block(
 *   id = "recipe_detail_hero",
 *   admin_label = @Translation("Recipe detail hero"),
 *   category = @Translation("Recipe"),
 *   context_definitions = {
 *     "node" = @ContextDefinition("entity:node", label =
 *   @Translation("Recipe"))
 *   }
 * )
 *
 * @package Drupal\mars_recipes\Plugin\Block
 */
class RecipeDetailHero extends BlockBase implements ContextAwarePluginInterface, ContainerFactoryPluginInterface {

  /**
   * A view builder instance.
   *
   * @var \Drupal\Core\Entity\EntityViewBuilderInterface
   */
  protected $viewBuilder;

  /**
   * The configFactory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

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
   * The token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    ConfigFactoryInterface $config_factory,
    Token $token,
    ThemeConfiguratorParser $themeConfiguratorParser,
    MediaHelper $media_helper
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->viewBuilder = $entity_type_manager->getViewBuilder('node');
    $this->configFactory = $config_factory;
    $this->token = $token;
    $this->themeConfiguratorParser = $themeConfiguratorParser;
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
      $container->get('config.factory'),
      $container->get('token'),
      $container->get('mars_common.theme_configurator_parser'),
      $container->get('mars_common.media_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->getContextValue('node');

    $build = [
      '#label' => $node->label(),
      '#description' => $node->field_recipe_description->value,
      '#cooking_time' => $node->field_recipe_cooking_time->value,
      '#ingredients_number' => $node->field_recipe_ingredients_number->value,
      '#number_of_servings' => $node->field_recipe_number_of_servings->value,
      '#theme' => 'recipe_detail_hero_block',
    ];

    $media_id = $this->mediaHelper->getEntityMainMediaId($node);
    $image_arr = $this->mediaHelper->getMediaParametersById($media_id);
    if (!($image_arr['error'] ?? FALSE) && ($image_arr['src'] ?? FALSE)) {
      $build['#image'] = [
        'alt' => $image_arr['alt'] ?? '',
        'url' => $image_arr['src'] ?? '',
      ];
    }

    // Get brand border path.
    $build['#border'] = $this->themeConfiguratorParser->getFileWithId('brand_borders', 'recipe-hero-border');
    $build['#brand_shape_class'] = $this->themeConfiguratorParser->getSettingValue('brand_border_style', 'repeat');

    if (
      $node->hasField('field_recipe_video') &&
      !$node->get('field_recipe_video')->isEmpty()
    ) {
      $video_id = $node->get('field_recipe_video')->first()->target_id;
      $vide_params = $this->mediaHelper->getMediaParametersById($video_id);
      if (!($vide_params['error'] ?? FALSE) && ($vide_params['src'] ?? FALSE)) {
        $build['#video'] = $vide_params['src'];
      }
    }

    // Toggle to simplify unit test.
    $block_config = $this->getConfiguration();
    if (!array_key_exists('social_links_toggle', $block_config)) {
      $build['#social_links'] = $this->socialLinks();
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIf($this->getContextValue('node')->bundle() == 'recipe');
  }

  /**
   * Prepare social links data.
   *
   * @return array
   *   Rendered menu.
   */
  protected function socialLinks() {
    global $base_url;
    $node = $this->getContextValue('node');
    $social_menu_items = [];
    $social_medias = $this->configFactory->get('social_media.settings')
      ->get('social_media');

    foreach ($social_medias as $name => $social_media) {
      if ($social_media['enable'] != 1 || empty($social_media['api_url'])) {
        continue;
      }
      $social_menu_items[$name]['title'] = $social_media['text'];
      $social_menu_items[$name]['url'] = $this->token->replace($social_media['api_url'], ['node' => $node]);
      $social_menu_items[$name]['item_modifiers'] = $social_media['attributes'];
      $social_menu_items[$name]['item_attributes'] = ['data-clickName' => $social_media['text'], 'data-pageType' => 'recipe'];

      if (isset($social_media['default_img']) && $social_media['default_img']) {
        $icon_path = $base_url . '/' . drupal_get_path('module', 'social_media') . '/icons/';
        $social_menu_items[$name]['icon'] = [
          '#theme' => 'image',
          '#uri' => $icon_path . $name . '.svg',
          '#title' => $social_media['text'],
          '#alt' => $social_media['text'],
        ];
      }
      elseif (!empty($social_media['img'])) {
        $social_menu_items[$name]['icon'] = [
          '#theme' => 'image',
          '#uri' => $base_url . '/' . $social_media['img'],
          '#title' => $social_media['text'],
          '#alt' => $social_media['text'],
        ];
      }
    }

    return $social_menu_items;
  }

}
