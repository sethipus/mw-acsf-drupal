<?php

namespace Drupal\mars_banners\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\mars_common\MediaHelper;
use Drupal\mars_lighthouse\Traits\EntityBrowserFormTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Homepage hero Block.
 *
 * @Block(
 *   id = "homepage_hero_block",
 *   admin_label = @Translation("Homepage Hero block"),
 *   category = @Translation("Global elements"),
 * )
 */
class HomepageHeroBlock extends BlockBase implements ContainerFactoryPluginInterface {

  use EntityBrowserFormTrait;

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
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * File storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $fileStorage;

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
    ConfigFactoryInterface $config_factory,
    MediaHelper $media_helper
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->fileStorage = $entity_type_manager->getStorage('file');
    $this->config = $config_factory;
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
      $container->get('mars_common.media_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    $theme_settings = $this->config->get('emulsifymars.settings')->get();
    $brand_shape = $theme_settings['brand_shape'];
    $default_fid = reset($brand_shape);

    $build['#label'] = $config['label'];
    $build['#eyebrow'] = $config['eyebrow'];
    $build['#title_url'] = $config['title']['url'];
    $build['#title_label'] = $config['title']['label'];
    $build['#cta_url'] = ['href' => $config['cta']['url']];
    $build['#cta_title'] = $config['cta']['title'];
    $build['#block_type'] = $config['block_type'];

    if ($config['block_type'] == self::KEY_OPTION_IMAGE && !empty($config['background_image'])) {
      $media_id = $this->mediaHelper->getIdFromEntityBrowserSelectValue($config['background_image']);
      $media_params = $this->mediaHelper->getMediaParametersById($media_id);
      if (!isset($media_params['error'])) {
        $build['#background_image'] = file_create_url($media_params['src']);
      }
    }
    elseif ($config['block_type'] == self::KEY_OPTION_VIDEO && !empty($config['background_video'])) {
      $media_id = $this->mediaHelper->getIdFromEntityBrowserSelectValue($config['background_video']);
      $media_params = $this->mediaHelper->getMediaParametersById($media_id);
      if (!isset($media_params['error'])) {
        $build['#background_video'] = file_create_url($media_params['src']);
      }
    }
    else {
      $fid = $default_fid;
      $file = !empty($fid) ? $this->fileStorage->load($fid) : '';
      $build['#background_image'] = !empty($file) ? $file->createFileUrl() : '';
    }

    if (!empty($config['card'])) {
      foreach ($config['card'] as $key => $card) {
        $build['#blocks'][$key]['eyebrow'] = $card['eyebrow'];
        $build['#blocks'][$key]['title_label'] = $card['title']['label'];
        $build['#blocks'][$key]['title_href'] = $card['title']['url'];
        $fid = reset($card['foreground_image']);
        if (!empty($fid)) {
          $file = $this->fileStorage->load($fid);
        }
        $file_url = !empty($file) ? $file->createFileUrl() : '';
        $format = '%s 375w, %s 768w, %s 1024w, %s 1440w';
        $build['#blocks'][$key]['image'][] = [
          'srcset' => sprintf($format, $file_url, $file_url, $file_url, $file_url),
          'src' => $file_url,
          'class' => 'block1-small',
        ];
        $build['#blocks'][$key]['cta'][] = [
          'title' => $card['cta']['title'],
          'link_attributes' => [
            [
              'href' => $card['cta']['url'],
            ],
          ],
        ];
      }
    }

    $build['#theme'] = 'homepage_hero_block';
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
    $form['title'] = [
      '#type' => 'details',
      '#title' => $this->t('Title'),
      '#open' => TRUE,
    ];
    $form['title']['url'] = [
      '#type' => 'url',
      '#title' => $this->t('Title Link URL'),
      '#maxlength' => 2048,
      '#required' => TRUE,
      '#default_value' => $config['title']['url'] ?? '',
    ];
    $form['title']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title label'),
      '#maxlength' => 50,
      '#required' => TRUE,
      '#default_value' => $config['title']['label'] ?? '',
    ];
    $form['cta'] = [
      '#type' => 'details',
      '#title' => $this->t('CTA'),
      '#open' => TRUE,
    ];
    $form['cta']['url'] = [
      '#type' => 'url',
      '#title' => $this->t('CTA Link URL'),
      '#maxlength' => 2048,
      '#required' => TRUE,
      '#default_value' => $config['cta']['url'] ?? '',
    ];
    $form['cta']['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('CTA Link Title'),
      '#maxlength' => 15,
      '#required' => TRUE,
      '#default_value' => $config['cta']['title'] ?? 'Explore',
    ];
    $form['block_type'] = [
      '#title' => $this->t('Choose block type'),
      '#type' => 'select',
      '#options' => [
        'default' => $this->t('Default'),
        'image' => $this->t('Image'),
        'video' => $this->t('Video'),
      ],
      '#default_value' => $config['block_type'] ?? 'default',
    ];

    $image_default = isset($config['background_image']) ? $config['background_image'] : NULL;
    // Entity Browser element for background image.
    $form['background_image'] = $this->getEntityBrowserForm(self::LIGHTHOUSE_ENTITY_BROWSER_IMAGE_ID, $image_default, 1, 'thumbnail');
    // Convert the wrapping container to a details element.
    $form['background_image']['#type'] = 'details';
    $form['background_image']['#title'] = $this->t('Background Image');
    $form['background_image']['#open'] = TRUE;
    $form['background_image']['#states'] = [
      'visible' => [
        ':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_IMAGE],
      ],
      'required' => [
        ':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_IMAGE],
      ],
    ];

