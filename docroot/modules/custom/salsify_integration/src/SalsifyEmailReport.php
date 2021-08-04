<?php

namespace Drupal\salsify_integration;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\TempStore\SharedTempStoreFactory;

/**
 * Class SalsifyEmailReport - email reporting logic.
 *
 * @package Drupal\salsify_integration
 */
class SalsifyEmailReport {

  use StringTranslationTrait;

  /**
   * The Mail manager service.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  private $mailManager;

  /**
   * The Language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  private $languageManager;

  /**
   * The Config service.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private $config;

  /**
   * The Enitity type service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * The Shared temp store interface.
   *
   * @var \Drupal\Core\TempStore\SharedTempStoreFactory
   */
  protected $sharedTempStore;

  /**
   * The logger interface.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs a \Drupal\salsify_integration\Salsify object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory interface.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Mail\MailManagerInterface $mail_manager
   *   Mail manager service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language manager service.
   * @param \Drupal\Core\TempStore\SharedTempStoreFactory $temp_store
   *   Shared temp store service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory interface.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    EntityTypeManagerInterface $entity_type_manager,
    MailManagerInterface $mail_manager,
    LanguageManagerInterface $language_manager,
    SharedTempStoreFactory $temp_store,
    LoggerChannelFactoryInterface $logger_factory
  ) {
    $this->config = $config_factory->get('salsify_integration.settings');
    $this->mailManager = $mail_manager;
    $this->languageManager = $language_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->sharedTempStore = $temp_store->get('salsify_integration');
    $this->logger = $logger_factory->get('salsify_integration');
  }

  /**
   * Send report email.
   *
   * @param mixed $validation_errors
   *   Validation errors.
   * @param array $deleted_items
   *   Deleted items.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function sendReport($validation_errors, array $deleted_items = []) {
    $current_langcode = $this->languageManager->getCurrentLanguage()->getId();
    $email = $this->config->get('salsify_import.email');
    $send_email_flag = $this->config->get('send_email');

    // Get superadmin account object.
    $account = $this->entityTypeManager
      ->getStorage('user')
      ->load(1);

    if (isset($email) && $send_email_flag) {
      try {
        $result = $this->mailManager->mail(
          'user',
          'salsify_import',
          $email,
          $current_langcode,
          [
            'validation_errors' => $validation_errors,
            'deleted_items' => $deleted_items,
            'account' => $account,
          ]
        );
        if ($result['result'] === TRUE) {
          $this->logger->notice('Salisfy import report was sent.');
        }
      }
      catch (\Exception $e) {
        $this->logger->error($this->t('There was an error during sending
        email report: @message', ['@message' => $e->getMessage()]));
      }
    }
  }

  /**
   * Save validation errors to temp shared storage.
   *
   * @param array $validation_errors
   *   List of errors.
   *
   * @throws \Drupal\Core\TempStore\TempStoreException
   */
  public function saveValidationErrors(array $validation_errors) {
    $saved_errors = $this->sharedTempStore
      ->get('validation_errors');
    if (isset($saved_errors)) {
      $saved_errors = array_merge($saved_errors, $validation_errors);
      $this->sharedTempStore->set('validation_errors', $saved_errors);
    }
    else {
      $this->sharedTempStore->set(
        'validation_errors',
        $validation_errors
      );
    }
  }

  /**
   * Send email report by cron.
   */
  public function sendReportByCron() {
    $saved_errors = $this->sharedTempStore
      ->get('validation_errors');

    if (isset($saved_errors) && is_array($saved_errors) && !empty($saved_errors)) {
      $this->sendReport($saved_errors);

      // Delete validation errors after sending email.
      $this->sharedTempStore
        ->set('validation_errors', NULL);
    }
  }

}
