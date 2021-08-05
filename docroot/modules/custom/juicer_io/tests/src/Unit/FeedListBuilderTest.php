<?php

declare(strict_types=1);

namespace Drupal\Tests\juicer_io\Unit;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\juicer_io\FeedListBuilder;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Unit tests for FeedListBuilder class.
 *
 * @coversDefaultClass \Drupal\juicer_io\FeedListBuilder
 */
class FeedListBuilderTest extends UnitTestCase {

  /**
   * Test that label is added to the header.
   *
   * @test
   */
  public function shouldAddLabelToHeader() {
    $list_builder = $this->createListBuilder();

    $header = $list_builder->buildHeader();

    $this->assertArrayContainsTranslatableMarkup(
      $header,
      'label',
      'Feed label'
    );
  }

  /**
   * Test that feed id is added to the header.
   *
   * @test
   */
  public function shouldAddFeedIdToHeader() {
    $list_builder = $this->createListBuilder();

    $header = $list_builder->buildHeader();

    $this->assertArrayContainsTranslatableMarkup(
      $header,
      'feed_id',
      'Feed id'
    );
  }

  /**
   * Test that entity label is added to the row.
   *
   * @test
   */
  public function shouldAddLabelOfEntityToTheRow() {
    $expected_label_value = 'label_value';
    $list_builder = $this->createListBuilder();
    $entity = $this->mockEntity('feed_id', $expected_label_value);

    $header = $list_builder->buildRow($entity);

    $this->assertArrayContainsString($header, 'label', $expected_label_value);
  }

  /**
   * Test that feed id is added to the row.
   *
   * @test
   */
  public function shouldAddFeedIdOfEntityToTheRow() {
    $list_builder = $this->createListBuilder();
    $expected_feed_id = 'feed_id';
    $entity = $this->mockEntity($expected_feed_id, 'label_value');

    $header = $list_builder->buildRow($entity);

    $this->assertArrayContainsString($header, 'feed_id', $expected_feed_id);
  }

  /**
   * Creates a list builder with dependencies mocked out.
   *
   * @return \Drupal\juicer_io\FeedListBuilder
   *   The created FeedListBuilder object.
   */
  private function createListBuilder(): FeedListBuilder {
    \Drupal::setContainer($this->createMock(ContainerInterface::class));
    $entity_type = $this->createMock(EntityTypeInterface::class);
    $entity_storage = $this->createMock(EntityStorageInterface::class);
    $module_handler = $this->createMock(ModuleHandlerInterface::class);
    $module_handler->method('invokeAll')->willReturn([]);
    $list_builder = new FeedListBuilder($entity_type, $entity_storage);
    $list_builder->setModuleHandler($module_handler);
    return $list_builder;
  }

  /**
   * Creates a mock entity with the provided values.
   *
   * @param string $feed_id
   *   The id of the feed.
   * @param string $label_value
   *   The label of the feed.
   *
   * @return \Drupal\Core\Entity\FieldableEntityInterface|\PHPUnit\Framework\MockObject\MockObject
   *   The mocked entity.
   */
  private function mockEntity(string $feed_id, string $label_value) {
    $entity = $this->createMock(FieldableEntityInterface::class);
    $entity->method('get')
      ->with('feed_id')
      ->willReturn($feed_id);
    $entity->method('label')
      ->willReturn($label_value);
    return $entity;
  }

  /**
   * Assert that the array contains a translatable string under the key.
   *
   * @param array $array
   *   The array to check.
   * @param string $key
   *   The key where we will check if we have the correct value.
   * @param string $value
   *   The value that we are expecting in the header.
   */
  private function assertArrayContainsTranslatableMarkup(
    array $array,
    string $key,
    string $value
  ): void {
    $this->assertArrayHasKey($key, $array);
    /** @var \Drupal\Core\StringTranslation\TranslatableMarkup $markup */
    $markup = $array[$key];
    $this->assertInstanceOf(TranslatableMarkup::class, $markup);
    $this->assertEquals($markup->getUntranslatedString(), $value);
  }

  /**
   * Assert that the array contains a string under a given key.
   *
   * @param array $array
   *   The array to check.
   * @param string $key
   *   The key where we will check if we have the correct value.
   * @param string $value
   *   The value that we are expecting in the header.
   */
  private function assertArrayContainsString(
    array $array,
    string $key,
    string $value
  ): void {
    $this->assertArrayHasKey($key, $array);
    $this->assertEquals($array[$key], $value);
  }

}
