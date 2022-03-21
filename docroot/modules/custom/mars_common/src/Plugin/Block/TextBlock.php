<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\mars_common\LanguageHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Text component block.
 *
 * @Block(
 *   id = "text_block",
 *   admin_label = @Translation("MARS: Text block"),
 *   category = @Translation("Page components"),
 * )
 */
class TextBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Language helper service.
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
    LanguageHelper $language_helper
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->languageHelper = $language_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('mars_common.language_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $config = $this->getConfiguration();
    $character_limit_config = \Drupal::config('mars_common.character_limit_page');

    $form['header'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Header'),
      '#maxlength' => !empty($character_limit_config->get('text_block_header')) ? $character_limit_config->get('text_block_header') : 55,
      '#default_value' => $config['header'] ?? '',
    ];

    $form['body'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Body'),
      '#default_value' => $config['body']['value'] ?? '',
      '#format' => $config['body']['format'] ?? 'rich_text',
    ];

    $form['iframe_width_full'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('iFrame full width'),
      '#default_value' => !empty($config['iframe_width_full']) ? $config['iframe_width_full'] : FALSE,
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

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();

    $build['#content'] = $this->languageHelper->translate($config['body']['value']);
    $build['#header'] = $config['header'];
    $build['#iframe_width_full'] = !empty($config['iframe_width_full']) ? TRUE : FALSE;

    $build['#theme'] = 'text_block';

    return $build;
  }

}
