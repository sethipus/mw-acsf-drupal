<?php

namespace Drupal\Tests\salsify_integration\Unit\Event;

use Drupal\salsify_integration\Event\SalsifyGetEntityTypesEvent;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\salsify_integration\Event\SalsifyGetEntityTypesEvent
 * @group mars
 * @group salsify_integration
 */
class SalsifyGetEntityTypesEventTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \Drupal\salsify_integration\Event\SalsifyGetEntityTypesEvent
   */
  private $event;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->event = new SalsifyGetEntityTypesEvent(
      []
    );
  }

  /**
   * Test.
   */
  public function testShouldGetEntityTypesList() {
    $list = $this->event->getEntityTypesList();
    $this->assertIsArray($list);
  }

  /**
   * Test.
   */
  public function testShouldSetEntityTypesList() {
    $this->event->setEntityTypesList([]);
    $list = $this->event->getEntityTypesList();
    $this->assertIsArray($list);
  }

}
