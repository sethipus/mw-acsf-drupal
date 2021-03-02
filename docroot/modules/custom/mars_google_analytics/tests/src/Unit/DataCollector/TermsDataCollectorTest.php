<?php

namespace Drupal\Tests\mars_google_analytics\Unit\DataCollector;

use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\field\FieldConfigInterface;
use Drupal\mars_google_analytics\DataCollector\TermsDataCollector;
use Drupal\mars_google_analytics\Entity\EntityDecorator;
use Drupal\mars_google_analytics\Entity\EntityManagerWrapper;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\VocabularyInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\mars_google_analytics\DataCollector\TermsDataCollector
 * @group mars
 * @group mars_google_analytics
 */
class TermsDataCollectorTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \Drupal\mars_google_analytics\DataCollector\TermsDataCollector
   */
  private $collector;

  /**
   * Mock.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $containerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_google_analytics\Entity\EntityManagerWrapper
   */
  private $entityManagerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_google_analytics\Entity\EntityDecorator
   */
  private $entityDecoratorMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\node\NodeInterface
   */
  private $nodeMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Field\EntityReferenceFieldItemListInterface
   */
  private $fieldItemListMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\field\FieldConfigInterface
   */
  private $fieldConfigMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\taxonomy\Entity\Term
   */
  private $termMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\taxonomy\VocabularyInterface
   */
  private $vocabularyMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);
    $this->collector = new TermsDataCollector(
      $this->entityManagerMock
    );
  }

  /**
   * Test.
   */
  public function testShouldGetDataLayerId() {
    $id = $this->collector->getDataLayerId();
    $this->assertIsString($id);
  }

  /**
   * Test.
   */
  public function testShouldCollect() {
    $this->entityManagerMock
      ->expects($this->once())
      ->method('getRendered')
      ->willReturn($this->entityDecoratorMock);

    $this->entityDecoratorMock
      ->expects($this->once())
      ->method('getEntities')
      ->willReturn([$this->nodeMock]);

    $this->nodeMock
      ->expects($this->once())
      ->method('getFieldDefinitions')
      ->willReturn([
        'field_name' => $this->fieldConfigMock,
      ]);

    $this->fieldConfigMock
      ->expects($this->once())
      ->method('getType')
      ->willReturn('entity_reference');

    $this->fieldConfigMock
      ->expects($this->once())
      ->method('getSetting')
      ->willReturn('taxonomy_term');

    $this->fieldItemListMock
      ->expects($this->once())
      ->method('referencedEntities')
      ->willReturn([$this->termMock]);

    $this->termMock
      ->expects($this->once())
      ->method('referencedEntities')
      ->willReturn([
        $this->vocabularyMock,
      ]);

    $this->vocabularyMock
      ->expects($this->once())
      ->method('label')
      ->willReturn('label');

    $this->termMock
      ->expects($this->any())
      ->method('label')
      ->willReturn('label');

    $this->nodeMock
      ->expects($this->once())
      ->method('get')
      ->willReturn($this->fieldItemListMock);

    $this->collector->collect();
  }

  /**
   * Test.
   */
  public function testShouldGetGaData() {
    $this->entityManagerMock
      ->expects($this->once())
      ->method('getRendered')
      ->willReturn($this->entityDecoratorMock);

    $this->entityDecoratorMock
      ->expects($this->once())
      ->method('getEntities')
      ->willReturn([$this->nodeMock]);

    $this->nodeMock
      ->expects($this->once())
      ->method('getFieldDefinitions')
      ->willReturn([
        'field_name' => $this->fieldConfigMock,
      ]);

    $this->fieldConfigMock
      ->expects($this->once())
      ->method('getType')
      ->willReturn('entity_reference');

    $this->fieldConfigMock
      ->expects($this->once())
      ->method('getSetting')
      ->willReturn('taxonomy_term');

    $this->fieldItemListMock
      ->expects($this->once())
      ->method('referencedEntities')
      ->willReturn([$this->termMock]);

    $this->termMock
      ->expects($this->once())
      ->method('referencedEntities')
      ->willReturn([
        $this->vocabularyMock,
      ]);

    $this->vocabularyMock
      ->expects($this->once())
      ->method('label')
      ->willReturn('label');

    $this->termMock
      ->expects($this->any())
      ->method('label')
      ->willReturn('label');

    $this->nodeMock
      ->expects($this->once())
      ->method('get')
      ->willReturn($this->fieldItemListMock);

    $this->collector->collect();
    $ga_data = $this->collector->getGaData();
    $this->assertNotEmpty($ga_data);
  }

  /**
   * Test.
   */
  public function testShouldGetAndAddLoadedTerms() {
    $this->collector->addLoadedTerms(
      'vocabulary_id',
      'term_label'
    );
    $terms = $this->collector->getLoadedTerms();
    $this->assertNotEmpty($terms);
    $this->assertSame(
      'term_label',
      $terms['vocabulary_id']['term_label']
    );
  }

  /**
   * Create all mocks for tests in this file.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->entityManagerMock = $this->createMock(EntityManagerWrapper::class);
    $this->nodeMock = $this->createMock(NodeInterface::class);
    $this->entityDecoratorMock = $this->createMock(EntityDecorator::class);
    $this->fieldItemListMock = $this->createMock(EntityReferenceFieldItemListInterface::class);
    $this->fieldConfigMock = $this->createMock(FieldConfigInterface::class);
    $this->termMock = $this->createMock(Term::class);
    $this->vocabularyMock = $this->createMock(VocabularyInterface::class);
  }

}
