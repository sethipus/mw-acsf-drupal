<?php

namespace Drupal\mars_common\SVG;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Factory class to create SVG objects from file entities.
 */
class SVGFactory {

  /**
   * The file entity storage service.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private $fileStorage;

  /**
   * SVGFactory constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager
  ) {
    $this->fileStorage = $entity_type_manager->getStorage('file');
  }

  /**
   * Creates svg file based on the file entity id that it receives.
   *
   * @param string $file_id
   *   The file entity id.
   *
   * @return \Drupal\mars_common\SVG\SVG
   *   The svg object based on the found file entity.
   *
   * @throws \Drupal\mars_common\SVG\SVGException
   */
  public function createSvgFromFileId(string $file_id): SVG {
    /** @var \Drupal\file\Entity\File $drupal_file */
    $drupal_file = $this->fileStorage->load($file_id);
    if (!$drupal_file) {
      throw SVGException::missingDrupalFile($file_id);
    }
    $file_uri = $drupal_file->getFileUri();
    return SVG::createFromFile($file_uri);
  }

}
