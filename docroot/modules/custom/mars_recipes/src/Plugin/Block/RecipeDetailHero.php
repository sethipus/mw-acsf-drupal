<?php

namespace Drupal\mars_recipes\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\DependencyInjection\ClassResolverInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_media\MediaHelper;
use Drupal\mars_media\SVG\SVG;
use Drupal\mars_recipes\Form\RecipeEmailForm;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\Core\Utility\Token;
use Drupal\mars_common\Traits\SelectBackgroundColorTrait;

/**
 * Class RecipeDetailHero - recipe detail hero logic.
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
   * Node storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

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
   * @var \Drupal\mars_media\MediaHelper
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
   * The Form builder service.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  private $formBuilder;

  /**
   * The class resolver service.
   *
   * @var \Drupal\Core\DependencyInjection\ClassResolverInterface
   */
  private $classResolver;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  private $renderer;

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
    LanguageHelper $language_helper,
    FormBuilderInterface $form_builder,
    ClassResolverInterface $class_resolver,
    RendererInterface $renderer
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->viewBuilder = $entity_type_manager->getViewBuilder('node');
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    $this->configFactory = $config_factory;
    $this->token = $token;
    $this->themeConfiguratorParser = $themeConfiguratorParser;
    $this->mediaHelper = $media_helper;
    $this->languageHelper = $language_helper;
    $this->formBuilder = $form_builder;
    $this->classResolver = $class_resolver;
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
      $container->get('entity_type.manager'),
      $container->get('config.factory'),
      $container->get('token'),
      $container->get('mars_common.theme_configurator_parser'),
      $container->get('mars_media.media_helper'),
      $container->get('mars_common.language_helper'),
      $container->get('form_builder'),
      $container->get('class_resolver'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->getContextValue('node');

    // Load custom product.
    if (!empty($this->configuration['recipe'])) {
      $node = $this->nodeStorage->load($this->configuration['recipe']) ?? $node;
    }

    // Skip build process for non product entities.
    if (empty($node) || $node->bundle() != 'recipe') {
      return [];
    }

    $build = [
      '#label' => $node->label(),
      '#description' => $node->field_recipe_description->value,
      '#cooking_time' => $node->field_recipe_cooking_time->value,
      '#ingredients_number' => $node->field_recipe_ingredients_number->value,
      '#number_of_servings' => $node->field_recipe_number_of_servings->value,
      '#theme' => 'recipe_detail_hero_block',
    ];

    $build['#images'] = $this->mediaHelper->getResponsiveImagesFromEntity(
      $node,
      'field_recipe_image'
    );

    // Get brand border path.
    $build['#border'] = $this->themeConfiguratorParser->getBrandBorder();
    $build['#brand_shape'] = $this->themeConfiguratorParser->getBrandShapeWithoutFill();

    // Get label config values.
    $label_config = $this->configFactory->get('mars_common.site_labels');
    $build['#cooking_time_label'] = $this->languageHelper->translate($label_config->get('recipe_details_time'));
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
        $build['#hide_volume'] = !empty($this->configuration['hide_volume']) ? TRUE : FALSE;
      }
    }

    // Toggle to simplify unit test.
    $block_config = $this->getConfiguration();
    if (!array_key_exists('social_links_toggle', $block_config)) {
      $build['#social_links'] = $this->socialLinks($block_config);
    }

    $background_color = '';
    if (!empty($this->configuration['select_background_color']) && $this->configuration['select_background_color'] != 'default'
      && array_key_exists($this->configuration['select_background_color'], static::$colorVariables)
    ) {
      $background_color = static::$colorVariables[$this->configuration['select_background_color']];
    }

    $build['#select_background_color'] = $background_color;
    $build['#custom_background_color'] = $this->configuration['custom_background_color'] ?? NULL;
    $build['#use_custom_color'] = (bool) ($this->configuration['use_custom_color'] ?? 0);
    $build['#brand_shape_enabled'] = (bool) ($this->configuration['brand_shape_enabled'] ?? 0);

    $build['#email_recipe'] = $this->getEmailRecipeData($block_config, $node);

    if (isset($block_config['email_recipe']) && $block_config['email_recipe']) {
      $form_object = $this->classResolver
        ->getInstanceFromDefinition(RecipeEmailForm::class);
      $form_object->setRecipe($node);
      $form_object->setContextData($build['#email_recipe']);

      $recipe_form = $this->formBuilder->getForm($form_object);

      $build['#email_recipe_form'] = (string) $this->renderer
        ->render(
          $recipe_form
        );
      $build['#attached'] = (isset($build['#attached']))
        ? array_merge_recursive($build['#attached'], $recipe_form['#attached'])
        : $recipe_form['#attached'];
    }

    $cacheMetadata = CacheableMetadata::createFromRenderArray($build);
    $cacheMetadata->addCacheableDependency($label_config);
    $cacheMetadata->applyTo($build);

    return $build;
  }

  /**
   * Get default values for recipe email functionality.
   *
   * @return array
   *   Email recipe form default values.
   */
  public function getRecipeEmailDefault(): array {
    return [
      'email_hint' => $this->languageHelper->translate('Use email icon to send the Grocery and Recipe to email'),
      'email_overlay_title' => $this->languageHelper->translate('Share your Recipe'),
      'email_overlay_description' => $this->languageHelper->translate('Select options'),
      'checkboxes_container' => [
        'grocery_list' => $this->languageHelper->translate('Email a grocery list'),
        'email_recipe' => $this->languageHelper->translate('Email a recipe'),
      ],
      'email_address_hint' => $this->languageHelper->translate('Email address'),
      'error_message' => $this->languageHelper->translate('Please check your details'),
      'cta_title' => $this->languageHelper->translate('Submit'),
      'confirmation_message' => $this->languageHelper->translate('You are all set'),
      'captcha' => TRUE,
    ];
  }

  /**
   * Get recipe data for email form.
   *
   * @param array|null $block_config
   *   Block config.
   * @param \Drupal\node\NodeInterface $node
   *   Context recipe.
   */
  private function getEmailRecipeData(array $block_config, NodeInterface $node): ?array {
    return (isset($block_config['email_recipe']) && $block_config['email_recipe'])
      ? [
        'email_hint' => $this->languageHelper->translate($block_config['email_recipe_container']['email_hint']),
        'email_overlay_title' => $this->languageHelper->translate($block_config['email_recipe_container']['email_overlay_title']) ?? $this->getRecipeEmailDefault()['email_overlay_title'],
        'email_overlay_description' => $this->languageHelper->translate($block_config['email_recipe_container']['email_overlay_description']) ?? $this->getRecipeEmailDefault()['email_overlay_description'],
        'checkboxes_container' => [
          'grocery_list' => $this->languageHelper->translate($block_config['email_recipe_container']['checkboxes_container']['grocery_list']) ?? $this->getRecipeEmailDefault()['checkboxes_container']['grocery_list'],
          'email_recipe' => $this->languageHelper->translate($block_config['email_recipe_container']['checkboxes_container']['email_recipe']) ?? $this->getRecipeEmailDefault()['checkboxes_container']['email_recipe'],
        ],
        'email_address_hint' => $this->languageHelper->translate($block_config['email_recipe_container']['email_address_hint']) ?? $this->getRecipeEmailDefault()['email_address_hint'],
        'error_message' => $this->languageHelper->translate($block_config['email_recipe_container']['error_message']) ?? $this->getRecipeEmailDefault()['error_message'],
        'cta_title' => $this->languageHelper->translate($block_config['email_recipe_container']['cta_title']) ?? $this->getRecipeEmailDefault()['cta_title'],
        'confirmation_message' => $this->languageHelper->translate($block_config['email_recipe_container']['confirmation_message']) ?? $this->getRecipeEmailDefault()['confirmation_message'],
        'captcha' => $block_config['email_recipe_container']['captcha'] ?? $this->getRecipeEmailDefault()['captcha'],
      ]
      : NULL;
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
  protected function socialLinks(array $block_config) {
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
      $social_menu_items[$name]['item_modifiers'] = $this->transformAttributesToArray(
        $social_media['attributes']
        );
      $social_menu_items[$name]['weight'] = $social_media['weight'];

      if (isset($social_media['default_img']) && $social_media['default_img']) {
        $icon_path = $base_url . '/' . drupal_get_path('module', 'social_media') . '/icons/';
        try {
          $svg = SVG::createFromFile($icon_path . $name . '.svg', '');
          $social_menu_items[$name]['icon'] = $svg;
        }
        catch (\Exception $e) {
          $social_menu_items[$name]['icon'] = $this->t('The social icon is missing.');
        }
      }
      elseif (!empty($social_media['img'])) {
        try {
          $svg = SVG::createFromFile($base_url . '/' . $social_media['img'], '');
          $social_menu_items[$name]['icon'] = $svg;
        }
        catch (\Exception $e) {
          $social_menu_items[$name]['icon'] = $this->t('The social icon is missing.');
        }
      }
    }

    // Remove email button if email recipe is disable.
    if ((!isset($block_config['email_recipe']) || !$block_config['email_recipe']) &&
    isset($social_menu_items['email'])) {
      unset($social_menu_items['email']);
    }

    // Sort accordint to weight in configuration.
    usort($social_menu_items, function ($a, $b) {
      if ($a['weight'] == $b['weight']) {
        return 0;
      }
      return ($a['weight'] >= $b['weight'])
        ? 1
        : -1;
    });

    return $social_menu_items;
  }

  /**
   * Transform string attributes to array.
   *
   * @param array|null $attributes
   *   Attributes.
   *
   * @return array
   *   Array of attributes.
   */
  private function transformAttributesToArray(?string $attributes): array {
    $attributes = preg_split('/\n|\r\n?/', $attributes);
    $item_modifiers = [];
    foreach ($attributes as $attribute) {
      $attribute_value = explode('|', $attribute);
      $item_modifiers[$attribute_value[0]] = $attribute_value[1];
    }
    return $item_modifiers;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['recipe'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'node',
      '#title' => $this->t('Default recipe'),
      '#default_value' => isset($this->configuration['recipe']) ? $this->nodeStorage->load($this->configuration['recipe']) : NULL,
      '#selection_settings' => [
        'target_bundles' => ['recipe'],
      ],
    ];
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

    $form['hide_volume'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Hide Volume'),
      '#default_value' => $this->configuration['hide_volume'] ?? FALSE,
    ];

    $this->buildEmailRecipeForm($form);

    return $form;
  }

  /**
   * Email recipe feature part of form.
   *
   * @param array $form
   *   Form array.
   */
  public function buildEmailRecipeForm(array &$form) {

    $form['email_recipe'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Email recipe feature enabled'),
      '#default_value' => $this->configuration['email_recipe'] ?? FALSE,
    ];

    $form['email_recipe_container'] = [
      '#type' => 'details',
      '#title' => $this->t('Email recipe settings'),
      '#open' => TRUE,
      '#states' => [
        'visible' => [
          [':input[name="settings[email_recipe]"]' => ['checked' => TRUE]],
        ],
      ],
    ];

    $form['email_recipe_container']['email_hint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Hint'),
      '#maxlength' => 160,
      '#default_value' => $this->configuration['email_recipe_container']['email_hint'] ?? $this->getRecipeEmailDefault()['email_hint'],
      '#states' => [
        'visible' => [
          [':input[name="settings[email_recipe]"]' => ['checked' => TRUE]],
        ],
      ],
    ];

    $form['email_recipe_container']['email_overlay_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Overlay title'),
      '#maxlength' => 55,
      '#default_value' => $this->configuration['email_recipe_container']['email_overlay_title'] ?? $this->getRecipeEmailDefault()['email_overlay_title'],
      '#states' => [
        'visible' => [
          [':input[name="settings[email_recipe]"]' => ['checked' => TRUE]],
        ],
      ],
    ];

    $form['email_recipe_container']['email_overlay_description'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Overlay description'),
      '#maxlength' => 150,
      '#default_value' => $this->configuration['email_recipe_container']['email_overlay_description'] ?? $this->getRecipeEmailDefault()['email_overlay_description'],
      '#states' => [
        'visible' => [
          [':input[name="settings[email_recipe]"]' => ['checked' => TRUE]],
        ],
      ],
    ];

    $form['email_recipe_container']['checkboxes_container'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Checkboxes labels'),
      '#open' => TRUE,
      '#states' => [
        'visible' => [
          [':input[name="settings[email_recipe]"]' => ['checked' => TRUE]],
        ],
      ],
    ];

    $form['email_recipe_container']['checkboxes_container']['grocery_list'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Grocery list label'),
      '#maxlength' => 55,
      '#default_value' => $this->configuration['email_recipe_container']['checkboxes_container']['grocery_list'] ?? $this->getRecipeEmailDefault()['checkboxes_container']['grocery_list'],
      '#states' => [
        'visible' => [
          [':input[name="settings[email_recipe]"]' => ['checked' => TRUE]],
        ],
      ],
    ];

    $form['email_recipe_container']['checkboxes_container']['email_recipe'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email a recipe label'),
      '#maxlength' => 55,
      '#default_value' => $this->configuration['email_recipe_container']['checkboxes_container']['email_recipe'] ?? $this->getRecipeEmailDefault()['checkboxes_container']['email_recipe'],
      '#states' => [
        'visible' => [
          [':input[name="settings[email_recipe]"]' => ['checked' => TRUE]],
        ],
      ],
    ];

    $form['email_recipe_container']['email_address_hint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email address hint'),
      '#maxlength' => 35,
      '#required' => TRUE,
      '#default_value' => $this->configuration['email_recipe_container']['email_address_hint'] ?? $this->getRecipeEmailDefault()['email_address_hint'],
      '#states' => [
        'visible' => [
          [':input[name="settings[email_recipe]"]' => ['checked' => TRUE]],
        ],
        'required' => [
          [':input[name="settings[email_recipe]"]' => ['checked' => TRUE]],
        ],
      ],
    ];

    $form['email_recipe_container']['error_message'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Error message'),
      '#maxlength' => 100,
      '#required' => TRUE,
      '#default_value' => $this->configuration['email_recipe_container']['error_message'] ?? $this->getRecipeEmailDefault()['error_message'],
      '#states' => [
        'visible' => [
          [':input[name="settings[email_recipe]"]' => ['checked' => TRUE]],
        ],
        'required' => [
          [':input[name="settings[email_recipe]"]' => ['checked' => TRUE]],
        ],
      ],
    ];

    $form['email_recipe_container']['cta_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('CTA title'),
      '#maxlength' => 35,
      '#required' => TRUE,
      '#default_value' => $this->configuration['email_recipe_container']['cta_title'] ?? $this->getRecipeEmailDefault()['cta_title'],
      '#states' => [
        'visible' => [
          [':input[name="settings[email_recipe]"]' => ['checked' => TRUE]],
        ],
        'required' => [
          [':input[name="settings[email_recipe]"]' => ['checked' => TRUE]],
        ],
      ],
    ];

    $form['email_recipe_container']['confirmation_message'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Confirmation message'),
      '#maxlength' => 55,
      '#required' => TRUE,
      '#default_value' => $this->configuration['email_recipe_container']['confirmation_message'] ?? $this->getRecipeEmailDefault()['confirmation_message'],
      '#states' => [
        'visible' => [
          [':input[name="settings[email_recipe]"]' => ['checked' => TRUE]],
        ],
        'required' => [
          [':input[name="settings[email_recipe]"]' => ['checked' => TRUE]],
        ],
      ],
    ];
    $form['email_recipe_container']['captcha'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable captcha'),
      '#default_value' => $this->configuration['email_recipe_container']['captcha'] ?? TRUE,
      '#states' => [
        'visible' => [
          [':input[name="settings[email_recipe]"]' => ['checked' => TRUE]],
        ],
      ],
    ];
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
