<?php

namespace Drupal\mars_recipes\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\mars_common\MediaHelper;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\mars_lighthouse\Traits\EntityBrowserFormTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class RecipeFeatureBlock.
 *
 * @Block(
 *   id = "recipe_feature_block",
 *   admin_label = @Translation("Recipe feature block"),
 *   category = @Translation("Recipe"),
 *   context_definitions = {
 *     "node" = @ContextDefinition("entity:node", label = @Translation("Recipe"))
 *   }
 * )
 *
 * @package Drupal\mars_recipes\Plugin\Block
 */
class RecipeFeatureBlock extends BlockBase implements ContextAwarePluginInterface, ContainerFactoryPluginInterface {

  use EntityBrowserFormTrait;

  /**
   * NodeStorage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * Media storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $mediaStorage;

  /**
   * File storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $fileStorage;

  /**
   * ThemeConfiguratorParser.
   *
   * @var \Drupal\mars_common\ThemeConfiguratorParser
   */
  protected $themeConfiguratorParser;

  /**
   * Lighthouse entity browser image id.
   */
  const LIGHTHOUSE_ENTITY_BROWSER_IMAGE_ID = 'lighthouse_browser';

  /**
   * Lighthouse entity browser video id.
   */
  const LIGHTHOUSE_ENTITY_BROWSER_VIDEO_ID = 'lighthouse_video_browser';

  /**
   * Key option video.
   */
  const KEY_OPTION_VIDEO = 'video';

  /**
   * Key option image.
   */
  const KEY_OPTION_IMAGE = 'image';

  /**
   * From recipe page.
   */
  const KEY_OPTION_FROM_RECIPE_PAGE = 'from_recipe_page';

  /**
   * Recipe options.
   *
   * @var array
   */
  protected $options = [
    self::KEY_OPTION_FROM_RECIPE_PAGE => 'Media from recipe page',
    self::KEY_OPTION_VIDEO => 'Video',
    self::KEY_OPTION_IMAGE => 'Image',
  ];

  /**
   * Mars Media Helper service.
   *
   * @var \Drupal\mars_common\MediaHelper
   */
  protected $mediaHelper;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    ThemeConfiguratorParser $themeConfiguratorParser,
    MediaHelper $media_helper
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->themeConfiguratorParser = $themeConfiguratorParser;
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    $this->mediaStorage = $entity_type_manager->getStorage('media');
    $this->fileStorage = $entity_type_manager->getStorage('file');
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
      $container->get('mars_common.theme_configurator_parser'),
      $container->get('mars_common.media_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $media_id = NULL;
    $config = $this->getConfiguration();
    $node = $this->getRecipe();
    if (empty($node)) {
      return [];
    }

    if (
      !empty($config['recipe_media_image']) && $config['recipe_options'] == self::KEY_OPTION_IMAGE ||
      !empty($config['recipe_media_video']) && $config['recipe_options'] == self::KEY_OPTION_VIDEO
    ) {
      if ($config['recipe_options'] == self::KEY_OPTION_IMAGE) {
        $media_id = $this->mediaHelper->getIdFromEntityBrowserSelectValue($this->configuration['recipe_media_image']);
      }
      elseif ($config['recipe_options'] == self::KEY_OPTION_VIDEO) {
        $media_id = $this->mediaHelper->getIdFromEntityBrowserSelectValue($this->configuration['recipe_media_video']);
      }
    }
    else {
      if (!$node->get('field_recipe_video')->isEmpty()) {
        $media_id = $node->get('field_recipe_video')->first()->target_id;
      }
      else {
        $media_id = $node->get('field_recipe_image')->first()->target_id;
      }
    }

    if ($media_id) {
      $recipe_media_set = $this->prepareRecipeMediaSet($media_id);
    }
    else {
      $recipe_media_set = [];
    }

    $title = !empty($config['recipe_title']) ? $this->configuration['recipe_title'] : $node->label();
    // Get brand border path.
    $build['#brand_borders'] = $this->themeConfiguratorParser->getBrandBorder();
    $build['#graphic_divider'] = $this->themeConfiguratorParser->getGraphicDivider();
    $config['cta']['url'] = $node->toUrl('canonical', ['absolute' => FALSE])->toString();

    $build += [
      '#block_title' => $config['block_title'] ?? '',
      '#eyebrow' => $config['eyebrow'] ?? '',
      '#title' => $title,
      '#recipe_media' => $recipe_media_set,
      '#cooking_time' => $node->field_recipe_cooking_time->value . $node->get('field_recipe_cooking_time')->getSettings()['suffix'],
      '#cta' => $config['cta'],
      '#theme' => 'recipe_feature_block',
    ];
    return $build;
  }

