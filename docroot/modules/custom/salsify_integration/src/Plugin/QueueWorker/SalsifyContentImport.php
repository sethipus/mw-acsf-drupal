<?php

namespace Drupal\salsify_integration\Plugin\QueueWorker;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\salsify_integration\ProductHelper;
use Drupal\salsify_integration\SalsifyEmailReport;
use Drupal\salsify_integration\SalsifyImportField;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides functionality for the SalsifyContentImport Queue.
 *
 * @QueueWorker(
 *   id = "salsify_integration_content_import",
 *   title = @Translation("Salsify: Content Import"),
 *   cron = {"time" = 10}
 * )
 */
class SalsifyContentImport extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * The configFactory interface.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The Salsify config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * The Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The QueueFactory object.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queueFactory;

  /**
   * The logger interface.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The Salsify email report interface.
   *
   * @var \Drupal\salsify_integration\SalsifyEmailReport
   */
  protected $emailReport;

  /**
   * Creates a new SalsifyContentImport object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Queue\QueueFactory $queue_factory
   *   The QueueFactory object.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The LoggerFactory object.
   * @param \Drupal\salsify_integration\SalsifyEmailReport $email_report
   *   The Salsify email report object.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    EntityTypeManagerInterface $entity_type_manager,
    QueueFactory $queue_factory,
    LoggerChannelFactoryInterface $logger_factory,
    SalsifyEmailReport $email_report
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory = $config_factory;
    $this->config = $this->configFactory->get('salsify_integration.settings');
    $this->queueFactory = $queue_factory;
    $this->logger = $logger_factory->get('salsify_integration');
    $this->emailReport = $email_report;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('queue'),
      $container->get('logger.factory'),
      $container->get('salsify_integration.email_report')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    // Create a new SalsifyImport object and pass the Salsify data through.
    $force_update = $data['force_update'];
    $process_result = SalsifyImportField::processSalsifyItem(
      $data,
      $force_update,
      ProductHelper::getProductType($data)
    );

    // Save validation errors to shared temp storage in order
    // to send email with detail by cron.
    $this->emailReport
      ->saveValidationErrors($process_result['validation_errors']);

    $this->logger->info($this->t(
      'The Salsify record was @result: @gtin.', [
        '@gtin' => $data['GTIN'],
        '@result' => $process_result['import_result'] ?? NULL,
      ]
    ));
  }

}
