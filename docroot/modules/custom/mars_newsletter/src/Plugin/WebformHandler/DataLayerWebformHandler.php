<?php

namespace Drupal\mars_newsletter\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use \Drupal\file\Entity\File;

/**
 * Inserts data layer on a webform submission.
 *
 * @WebformHandler(
 *   id = "data_layer",
 *   label = @Translation("Data layer"),
 *   category = @Translation("Action"),
 *   description = @Translation("Inserts data layer on a webform submission."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 * )
 */
class DataLayerWebformHandler extends WebformHandlerBase {

  /**
   * The data layer service.
   *
   * @var \Drupal\MY_MODULE\DataLayerService
   */
  protected $dataLayerService;

  /**
   * Entity type manager.
   *
   * @var \Drupal\core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->dataLayerService = $container->get('mars_newsletter.data_layer_service');
    $instance->entityTypeManager = $container->get('entity_type.manager');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
    $submitted_values = $webform_submission->getData();
    $image_id = !empty($submitted_values['image']) ? $submitted_values['image'] : "";
    if($image_id){
      $base_path = \Drupal::request()->getSchemeAndHttpHost();
      $target_directory = 'public://';
      $file = $this->entityTypeManager->getStorage('file')->load($image_id);
      $file->save();
      $file_name = $file->getFilename();
      $file_uri = $target_directory . '/' . $file_name;
      $file = file_move($file, $file_uri, 'FILE_EXISTS_REPLACE');
      $file_uri = $file->getFileUri();
      $file_path = file_url_transform_relative(file_create_url($file_uri));
      $file->setPermanent();
      $file->save();
      $submitted_values['image'] = $base_path . $file_path;
    }
    $this->dataLayerService->addData($submitted_values);
  }
}