    $video_default = isset($config['background_video']) ? $config['background_video'] : NULL;
    // Entity Browser element for video.
    $form['background_video'] = $this->getEntityBrowserForm(self::LIGHTHOUSE_ENTITY_BROWSER_VIDEO_ID, $video_default, 1);
    // Convert the wrapping container to a details element.
    $form['background_video']['#type'] = 'details';
    $form['background_video']['#title'] = $this->t('Background Video');
    $form['background_video']['#open'] = TRUE;
    $form['background_video']['#states'] = [
      'visible' => [
        ':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_VIDEO],
      ],
      'required' => [
        ':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_VIDEO],
      ],
    ];

    $form['card'] = [
      '#type' => 'fieldset',
      '#tree' => TRUE,
      '#title' => $this->t('Setup 3UP variant'),
      '#description' => $this->t('2 additional cards for hero block on homepage.'),
      '#prefix' => '<div id="cards-wrapper">',
      '#suffix' => '</div>',
    ];

    $card_settings = !empty($config['card']) ? $config['card'] : '';
    $card_storage = $form_state->get('card_storage');
    if (!isset($card_storage)) {
      if (!empty($card_settings)) {
        $card_storage = array_keys($card_settings);
      }
      else {
        $card_storage = [];
      }
      $form_state->set('card_storage', $card_storage);
    }

    $triggered = $form_state->getTriggeringElement();
    if (isset($triggered['#parents'][3]) && $triggered['#parents'][3] == 'remove_card') {
      $card_storage = $form_state->get('card_storage');
      $id = $triggered['#parents'][2];
      unset($card_storage[$id]);
    }

    foreach ($card_storage as $key => $value) {
      $form['card'][$key] = [
        '#type' => 'details',
        '#title' => $this->t('Product card'),
        '#open' => TRUE,
      ];
      $form['card'][$key]['eyebrow'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Card Eyebrow'),
        '#maxlength' => 15,
        '#default_value' => $config['card'][$key]['eyebrow'] ?? '',
      ];
      $form['card'][$key]['title']['label'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Card Title label'),
        '#maxlength' => 45,
        '#default_value' => $config['card'][$key]['title']['label'] ?? '',
      ];
      $form['card'][$key]['title']['url'] = [
        '#type' => 'url',
        '#title' => $this->t('Card Title Link URL'),
        '#maxlength' => 2048,
        '#default_value' => $config['card'][$key]['title']['url'] ?? '',
      ];
      $form['card'][$key]['cta']['title'] = [
        '#type' => 'textfield',
        '#title' => $this->t('CTA Link Title'),
        '#maxlength' => 15,
        '#default_value' => $config['card'][$key]['cta']['title'] ?? 'Explore',
      ];
      $form['card'][$key]['cta']['url'] = [
        '#type' => 'url',
        '#title' => $this->t('CTA Link URL'),
        '#maxlength' => 2048,
        '#default_value' => $config['card'][$key]['cta']['url'] ?? '',
      ];
      $form['card'][$key]['foreground_image'] = [
        '#title'           => $this->t('Foreground Image'),
        '#type'            => 'managed_file',
        '#process'         => [
          ['\Drupal\file\Element\ManagedFile', 'processManagedFile'],
          'mars_banners_process_image_widget',
        ],
        '#upload_validators' => [
          'file_validate_extensions' => ['gif png jpg jpeg svg'],
        ],
        '#theme' => 'image_widget',
        '#upload_location' => 'public://',
        '#preview_image_style' => 'medium',
        '#default_value'       => $config['card'][$key]['foreground_image'] ?? '',
      ];
      $form['card'][$key]['remove_card'] = [
        '#type'  => 'button',
        '#name' => 'card_' . $key,
        '#value' => $this->t('Remove card'),
        '#ajax'  => [
          'callback' => [$this, 'ajaxRemoveCardCallback'],
          'wrapper' => 'cards-wrapper',
        ],
      ];
    }
    if (count($card_storage) < 2) {
      $form['card']['add_card'] = [
        '#type' => 'submit',
        '#value' => $this->t('Add card'),
        '#ajax' => [
          'callback' => [$this, 'ajaxAddCardCallback'],
          'wrapper' => 'cards-wrapper',
        ],
        '#limit_validation_errors' => [],
        '#submit' => [[$this, 'addCardSubmited']],
      ];
    }

    return $form;
  }

  /**
   * Add new card link callback.
   *
   * @param array $form
   *   Theme settings form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Theme settings form state.
   *
   * @return array
   *   Card container of configuration settings.
   */
  public function ajaxAddCardCallback(array $form, FormStateInterface $form_state) {
    return $form['settings']['card'];
  }

  /**
   * Add remove card callback.
   *
   * @param array $form
   *   Theme settings form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Theme settings form state.
   *
   * @return array
   *   Card container of configuration settings.
   */
  public function ajaxRemoveCardCallback(array $form, FormStateInterface $form_state) {
    return $form['settings']['card'];
  }

  /**
   * Custom submit card configuration settings form.
   *
   * @param array $form
   *   Theme settings form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Theme settings form state.
   */
  public function addCardSubmited(array $form, FormStateInterface $form_state) {
    $storage = $form_state->get('card_storage');
    array_push($storage, 1);
    $form_state->set('card_storage', $storage);
    $form_state->setRebuild(TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    unset($values['card']['add_card']);
    $this->setConfiguration($values);
    $this->configuration['background_image'] = $this->getEntityBrowserValue($form_state, 'background_image');
    $this->configuration['background_video'] = $this->getEntityBrowserValue($form_state, 'background_video');
  }

}
