<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mars_common\Traits\EntityBrowserFormTrait;

/**
 * Provides a 'MARS: Freeform Story Block' Block.
 *
 * @Block(
 *   id = "freeform_story_block",
 *   admin_label = @Translation("MARS: Freeform Story Block"),
 *   category = @Translation("Mars Common"),
 * )
 */
class FreeformStoryBlock extends BlockBase {

  use EntityBrowserFormTrait;

  /**
   * Lighthouse entity browser id.
   */
  const LIGHTHOUSE_ENTITY_BROWSER_ID = 'image_browser';

  /**
   * Aligned by left side.
   */
  const LEFT_ALIGNED = 'left_aligned';

  /**
   * Aligned by right side.
   */
  const RIGHT_ALIGNED = 'right_aligned';

  /**
   * Aligned by center.
   */
  const CENTER_ALIGNED = 'center_aligned';

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['block_aligned'] = [
      '#type' => 'select',
      '#title' => $this->t('Block aligned'),
      '#default_value' => $this->configuration['block_aligned'],
      '#options' => [
        self::LEFT_ALIGNED => $this->t('Left aligned'),
        self::RIGHT_ALIGNED => $this->t('Right aligned'),
        self::CENTER_ALIGNED => $this->t('Center aligned'),
      ],
    ];
    $form['header_1'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Header 1'),
      '#default_value' => $this->configuration['header_1'],
      '#maxlength' => 60,
      '#required' => TRUE,
    ];
    $form['header_2'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Header 2'),
      '#default_value' => $this->configuration['header_2'],
      '#maxlength' => 60,
    ];
    // Entity Browser element for background image.
    $form['image'] = $this->getEntityBrowserForm(self::LIGHTHOUSE_ENTITY_BROWSER_ID, $this->configuration['image'], 1, 'thumbnail');
    // Convert the wrapping container to a details element.
    $form['image']['#type'] = 'details';
    $form['image']['#title'] = $this->t('Image');
    $form['image']['#open'] = TRUE;

    $form['body'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Description'),
      '#default_value' => $this->configuration['body']['value'] ?? '',
      '#format' => $this->configuration['body']['format'] ?? 'rich_text',
      '#maxlength' => 1000,
      '#required' => TRUE,
    ];
    $form['background_shape'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Background shape'),
      '#default_value' => $this->configuration['background_shape'] ?? FALSE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build['#block_aligned'] = $this->configuration['block_aligned'];
    $build['#header_1'] = $this->configuration['header_1'];
    $build['#header_2'] = $this->configuration['header_2'];
    $build['#body'] = $this->configuration['body']['value'];
    $build['#background_shape'] = $this->configuration['background_shape'];
    /** @var \Drupal\media\MediaInterface $media */
    if (!empty($this->configuration['image']) && $media = static::loadEntityBrowserEntity($this->configuration['image'])) {
      $build['#image'] = file_create_url($media->getFileUri());
    }
    $build['#theme'] = 'freeform_story_block';

    return $build;
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
    $this->configuration['image'] = $this->getEntityBrowserValue($form_state, 'image');
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    $config = $this->getConfiguration();
    return [
      'block_aligned' => $config['block_aligned'] ?? '',
      'header_1' => $config['header_1'] ?? $this->t('Header 1'),
      'header_2' => $config['header_2'] ?? '',
      'body' => $config['body']['value'] ?? '',
      'background_shape' => $config['background_shape'] ?? '',
      'image' => $config['image'] ?? '',
    ];
  }

}
