<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\mars_common\ThemeConfiguratorParser;

/**
 * Provides a poll block.
 *
 * @Block(
 *   id = "poll_block",
 *   admin_label = @Translation("MARS: Poll Block"),
 *   category = @Translation("Mars Common")
 * )
 */
class PollBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $pollEntityStorage;

  /**
   * View builder.
   *
   * @var \Drupal\Core\Entity\EntityViewBuilderInterface
   */
  protected $pollViewBuilder;

  /**
   * ThemeConfiguratorParser.
   *
   * @var \Drupal\mars_common\ThemeConfiguratorParser
   */
  protected $themeConfiguratorParser;

  /**
   * Ajax id of block.
   *
   * @var string
   */
  protected $ajaxId;

  /**
   * Uuid service.
   *
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  protected $uuid;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('mars_common.theme_configurator_parser'),
      $container->get('uuid')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    ThemeConfiguratorParser $themeConfiguratorParser,
    UuidInterface $uuid
  ) {
    $this->pollEntityStorage = $entity_type_manager->getStorage('poll');
    $this->pollViewBuilder = $entity_type_manager->getViewBuilder('poll');
    $this->themeConfiguratorParser = $themeConfiguratorParser;
    $this->uuid = $uuid;
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'label_display' => FALSE,
    ];
  }

  /**
   * Generates and sets ajax id configuration value.
   *
   * @param array $configuration
   *   Configuration array.
   *
   * @return string
   *   Ajax id.
   */
  public function generateAjaxId(array &$configuration) {
    return $configuration['ajaxId'] = $this->uuid->generate();
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    // In case an ajax id has not been assigned, create one.
    if (empty($configuration['ajaxId'])) {
      $this->generateAjaxId($configuration);
    }

    // Make sure the configuration value is not null.
    if (!$configuration) {
      $configuration = [];
    }

    parent::setConfiguration($configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $conf = $this->getConfiguration();
    if (!$conf['poll']) {
      return [];
    }

    $pollEntity = $this->pollEntityStorage->load($conf['poll']);
    if (!$pollEntity) {
      return [];
    }

    $ajax_id = $this->configuration['ajaxId'];
    $build['#attached']['library'] = ['mars_common/poll_ajax'];
    $build['#attached']['drupalSettings'] = [
      'pollConfig' => [
        $ajax_id => [
          'plugin_id' => $this->pluginId,
          'config' => [
            'ajaxId' => $ajax_id,
          ],
          'block_config' => $conf,
        ],
      ],
    ];

    if (isset($conf['ajax_render']) && $conf['ajax_render'] == 'true') {
      $build['#poll'] = $this->pollViewBuilder->view($pollEntity);
    }
    $build['#theme'] = 'poll_block';

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['poll'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Poll Entity'),
      '#target_type' => 'poll',
      '#selection_handler' => 'default:poll_by_field',
      '#selection_settings' => [
        'filter' => ['field_type' => 'poll'],
      ],
      '#default_value' => $this->getPollEntity(),
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $this->configuration['poll'] = $form_state->getValue('poll');
  }

  /**
   * Returns the poll entity that's saved to the block.
   */
  private function getPollEntity(): ?EntityInterface {
    $pollEntityId = $this->getConfiguration()['poll'] ?? NULL;
    if (!$pollEntityId) {
      return NULL;
    }

    return $this->pollEntityStorage->load($pollEntityId);
  }

}
