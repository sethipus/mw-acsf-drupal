<?php

namespace Drupal\mars_common\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class Light House Inventory.
 *
 * @package Drupal\mars_common\Controller
 */
class LightHouseInventory extends ControllerBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Request service.
   *
   * @var Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The configFactory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory,
    RequestStack $request_stack,
    EntityTypeManagerInterface $entity_type_manager) {
    $this->configFactory = $config_factory;
    $this->requestStack = $request_stack;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('request_stack'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Export a CSV of data.
   */
  public function videoList() {
    // Start using PHP's built in file handler functions to create
    // a temporary file.
    $handle = fopen('php://temp', 'w+');

    // Set up the header that will be displayed as the first line of the CSV.
    // file Blank strings are used for multi-cell values where there is a count
    // of the "keys" and a list of the keys with the count of their usage.
    $header = [
      'Site Url',
      'Site Brand Name',
      'File Name',
      'External ID',
      'Original external ID',
      'File Type',
    ];
    // Add the header as the first line of the CSV.
    fputcsv($handle, $header);
    // Find and load all of the Media we are going to include.
    // Set properties to filter.
    $query_values = [
      'bundle' => 'lighthouse_video',
    ];

    // Get the nodes.
    $medias = $this->entityTypeManager
      ->getStorage('media')
      ->loadByProperties($query_values);

    // Iterate through the nodes.  We want one row in the CSV per Media.
    foreach ($medias as $media) {

      // Build the array for putting the row data together.
      $data = $this->buildRow($media);

      // Add the data we exported to the next line of the CSV>.
      fputcsv($handle, array_values($data));
    }
    // Reset where we are in the CSV.
    rewind($handle);

    // Retrieve the data from the file handler.
    $csv_data = stream_get_contents($handle);

    // Close the file handler since we don't need it anymore.
    // We are not storing this file anywhere in the filesystem.
    fclose($handle);

    // This is the "magic" part of the code.  Once the data is built, we can
    // return it as a response.
    $response = new Response();

    // By setting these 2 header options, the browser will see the URL
    // used by this Controller to return
    // a CSV file called "lighthouse_inventory.csv".
    $host_name = $this->requestStack->getCurrentRequest()->getHost();
    $response->headers->set('Content-Type', 'text/csv');
    $response->headers->set('Content-Disposition', 'attachment; filename="' . $host_name . "_lighthouse_inventory.csv");

    // This line physically adds the CSV data we created.
    $response->setContent($csv_data);

    return $response;
  }

  /**
   * Fetches data and builds CSV row.
   */
  private function buildRow($media) {
    $data = [
      'site_url' => $this->requestStack->getCurrentRequest()->getSchemeAndHttpHost(),
      'site_brand_name' => $this->configFactory->get('mars_common.system.site')->get('brand'),
      'file_name' => $media->getName(),
      'external_id' => ($media->bundle() == 'lighthouse_video') ? $media->field_external_id->value : 'N/A',
      'original_external_id' => ($media->bundle() == 'lighthouse_video') ? $media->field_original_external_id->value : 'N/A',
      'file_type' => ($media->bundle() == 'lighthouse_video') ? $this->t('Lighthouse Video') : $this->t('Video File'),
    ];
    return $data;
  }

}
