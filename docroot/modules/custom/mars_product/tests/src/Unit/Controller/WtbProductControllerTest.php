<?php

namespace Drupal\Tests\mars_product\Unit\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\mars_media\MediaHelper;
use Drupal\mars_product\Controller\WtbProductController;
use Drupal\node\NodeInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @coversDefaultClass \Drupal\mars_product\Controller\WtbProductController
 * @group mars
 * @group mars_product
 */
class WtbProductControllerTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \Drupal\mars_product\Controller\WtbProductController
   */
  private $controller;

  /**
   * Mock.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $containerMock;

  /**
   * Mock.
   *
   * @var \Drupal\mars_media\MediaHelper|\PHPUnit\Framework\MockObject\MockObject
   */
  private $mediaHelperMock;

  /**
   * Mock.
   *
   * @var \Drupal\node\NodeInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $nodeMock;

  /**
   * Mock.
   *
   * @var \Drupal\Core\Field\EntityReferenceFieldItemListInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $fieldItemListMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);
    $this->controller = new WtbProductController(
      $this->mediaHelperMock
    );
  }

  /**
   * Test.
   */
  public function testShouldInstantiateProperly() {
    $this->containerMock
      ->expects($this->exactly(1))
      ->method('get')
      ->willReturnMap(
        [
          [
            'mars_media.media_helper',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->mediaHelperMock,
          ],
        ]
      );
    $this->controller::create($this->containerMock);
  }

  /**
   * Test.
   */
  public function testShouldProductInfo() {
    $this->nodeMock->target_id = '123';
    $this->nodeMock->value = 'value';

    $this->nodeMock
      ->expects($this->any())
      ->method('get')
      ->willReturn($this->fieldItemListMock);

    $this->fieldItemListMock
      ->expects($this->once())
      ->method('referencedEntities')
      ->willReturn([
        $this->nodeMock,
      ]);

    $this->mediaHelperMock
      ->expects($this->exactly(2))
      ->method('getMediaParametersById')
      ->willReturn([
        'error' => TRUE,
        'src' => 'src',
        'alt' => 'alt',
      ]);

    $result = $this->controller->productInfo(
      $this->nodeMock
    );
    $this->assertInstanceOf(
      JsonResponse::class,
      $result
    );
    $result = Json::decode($result->getContent());
    $this->assertSame(
      'alt',
      $result[0]['image_alt']
    );
    $this->assertSame(
      'src',
      $result[0]['image_src']
    );
    $this->assertArrayHasKey(
      'size',
      $result[0]
    );
  }

  /**
   * Create all mocks for tests in this file.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->mediaHelperMock = $this->createMock(MediaHelper::class);
    $this->fieldItemListMock = $this->createMock(EntityReferenceFieldItemListInterface::class);
    $this->nodeMock = $this->createMock(NodeInterface::class);
  }

}
