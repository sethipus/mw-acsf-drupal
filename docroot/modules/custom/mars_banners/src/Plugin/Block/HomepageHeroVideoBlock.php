<?php

namespace Drupal\mars_banners\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Homepage hero video Block.
 *
 * @Block(
 *   id = "homepage_hero_video_block",
 *   admin_label = @Translation("Homepage hero video block"),
 *   category = @Translation("Global elements"),
 * )
 */
class HomepageHeroVideoBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();

    $build['#title'] = $config['label'];
    $build['#eyebrow'] = $config['eyebrow'];
    $build['#cta_url'] = $config['cta']['url'];
    $build['#cta_title'] = $config['cta']['title'];
    $build['#background_video'] = $config['background_video'];
    $build['#background_video'] = $config['background_video'];

    $build['#theme'] = 'homepage_hero_video_block';
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
      '#maxlength' => 45,
      '#required' => TRUE,
      '#default_value' => $config['cta']['title'] ?? 'Explore',
    ];
    $form['background_video'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Background video link'),
      '#maxlength' => 2048,
      '#required' => TRUE,
      '#default_value' => $config['background_video'] ?? '',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfiguration($form_state->getValues());
  }

}
