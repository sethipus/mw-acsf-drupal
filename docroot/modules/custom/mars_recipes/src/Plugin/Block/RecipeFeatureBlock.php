<?php

namespace Drupal\mars_recipes\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Class RecipeFeatureBlock.
 *
 * @Block(
 *   id = "recipe_feature_block",
 *   admin_label = @Translation("Recipe feature block"),
 *   category = @Translation("Recipe"),
 *   context_definitions = {
 *     "node" = @ContextDefinition("entity:node", label =
 *   @Translation("Recipe"))
 *   }
 * )
 *
 * @package Drupal\mars_recipes\Plugin\Block
 */
class RecipeFeatureBlock extends BlockBase implements ContextAwarePluginInterface, ContainerFactoryPluginInterface {

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
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    ThemeConfiguratorParser $themeConfiguratorParser
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->themeConfiguratorParser = $themeConfiguratorParser;
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    $this->mediaStorage = $entity_type_manager->getStorage('media');
    $this->fileStorage = $entity_type_manager->getStorage('file');
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
      $container->get('mars_common.theme_configurator_parser')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    $node = $this->getRecipe();
    if (empty($node)) {
      return [];
    }

    if (!empty($config['recipe_media'])) {
      // Assumption that only ligthouse_video and lighthouse_image allowed.
      $media_entity = $this->mediaStorage->load($config['recipe_media']);
      $field = ($media_entity->bundle() == 'lighthouse_video') ? 'field_media_video_file_1' : 'field_media_image';
      $isVideo = ($media_entity->bundle() == 'lighthouse_video') ? TRUE : FALSE;
    }
    else {
      if (!$node->get('field_recipe_video')->isEmpty()) {
        $videos = $node->get('field_recipe_video')->referencedEntities();
        $video = (is_array($videos) && count($videos) > 0) ? $videos[0] : NULL;

        if (!empty($video) && in_array($video->bundle(), ['lighthouse_video', 'video_file'])) {
          $media_entity = $this->getMediaEntity($node, 'field_recipe_video');
          $field = ($media_entity->bundle() == 'lighthouse_video') ? 'field_media_video_file_1' : 'field_media_video_file';
          $isVideo = TRUE;
        }
      }
      else {
        $media_entity = $this->getMediaEntity($node, 'field_recipe_image');
        $field = ($media_entity->bundle() == 'lighthouse_image') ? 'field_media_image' : 'image';
        $isVideo = FALSE;
      }
    }
    $recipe_media_set = (!empty($media_entity)) ? $this->prepareRecipeMediaSet($media_entity, $field, $isVideo) : [];

    $title = !empty($config['recipe_title']) ? $this->configuration['recipe_title'] : $node->label();
    // Get brand border path.
    $build['#brand_borders'] = $this->themeConfiguratorParser->getFileWithId('brand_borders', 'recipe-feature-border');
    $build['#brand_shape_class'] = $this->themeConfiguratorParser->getSettingValue('brand_border_style', 'repeat');
    $config['cta']['url'] = $node->toUrl('canonical', ['absolute' => FALSE])->toString();

    $build += [
      '#eyebrow' => $config['eyebrow'],
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
   * Load media entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $node
   *   The Recipe node.
   * @param string $field
   *   Field from which we uploading media.
   */
  protected function getMediaEntity(EntityInterface $node, string $field) {
    $media_entity_id = $node->get($field)->target_id;
    return $this->mediaStorage->load($media_entity_id);
  }

  /**
   * Get media parameters from node.
   *
   * @param \Drupal\Core\Entity\EntityInterface $media_entity
   *   Entity from which we should load date.
   * @param string $field
   *   The nade on the fiekd to be fetched.
   * @param bool $isVideo
   *   Set media as video or image.
   */
  protected function prepareRecipeMediaSet(EntityInterface $media_entity, string $field, bool $isVideo): array {
    $recipe_media_set = [];

    $media_file_id = $media_entity->get($field)->target_id;
    $media_file = $this->fileStorage->load($media_file_id);
    $media_file_url = !empty($media_file) ? $media_file->createFileUrl() : '';

    if ($isVideo) {
      $recipe_media_set = [
        'video_url' => $media_file_url,
      ];
    }
    else {
      $format = '%s 375w, %s 768w, %s 1024w, %s 1440w';
      $recipe_media_set = [
        'srcset' => sprintf($format, $media_file_url, $media_file_url, $media_file_url, $media_file_url),
        'src' => $media_file_url,
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

    $form['recipe_media'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'media',
      '#title' => $this->t('Recipe Media'),
      '#default_value' => isset($config['recipe_media']) ? $this->mediaStorage->load($this->configuration['recipe_media']) : NULL,
      '#selection_settings' => [
        'target_bundles' => [
          'lighthouse_video',
          'lighthouse_image',
        ],
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
  }

}
