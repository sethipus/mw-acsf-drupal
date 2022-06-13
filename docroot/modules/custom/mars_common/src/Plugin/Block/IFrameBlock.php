<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Provides an iFrame block.
 *
 * @Block(
 *   id = "iframe_block",
 *   admin_label = @Translation("MARS: iFrame"),
 *   category = @Translation("Mars Common")
 * )
 */
class IFrameBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ConfigFactoryInterface $config_factory
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $conf = $this->getConfiguration();
    $build['#url'] = $conf['url'] ?? '';
    $build['#accessibility_title'] = $conf['accessibility_title'] ?? '';
    $build['#theme'] = 'iframe_block';
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    $conf = $this->getConfiguration();

    return [
      'label_display' => FALSE,
      'accessibility_title' => $conf['accessibility_title'] ?? '',
      'url' => $conf['url'] ?? '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildConfigurationForm($form, $form_state);
    $character_limit_config = $this->configFactory->get('mars_common.character_limit_page');

    $form['accessibility_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Accessibility Title'),
      '#maxlength' => !empty($character_limit_config->get('iframe_accessibility_title')) ? $character_limit_config->get('iframe_accessibility_title') : 150,
      '#default_value' => $this->configuration['accessibility_title'] ?? '',
      '#required' => TRUE,
    ];
    $form['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL'),
      '#default_value' => $this->configuration['url'] ?? '',
      '#required' => TRUE,
      '#size' => 65,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state): void {
    parent::blockSubmit($form, $form_state);
    $this->configuration['url'] = $form_state->getValue('url');
    $this->configuration['accessibility_title'] = $form_state->getValue('accessibility_title');
  }

}
