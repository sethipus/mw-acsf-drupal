<?php

namespace Drupal\mars_common;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LanguageHelper is responsible for some i18n BE logic.
 */
class LanguageHelper {

  const TRANSLATION_BLOCK_CONFIG_CONTEXT = 'MARS Config';

  const TRANSLATION_CONTEXT = 'MARS';

  use StringTranslationTrait;

  /**
   * The LanguageManager service.
   *
   * @var \Drupal\Core\Language\LanguageManager
   */
  private $languageManager;

  /**
   * The current language id.
   *
   * @var string
   */
  private $currentLanguageId;

  /**
   * {@inheritdoc}
   */
  public function __construct(LanguageManager $language_manager, TranslationInterface $string_translation) {
    $this->languageManager = $language_manager;
    $this->stringTranslation = $string_translation;
    $this->currentLanguageId = $this->languageManager->getCurrentLanguage()->getId();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('language_manager'),
      $container->get('string_translation')
    );
  }

  /**
   * Translates a string to the current language within the predefined context.
   *
   * @param string $string
   *   A string containing the English text to translate.
   * @param array $args
   *   Optional array of arguments.
   * @param array $options
   *   Optional array of options.
   * @param string $context
   *   Context to translate.
   *
   * @return string|null
   *   An object that, when cast to a string, returns the translated string.
   *
   * @see \Drupal\Component\Render\FormattableMarkup::placeholderFormat()
   * @see \Drupal\Core\StringTranslation\TranslatableMarkup::__construct()
   *
   * @ingroup sanitization
   */
  public function translate($string, array $args = [], array $options = [], $context = self::TRANSLATION_CONTEXT) {
    if ($string ?? NULL) {
      $options['context'] = $context;
      // @codingStandardsIgnoreStart
      return $this->t($string, $args, $options)->__toString();
      // @codingStandardsIgnoreEnd
    }
    return NULL;
  }

  /**
   * Translates a string to the current language within config context.
   *
   * @param string $string
   *   A string containing the English text to translate.
   * @param array $args
   *   Optional array of arguments.
   * @param array $options
   *   Optional array of options.
   *
   * @return string|null
   *   An object that, when cast to a string, returns the translated string.
   */
  public function translateBlockConfig($string, array $args = [], array $options = []) {
    return $this->translate($string, $args, $options, self::TRANSLATION_BLOCK_CONFIG_CONTEXT);
  }

  /**
   * Get translated content when available.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface|null $content
   *   The content entity.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface|null
   *   The translated content entity.
   */
  public function getTranslation(ContentEntityInterface $content = NULL) {
    if ($content && $content->hasTranslation($this->currentLanguageId)) {
      return $content->getTranslation($this->currentLanguageId);
    }
    return $content;
  }

  /**
   * Get current language id.
   *
   * @return string
   *   The language id.
   */
  public function getCurrentLanguageId(): string {
    return $this->currentLanguageId;
  }

  /**
   * Language manager service.
   *
   * @return \Drupal\Core\Language\LanguageManager
   *   The language manager.
   */
  public function getLanguageManager(): LanguageManager {
    return $this->languageManager;
  }

}
