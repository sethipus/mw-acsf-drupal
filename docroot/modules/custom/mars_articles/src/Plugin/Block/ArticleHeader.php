<?php

namespace Drupal\mars_articles\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_common\MediaHelper;
use Drupal\mars_common\Traits\OverrideThemeTextColorTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Utility\Token;

/**
 * Class ArticleHeader.
 *
 * @Block(
 *   id = "article_header",
 *   admin_label = @Translation("MARS: Article header"),
 *   category = @Translation("Article"),
 *   context_definitions = {
 *     "node" = @ContextDefinition("entity:node", label =
 *   @Translation("Article"))
 *   }
 * )
 *
 * @package Drupal\mars_articles\Plugin\Block
 */
class ArticleHeader extends BlockBase implements ContextAwarePluginInterface, ContainerFactoryPluginInterface {

  use OverrideThemeTextColorTrait;

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
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * ThemeConfiguratorParser.
   *
   * @var \Drupal\mars_common\ThemeConfiguratorParser
   */
  protected $themeConfiguratorParser;

  /**
   * The configFactory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Language helper service.
   *
   * @var \Drupal\mars_common\LanguageHelper
   */
  private $languageHelper;

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
    DateFormatterInterface $date_formatter,
    Token $token,
    ThemeConfiguratorParser $themeConfiguratorParser,
    ConfigFactoryInterface $config_factory,
    LanguageHelper $language_helper,
    MediaHelper $media_helper
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->viewBuilder = $entity_type_manager->getViewBuilder('node');
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    $this->dateFormatter = $date_formatter;
    $this->token = $token;
    $this->themeConfiguratorParser = $themeConfiguratorParser;
    $this->configFactory = $config_factory;
    $this->languageHelper = $language_helper;
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
      $container->get('date.formatter'),
      $container->get('token'),
      $container->get('mars_common.theme_configurator_parser'),
      $container->get('config.factory'),
      $container->get('mars_common.language_helper'),
      $container->get('mars_common.media_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $node = $this->getContextValue('node');

    if (!$node || !$node->bundle() == 'article') {
      $node = $this->nodeStorage->load($this->configuration['article']);
    }

    $label_config = $this->configFactory->get('mars_common.site_labels');
    $published_label = $label_config->get('article_published');
    $share_text = $label_config->get('article_recipe_share');

    $build = [
      '#label' => $node->label(),
      '#eyebrow' => $this->languageHelper->translate($this->configuration['eyebrow']),
      '#share_text' => $this->languageHelper->translate($share_text),
      '#publication_date' => $node->isPublished() ? $this->languageHelper->translate($published_label) . ' ' . $this->dateFormatter->format($node->published_at->value, 'article_header') : NULL,
    ];

    $media_id = $this->mediaHelper->getEntityMainMediaId($node);
    $image_arr = $this->mediaHelper->getMediaParametersById($media_id);
    if (!($image_arr['error'] ?? FALSE) && ($image_arr['src'] ?? FALSE)) {
      $build['#image'] = [
        'alt' => $image_arr['alt'] ?? '',
        'url' => $image_arr['src'] ?? '',
      ];
      $build['#theme'] = 'article_header_block_image';
    }
    else {
      $build['#theme'] = 'article_header_block_no_image';
      $build['#brand_shape'] = $this->themeConfiguratorParser->getBrandShapeWithoutFill();
    }

    // Get brand border path.
    $build['#brand_borders'] = $this->themeConfiguratorParser->getBrandBorder();
    $build['#social_links'] = $this->socialLinks();

    $build['#text_color_override'] = FALSE;
    if (!empty($this->configuration['override_text_color']['override_color'])) {
      $build['#text_color_override'] = self::$overrideColor;
    }

    $cacheMetadata = CacheableMetadata::createFromRenderArray($build);
    $cacheMetadata->addCacheableDependency($label_config);
    $cacheMetadata->applyTo($build);

    return $build;
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
    $form['article'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'node',
      '#title' => $this->t('Default article'),
      '#default_value' => isset($config['article']) ? $this->nodeStorage->load($this->configuration['article']) : NULL,
      '#selection_settings' => [
        'target_bundles' => ['article'],
      ],
    ];

    $this->buildOverrideColorElement($form, $config);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfiguration($form_state->cleanValues()->getValues());
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

}
