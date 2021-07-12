<?php

namespace Drupal\mars_common\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mars_common\LanguageHelper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Settings form for disable language.
 *
 * @internal
 */
class MarsDisableLanguageForm extends ConfigFormBase {

  /**
   * Minimum count of languages.
   */
  const MINIMUM_LANGUAGES = 1;

  /**
   * Language helper service.
   *
   * @var \Drupal\mars_common\LanguageHelper
   */
  private $languageHelper;

  /**
   * MarsSiteLabelsForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   * @param \Drupal\mars_common\LanguageHelper $language_helper
   *   The language helper service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, LanguageHelper $language_helper) {
    parent::__construct($config_factory);
    $this->languageHelper = $language_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('mars_common.language_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'disable_language';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['mars_common.disable_language'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config('mars_common.disable_language');
    $langcodes = $this->languageHelper->getLanguageManager()->getLanguages();
    $langcodes_list = array_keys($langcodes);

    if (count($langcodes_list) > static::MINIMUM_LANGUAGES) {
      $form['exclude_language'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Exclude language from language switcher and disable access to pages with selected language(s) (for anonymous users only)'),
        '#states' => [
          'visible' => [
            [':input[name="settings[language_selector]"]' => ['checked' => TRUE]],
          ],
        ],
      ];
      foreach ($langcodes_list as $langcode) {
        $form['exclude_language']['language'][$langcode] = [
          '#type' => 'checkbox',
          '#title' => '<b>' . $langcodes[$langcode]->getName() . ' (' . $langcode . ')</b>',
          '#default_value' => $config->get($langcode) ?? FALSE,
        ];
        if ($this->languageHelper->getLanguageManager()->getDefaultLanguage()->getId() == $langcode) {
          $form['exclude_language']['language'][$langcode]['#disabled'] = TRUE;
          $form['exclude_language']['language'][$langcode]['#default_value'] = FALSE;
          $form['exclude_language']['language'][$langcode]['#description'] = $this->t('Disable ability to exclude default language.');
        }
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('mars_common.disable_language');
    foreach ($this->languageHelper->getLanguageManager()->getLanguages() as $key => $language) {
      $config->set($key, $form_state->getValue($key));
    }
    $config->save();

    parent::submitForm($form, $form_state);
  }

}
