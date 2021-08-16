<?php

namespace Drupal\juicer_io\Model;

use function Symfony\Component\String\s as string;

/**
 * Class representing a single item from a feed.
 */
class FeedItem {

  /**
   * The url of the item image.
   *
   * @var \string
   */
  private $imageUrl;

  /**
   * The unformatted message of the item.
   *
   * @var \string
   */
  private $unformattedMessage;

  /**
   * Link for the item content.
   *
   * @var \string
   */
  private $link;

  /**
   * When was the item created.
   *
   * @var \DateTime
   */
  private $createdAt;

  /**
   * FeedItem constructor.
   *
   * @param \string $image_url
   *   The main image url.
   * @param \string $unformatted_message
   *   Unformatted message describing the item.
   * @param \string $link
   *   Link to the item.
   * @param \DateTime $created_at
   *   Create time for the item.
   */
  public function __construct(
    string $image_url,
    string $unformatted_message,
    string $link,
    \DateTime $created_at
  ) {
    $this->imageUrl = $image_url;
    $this->unformattedMessage = $unformatted_message;
    $this->link = $link;
    $this->createdAt = $created_at;
  }

  /**
   * Creates an instance based on the juicer.io feed data format.
   *
   * @param array $item
   *   A singe item array from the feed data.
   *
   * @return static
   *   The item object.
   */
  public static function createFromFeedArray(array $item): self {
    return new static(
      $item['image'],
      $item['unformatted_message'],
      $item['full_url'],
      \DateTime::createFromFormat(
        \DateTimeInterface::RFC3339_EXTENDED,
        $item['external_created_at']
      )
    );
  }

  /**
   * Returns the url for the item's main image.
   *
   * @return \string
   *   Url for the main image.
   */
  public function getImageUrl(): string {
    return $this->imageUrl;
  }

  /**
   * Returns an appropriate alt tag for the main image.
   *
   * @return \string
   *   Alt tag content for the main image.
   */
  public function getImageAlt(): string {
    return string(strip_tags($this->unformattedMessage))
      ->truncate(100 - 3, '...', FALSE);
  }

  /**
   * Returns the link for the item.
   *
   * @return \string
   *   Link for the item.
   */
  public function getLink(): string {
    return $this->link;
  }

  /**
   * Returns the time the feed item was created.
   *
   * @return \DateTime
   *   Creation time of the item.
   */
  public function getCreatedAt(): \DateTime {
    return $this->createdAt;
  }

  /**
   * Transforms the object into an array.
   *
   * @return array
   *   Array representation of the item.
   */
  public function toArray(): array {
    return [
      'image' => [
        'url' => $this->getImageUrl(),
        'alt' => $this->getImageAlt(),
      ],
      'link' => $this->getLink(),
      'createdAt' => $this->getCreatedAt()
        ->format(\DateTimeInterface::RFC3339_EXTENDED),
    ];
  }

}
