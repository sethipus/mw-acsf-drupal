<?php

namespace Drupal\Tests\mars_content_hub\Unit\EventSubscriber\EnqueueEntity;

use Acquia\ContentHubClient\Settings;
use Drupal\acquia_contenthub_publisher\ContentHubPublisherEvents;
use Drupal\mars_content_hub\Event\ContentHubEntityEligibilityEvent;
use Drupal\Core\Entity\EntityInterface;
use Drupal\mars_content_hub\EventSubscriber\EnqueueEntity\EnqueueProducts;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\mars_content_hub\EventSubscriber\EnqueueEntity\EnqueueProducts
 * @group mars
 * @group mars_content_hub
 */
class EnqueueProductsTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \Drupal\mars_content_hub\EventSubscriber\EnqueueEntity\EnqueueProducts
   */
  private $enqueueProducts;

  /**
   * Mock.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $containerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Acquia\ContentHubClient\Settings
   */
  private $settingsMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityInterface
   */
  private $entityMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\acquia_contenthub_publisher\Event\ContentHubEntityEligibilityEvent
   */
  private $contentHubEntityEligibilityEventMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);
    $this->enqueueProducts = new EnqueueProducts(
      $this->settingsMock
    );
  }

  /**
   * Test.
   */
  public function testShouldGetSubscribedEvents() {
    $events = $this->enqueueProducts::getSubscribedEvents();
    $this->assertIsArray($events);
    $this->assertNotEmpty($events);
    $this->assertArrayHasKey(ContentHubPublisherEvents::ENQUEUE_CANDIDATE_ENTITY, $events);
  }

  /**
   * Test.
   */
  public function testShouldOnEnqueueCandidateEntity() {
    $this->contentHubEntityEligibilityEventMock
      ->expects($this->once())
      ->method('getEntity')
      ->willReturn($this->entityMock);

    $this->entityMock
      ->expects($this->once())
      ->method('bundle')
      ->willReturn('product');

    $this->contentHubEntityEligibilityEventMock
      ->expects($this->once())
      ->method('setEligibility');

    $this->contentHubEntityEligibilityEventMock
      ->expects($this->once())
      ->method('stopPropagation');

    $this->enqueueProducts->onEnqueueCandidateEntity(
      $this->contentHubEntityEligibilityEventMock
    );
  }

  /**
   * Create all mocks for tests in this file.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->settingsMock = $this->createMock(Settings::class);
    $this->entityMock = $this->createMock(EntityInterface::class);
    $this->contentHubEntityEligibilityEventMock = $this->createMock(\Drupal\acquia_contenthub_publisher\Event\ContentHubEntityEligibilityEvent::class);
  }

}

namespace Drupal\acquia_contenthub_publisher\Event;

use Drupal\Core\Entity\EntityInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event fired for eligibility of an entity to POST to ContentHub.
 *
 * Subscribers to this event should manipulate $this->setEligibility() to
 * prevent entities from being considered eligible for ContentHub POSTing.
 * Entities are considered eligible by default.
 *
 * @see \Drupal\acquia_contenthub_publisher\ContentHubPublisherEvents
 */
class ContentHubEntityEligibilityEvent extends Event {

  /**
   * The entity being evaluated for eligibility.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $entity;

  /**
   * The operation being performed.
   *
   * Should be a value of "insert" or "update".
   *
   * @var string
   */
  protected $operation;

  /**
   * Whether the entity is eligible for the ContentHub queue.
   *
   * @var bool
   */
  protected $eligibility = TRUE;

  /**
   * Whether the entity should go through a full dependency calculation.
   *
   * @var bool
   */
  protected $calculateDependencies = TRUE;

  /**
   * ContentHubEntityEligibilityEvent constructor.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity being evaluated for eligibility.
   * @param string $op
   *   Whether this is being inserted or updated.
   */
  public function __construct(EntityInterface $entity, $op) {
    $this->entity = $entity;
    $this->operation = $op;
  }

  /**
   * The entity being evaluated for eligibility.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The entity that is being evaluated for eligibility.
   */
  public function getEntity() {
    return $this->entity;
  }

  /**
   * The operation being performed.
   *
   * @return string
   *   The operation identifier that's being performed.
   */
  public function getOperation() {
    return $this->operation;
  }

  /**
   * Whether the entity is eligible for the ContentHub queue.
   *
   * @return bool
   *   TRUE if eligible; FALSE otherwise.
   */
  public function getEligibility(): bool {
    return $this->eligibility;
  }

  /**
   * Set the eligibility of this entity for the ContentHub queue.
   *
   * @param bool $eligible
   *   TRUE if eligible; FALSE otherwise.
   *
   * @throws \Exception
   */
  public function setEligibility($eligible) {
    if (!is_bool($eligible)) {
      throw new \Exception("Eligibility must be a boolean value.");
    }
    $this->eligibility = $eligible;
  }

  /**
   * Whether the entity should go through a full dependency calculation.
   *
   * @return bool
   *   TRUE if full dependency calculation should be performed; FALSE otherwise.
   */
  public function getCalculateDependencies(): bool {
    return $this->calculateDependencies;
  }

  /**
   * Set whether the entity should go through a full dependency calculation.
   *
   * @param bool $calculate_dependencies
   *   TRUE if full dependency calculation should be performed; FALSE otherwise.
   *
   * @throws \Exception
   */
  public function setCalculateDependencies($calculate_dependencies) {
    if (!is_bool($calculate_dependencies)) {
      throw new \Exception("calculateDependencies must be a boolean value.");
    }
    $this->calculateDependencies = $calculate_dependencies;
  }

}
