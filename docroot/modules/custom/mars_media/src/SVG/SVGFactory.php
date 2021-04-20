<?php

namespace Drupal\mars_media\SVG;

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
   * Unique id generator service.
   *
   * @var \Drupal\mars_media\SVG\SVGUniqueIdGenerator
   */
  private $uniqueIdGenerator;

  /**
   * SVGFactory constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\mars_media\SVG\SVGUniqueIdGenerator $unique_id_generator
   *   Service for generating unique ids.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    SVGUniqueIdGenerator $unique_id_generator
  ) {
    $this->fileStorage = $entity_type_manager->getStorage('file');
    $this->uniqueIdGenerator = $unique_id_generator;
  }

  /**
   * Creates svg file based on the file entity id that it receives.
   *
   * @param string $file_id
   *   The file entity id.
   *
   * @return \Drupal\mars_media\SVG\SVG
   *   The svg object based on the found file entity.
   *
   * @throws \Drupal\mars_media\SVG\SVGException
   */
  public function createSvgFromFileId(string $file_id): SVG {
    /** @var \Drupal\file\Entity\File $drupal_file */
    $drupal_file = $this->fileStorage->load($file_id);
    if (!$drupal_file) {
      throw SVGException::missingDrupalFile($file_id);
    }
    $file_uri = $drupal_file->getFileUri();
    $id = $this->uniqueIdGenerator->generateId();
    return SVG::createFromFile($file_uri, $id);
  }

}
