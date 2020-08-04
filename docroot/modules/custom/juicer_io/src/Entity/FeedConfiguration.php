<?php

declare(strict_types=1);

namespace Drupal\juicer_io\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\juicer_io\Model\FeedConfigurationInterface;

/**
 * Defines the FeedConfiguration entity.
 *
 * @ConfigEntityType(
 *   id = "juicer_io_feed",
 *   label = @Translation("Juicer.io feed"),
 *   handlers = {
 *     "list_builder" = "Drupal\juicer_io\FeedListBuilder",
 *     "form" = {
 *       "add" = "Drupal\juicer_io\Form\FeedConfigurationForm",
 *       "edit" = "Drupal\juicer_io\Form\FeedConfigurationForm",
 *       "delete" = "Drupal\juicer_io\Form\FeedConfigurationDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     }
 *   },
 *   config_prefix = "feed",
 *   admin_permission = "administer_juicer_io_feed_entity",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "feed_id",
 *   },
 *   links = {
 *     "canonical" = "/admin/config/services/juicer_io/feed/{juicer_io_feed}",
 *     "add-form" = "/admin/config/services/juicer_io/feed/add",
 *     "edit-form" = "/admin/config/services/juicer_io/feed/{juicer_io_feed}/edit",
 *     "delete-form" = "/admin/config/services/juicer_io/feed/{juicer_io_feed}/delete",
 *     "collection" = "/admin/config/services/juicer_io/feed"
 *   }
 * )
 */
class FeedConfiguration extends ConfigEntityBase implements FeedConfigurationInterface {

  /**
   * The feed ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The feed label.
   *
   * @var string
   */
  protected $label;

  /**
   * The feed id.
   *
   * @var string
   */
  protected $feed_id;

  /**
   * {@inheritdoc}
   */
  public function getUrl(): string {
    return sprintf('https://www.juicer.io/api/feeds/%s', $this->getFeedId());
  }

  /**
   * Returns the id of the feed.
   *
   * @return string
   *   The id.
   */
  public function getFeedId(): string {
    return $this->feed_id ?? '';
  }

}
