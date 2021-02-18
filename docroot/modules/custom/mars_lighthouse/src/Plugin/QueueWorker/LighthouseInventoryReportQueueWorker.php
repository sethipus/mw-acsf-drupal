<?php

namespace Drupal\mars_lighthouse\Plugin\QueueWorker;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\mars_lighthouse\LighthouseClientInterface;
use Drupal\mars_lighthouse\LighthouseException;
use Drupal\mars_lighthouse\LighthouseInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Process a queue.
 *
 * @QueueWorker(
 *   id = "lighthouse_inventory_report_queue",
 *   title = @Translation("Lighthouse inventory report queue worker"),
 *   cron = {"time" = 360}
 * )
 */
class LighthouseInventoryReportQueueWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * Media storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $mediaStorage;

  /**
   * The configFactory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Lighthouse client.
   *
   * @var \Drupal\mars_lighthouse\LighthouseClientInterface
   */
  protected $lighthouseClient;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Lighthouse adapter.
   *
   * @var \Drupal\mars_lighthouse\LighthouseInterface
   */
  protected $lighthouseAdapter;

  /**
   * Logger for this channel.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The current request.
   *
   * @var null|\Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * LighthouseQueueWorker constructor.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    ConfigFactoryInterface $config_factory,
    LighthouseClientInterface $lighthouse_client,
    LighthouseInterface $lighthouse,
    LoggerChannelFactoryInterface $logger_factory,
    RequestStack $request_stack
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->mediaStorage = $entity_type_manager->getStorage('media');
    $this->configFactory = $config_factory;
    $this->lighthouseClient = $lighthouse_client;
    $this->lighthouseAdapter = $lighthouse;
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger_factory->get('mars_lighthouse');
    $this->currentRequest = $request_stack->getCurrentRequest();
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
      $container->get('lighthouse.client'),
      $container->get('lighthouse.adapter'),
      $container->get('logger.factory'),
      $container->get('request_stack')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    $asset_list = [];
    $asset_ids = [];
    if (!empty($data)) {
      $host = $this->currentRequest->getSchemeAndHttpHost();
      /* @var \Drupal\media\Entity\Media $media */
      foreach ($data as $media) {
        $asset_ids[] = $media->field_external_id->value;
        $asset_list[] = [
          'assetId' => $media->field_external_id->value,
          'isDerivedAsset' => FALSE,
          'repoId' => $host . ':' . $media->id(),
          'repoLoc' => '',
          'note' => '',
        ];
      }
    }
    try {
      $data = $this->lighthouseClient->sentInventoryReport($asset_list);
    }
    catch (LighthouseException $exception) {
      $this->logger->error('Failed to run inventory report "%error"', ['%error' => $exception->getMessage()]);
    }

    if (!empty($data)) {
      $this->logger->info($this->t('Inventory report have already sent to lighthouse with asset ids @external_ids', [
        '@external_ids' => implode(', ', array_unique($asset_ids)),
      ]));
    }
  }

}
