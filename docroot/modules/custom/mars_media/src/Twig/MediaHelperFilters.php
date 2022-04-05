<?php

namespace Drupal\mars_media\Twig;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\mars_media\MediaHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Class that adds media helper twig filters.
 */
class MediaHelperFilters extends AbstractExtension {

  /**
   * The media helper service.
   *
   * @var \Drupal\mars_media\MediaHelper
   */
  private $mediaHelper;

  /**
   * MediaHelperFilters constructor.
   *
   * @param \Drupal\mars_media\MediaHelper $media_helper
   *   The media helper service.
   */
  public function __construct(MediaHelper $media_helper) {
    $this->mediaHelper = $media_helper;
  }

  /**
   * {@inheritdoc}
   */
  public function getFilters() {
    return [
      new TwigFilter('entityMainMediaId', [$this, 'mainMediaId']),
      new TwigFilter('entityGroupMediaId', [$this, 'groupMediaId']),
      new TwigFilter('mediaParamsById', [$this, 'mediaParams']),
    ];
  }

  /**
   * Filter to return main media id.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface|null $contentEntity
   *   The entity that we are processing for main media.
   *
   * @return string|null
   *   The media ID or NULL if it has none or the entity was NULL.
   */
  public function mainMediaId(?ContentEntityInterface $contentEntity): ?string {
    if ($contentEntity === NULL) {
      return NULL;
    }
    return $this->mediaHelper->getEntityMainMediaId($contentEntity);
  }

  /**
   * Return the media param array for the given media id.
   *
   * @param string|null $mediaId
   *   The media id. For better chainability it accepts null as well.
   *
   * @return array
   *   The media helper array for the media.
   */
  public function mediaParams(?string $mediaId): array {
    return $this->mediaHelper->getMediaParametersById($mediaId);
  }

  /**
   * Filter to return group media id.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface|null $contentEntity
   *   The entity that we are processing for group media.
   *
   * @return string|null
   *   The media ID or NULL if it has none or the entity was NULL.
   */
  public function groupMediaId(?ContentEntityInterface $contentEntity): ?string {
    if ($contentEntity === NULL) {
      return NULL;
    }
    return $this->mediaHelper->getEntityGroupMediaId($contentEntity);
  }

}
