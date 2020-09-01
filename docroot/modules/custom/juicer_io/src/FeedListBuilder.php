<?php

declare(strict_types = 1);

namespace Drupal\juicer_io;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * List builder class for Feed entity.
 */
class FeedListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Feed label');
    $header['feed_id'] = $this->t('Feed id');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->label();
    $row['feed_id'] = $entity->get('feed_id');
    return $row + parent::buildRow($entity);
  }

}
