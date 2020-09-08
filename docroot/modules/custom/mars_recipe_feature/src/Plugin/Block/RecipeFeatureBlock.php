<?php

namespace Drupal\mars_recipe_feature\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\Core\Render\RendererInterface;
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
 *     "node" = @ContextDefinition("entity:node", label =
 *   @Translation("Recipe"))
 *   }
 * )
 *
 * @package Drupal\mars_recipe_feature\Plugin\Block
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
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, ConfigFactoryInterface $config_factory, RendererInterface $renderer) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    $this->mediaStorage = $entity_type_manager->getStorage('media');
    $this->fileStorage = $entity_type_manager->getStorage('file');
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
    $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    global $base_url;

    $config = $this->getConfiguration();
    $theme_settings = $this->configFactory->get('emulsifymars.settings')->get();

    $node = $this->getContextValue('node');
    if (!$node || !$node->bundle() == 'recipes') {
      $node = $this->nodeStorage->load($this->config['recipe_id']);
    }

    if (!empty($node)) {
      // Get brand border.
      if (!empty($theme_settings['brand_borders']) && count($theme_settings['brand_borders']) > 0) {
        $border_file = $this->fileStorage->load($theme_settings['brand_borders'][0]);
        $build['#brand_borders'] = !empty($border_file) ? file_get_contents($base_url . $border_file->createFileUrl()) : '';
      }
      $recipe_media_set = [];
      if (!empty($config['recipe_media'])) {

        $recipe_media = $this->mediaStorage->load($config['recipe_media']);
        // Manual overriding for media.
        if ($recipe_media->bundle() == 'lighthouse_video') {
          $media_file_id = $recipe_media->get('field_media_video_file_1')->target_id;
          $media_file = $this->fileStorage->load($media_file_id);
          $media_file_url = !empty($media_file) ? $media_file->createFileUrl() : '';
          $recipe_media_set = [
            'video_url' => $media_file_url,
          ];
        }
        elseif ($recipe_media->bundle() == 'lighthouse_image') {
          $media_file_id = $recipe_media->get('field_media_image')->target_id;
          $media_file = $this->fileStorage->load($media_file_id);
          $media_file_url = !empty($media_file) ? $media_file->createFileUrl() : '';
          $format = '%s 375w, %s 768w, %s 1024w, %s 1440w';
          $recipe_media_set = [
            'srcset' => sprintf($format, $media_file_url, $media_file_url, $media_file_url, $media_file_url),
            'src' => $media_file_url,
          ];
        }
      }
      else {
        if (!$node->get('field_recipe_video')->isEmpty()) {
          // Do it if field_recipe_video field is not empty.
          // Check bundle.
          $videoBundle = $node->get('field_recipe_video')->referencedEntities()[0]->bundle();
          if ($videoBundle == 'video') {
            // $field_recipe_video = $node->get('field_recipe_video')->view('default');
            // $field_recipe_video_rendered = $this->renderer->render($field_recipe_video);
          }
          else {
            $media_file_id = $node->get('field_recipe_video')->target_id;
            $media_file = $this->fileStorage->load($media_file_id);
            $media_file_url = !empty($media_file) ? $media_file->createFileUrl() : '';
            $recipe_media_set = [
              'video_url' => $media_file_url,
            ];
          }
        }
        else {
          $media_file_id = $node->get('field_recipe_image')->target_id;
          $media_file = $this->fileStorage->load($media_file_id);
          $media_file_url = !empty($media_file) ? $media_file->createFileUrl() : '';
          $format = '%s 375w, %s 768w, %s 1024w, %s 1440w';
          $recipe_media_set = [
            'srcset' => sprintf($format, $media_file_url, $media_file_url, $media_file_url, $media_file_url),
            'src' => $media_file_url,
          ];
        }
      }
      $cooking_time = $node->get('field_recipe_cooking_time')->getValue();

      $config['cta']['url'] = $node->toUrl()->toString();

      $build = [
        '#eyebrow' => $config['eyebrow'],
        '#recipe_media' => $recipe_media_set,
        '#cooking_time' => $cooking_time,
        '#cta' => $config['cta'],
        '#theme' => 'recipe_feature_block',
      ];
      return $build;
    }
    else {
      return [];
    }

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

    $form['recipe_id_arg'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Take ID from argument'),
      '#default_value' => $config['recipe_id_arg'] ?? FALSE,
      '#attributes' => [
        'name' => 'settings[recipe_id_arg]',
      ],
    ];

    $form['recipe_id'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'node',
      '#title' => $this->t('Recipe ID'),
      '#default_value' => isset($config['recipe_id']) ? $this->nodeStorage->load($this->configuration['recipe_id']) : NULL,
      '#selection_settings' => [
        'target_bundles' => ['recipe'],
      ],
      '#states' => [
        'visible' => [
          ':input[name="settings[recipe_id_arg]"]' => ['checked' => FALSE],
        ],
      ],
    ];

    $form['recipe_media'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'media',
      '#title' => $this->t('Recipe Media'),
      '#default_value' => isset($config['recipe_media']) ? $this->mediaStorage->load($this->configuration['recipe_media']) : NULL,
      '#selection_settings' => ['target_bundles' => ['lighthouse_video', 'lighthouse_image']],
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