  /**
   * Getting recipe node.
   *
   * @return \Drupal\node\Entity\Node
   *   The recipe node.
   */
  protected function getRecipe() {
    $node = $this->getContextValue('node');
    $config = $this->getConfiguration();
    if (empty($node) || $node->bundle() != 'recipe') {
      $node = $this->nodeStorage->load($config['recipe_id']);
    }
    return $node;
  }

  /**
   * Get media parameters from node.
   *
   * @param string $media_id
   *   Media id that should be used to generate media set.
   *
   * @return array
   *   Media set array for the given media id.
   */
  protected function prepareRecipeMediaSet(string $media_id): array {
    $media_params = $this->mediaHelper->getMediaParametersById($media_id);

    if (isset($media_params['error'])) {
      return [];
    }

    $is_video = isset($media_params['video']) && $media_params['video'];
    $media_file_url = $media_params['src'];

    if ($is_video) {
      $recipe_media_set = [
        'video_url' => $media_file_url,
      ];
    }
    else {
      $format = '%s 375w, %s 768w, %s 1024w, %s 1440w';
      $recipe_media_set = [
        'srcset' => sprintf($format, $media_file_url, $media_file_url, $media_file_url, $media_file_url),
        'src' => $media_file_url,
        'alt' => $media_params['alt'] ?? '',
      ];
    }
    return $recipe_media_set;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $config = $this->getConfiguration();

    $form['block_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Featured Recipe'),
      '#maxlength' => 55,
      '#required' => TRUE,
      '#default_value' => $config['block_title'] ?? $this->t('Featured Recipe'),
    ];

    $form['eyebrow'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Eyebrow'),
      '#maxlength' => 15,
      '#required' => TRUE,
      '#default_value' => $config['eyebrow'] ?? '',
    ];

    $form['recipe_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Recipe title'),
      '#description' => $this->t('Recipe title is by default pulled from the selected recipe. In case you need to change the title, please add Recipe title in the field above to override it.'),
      '#maxlength' => 60,
      '#default_value' => $config['recipe_title'] ?? '',
    ];

    $form['recipe_id'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'node',
      '#title' => $this->t('Recipe ID'),
      '#default_value' => isset($config['recipe_id']) ? $this->nodeStorage->load($this->configuration['recipe_id']) : NULL,
      '#selection_settings' => [
        'target_bundles' => ['recipe'],
      ],
    ];

    $form['recipe_options'] = [
      '#type' => 'radios',
      '#title' => $this->t('Recipe media type:'),
      '#options' => $this->options,
      '#default_value' => isset($config['recipe_options']) ? $config['recipe_options'] : NULL,
    ];

    $image_default = isset($config['recipe_media_image']) ? $config['recipe_media_image'] : NULL;
    // Entity Browser element for background image.
    $form['recipe_media_image'] = $this->getEntityBrowserForm(self::LIGHTHOUSE_ENTITY_BROWSER_IMAGE_ID, $image_default, 1, 'thumbnail');
    // Convert the wrapping container to a details element.
    $form['recipe_media_image']['#type'] = 'details';
    $form['recipe_media_image']['#title'] = $this->t('Image');
    $form['recipe_media_image']['#open'] = TRUE;
    $form['recipe_media_image']['#states'] = [
      'visible' => [
        ':input[name="settings[recipe_options]"]' => ['value' => self::KEY_OPTION_IMAGE],
      ],
    ];

    $video_default = isset($config['recipe_media_video']) ? $config['recipe_media_video'] : NULL;
    // Entity Browser element for video.
    $form['recipe_media_video'] = $this->getEntityBrowserForm(self::LIGHTHOUSE_ENTITY_BROWSER_VIDEO_ID, $video_default, 1);
    // Convert the wrapping container to a details element.
    $form['recipe_media_video']['#type'] = 'details';
    $form['recipe_media_video']['#title'] = $this->t('Video');
    $form['recipe_media_video']['#open'] = TRUE;
    $form['recipe_media_video']['#states'] = [
      'visible' => [
        ':input[name="settings[recipe_options]"]' => ['value' => self::KEY_OPTION_VIDEO],
      ],
    ];

    $form['cta'] = [
      '#type' => 'details',
      '#title' => $this->t('CTA'),
      '#open' => TRUE,
    ];
    $form['cta']['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('CTA Link Title'),
      '#maxlength' => 15,
      '#required' => TRUE,
      '#default_value' => $config['cta']['title'] ?? $this->t('Get started'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->setConfiguration($values);
    $this->configuration['recipe_media_image'] = $this->getEntityBrowserValue($form_state, 'recipe_media_image');
    $this->configuration['recipe_media_video'] = $this->getEntityBrowserValue($form_state, 'recipe_media_video');
  }

}
