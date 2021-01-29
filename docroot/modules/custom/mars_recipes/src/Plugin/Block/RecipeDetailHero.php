<?php

namespace Drupal\mars_recipes\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_common\MediaHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\Core\Utility\Token;
use Drupal\mars_common\Traits\SelectBackgroundColorTrait;

/**
 * Class RecipeDetailHero.
 *
 * @Block(
 *   id = "recipe_detail_hero",
 *   admin_label = @Translation("MARS: Recipe detail hero"),
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

  use SelectBackgroundColorTrait;

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
   * The language helper service.
   *
   * @var \Drupal\mars_common\LanguageHelper
   */
  private $languageHelper;

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
    MediaHelper $media_helper,
    LanguageHelper $language_helper
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->viewBuilder = $entity_type_manager->getViewBuilder('node');
    $this->configFactory = $config_factory;
    $this->token = $token;
    $this->themeConfiguratorParser = $themeConfiguratorParser;
    $this->mediaHelper = $media_helper;
    $this->languageHelper = $language_helper;
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
      $container->get('mars_common.media_helper'),
      $container->get('mars_common.language_helper')
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
    $build['#border'] = $this->themeConfiguratorParser->getBrandBorder();
    $build['#brand_shape'] = $this->themeConfiguratorParser->getBrandShapeWithoutFill();

    // Get label config values.
    $label_config = $this->configFactory->get('mars_common.site_labels');
    $build['#cooking_time_label'] = $this->languageHelper->translate($label_config->get('recipe_details_time'));
    $build['#cooking_time_measure'] = $this->languageHelper->translate($label_config->get('recipe_details_time_measurement'));
    $build['#ingredients_label'] = $this->languageHelper->translate($label_config->get('recipe_details_ingredients'));
    $build['#ingredients_measure'] = $this->languageHelper->translate($label_config->get('recipe_details_ingredients_measurement'));
    $build['#number_of_servings_label'] = $this->languageHelper->translate($label_config->get('recipe_details_servings'));
    $build['#number_of_servings_measure'] = $this->languageHelper->translate($label_config->get('recipe_details_servings_measurement'));
    $build['#social_text'] = $this->languageHelper->translate($label_config->get('article_recipe_share'));

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

    $background_color = '';
    if (!empty($this->configuration['select_background_color'] && $this->configuration['select_background_color'] != 'default') &&
      array_key_exists($this->configuration['select_background_color'], static::$colorVariables)
    ) {
      $background_color = static::$colorVariables[$this->configuration['select_background_color']];
    }

    $build['#select_background_color'] = $background_color;
    $build['#custom_background_color'] = $this->configuration['custom_background_color'] ?? NULL;
    $build['#use_custom_color'] = (bool) ($this->configuration['use_custom_color'] ?? 0);
    $build['#brand_shape_enabled'] = (bool) ($this->configuration['brand_shape_enabled'] ?? 0);

    $cacheMetadata = CacheableMetadata::createFromRenderArray($build);
    $cacheMetadata->addCacheableDependency($label_config);
    $cacheMetadata->applyTo($build);

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

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['use_custom_color'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use custom color'),
      '#default_value' => $this->configuration['use_custom_color'] ?? FALSE,
    ];
    $form['custom_background_color'] = [
      '#type' => 'jquery_colorpicker',
      '#title' => $this->t('Background Color Override'),
      '#default_value' => $this->configuration['custom_background_color'] ?? '',
    ];

    // Add select background color.
    $this->buildSelectBackground($form);

    $form['brand_shape_enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Brand shape enabled'),
      '#default_value' => $this->configuration['brand_shape_enabled'] ?? FALSE,
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

}
