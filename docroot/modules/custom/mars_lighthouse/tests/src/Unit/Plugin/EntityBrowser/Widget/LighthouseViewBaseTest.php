<?php

namespace Drupal\Tests\mars_lighthouse\Unit\Plugin\EntityBrowser\Widget;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Pager\PagerManagerInterface;
use Drupal\entity_browser\WidgetValidationManager;
use Drupal\mars_lighthouse\LighthouseInterface;
use Drupal\media\MediaInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class LighthouseViewBaseTest.
 *
 * @package Drupal\Tests\mars_lighthouse\Unit\Plugin\EntityBrowser\Widget
 */
abstract class LighthouseViewBaseTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \Drupal\entity_browser\WidgetInterface
   */
  protected $viewClass;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected $containerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Form\FormStateInterface
   */
  protected $formStateMock;

  /**
   * Test widget configuration.
   *
   * @var array
   */
  protected $configuration;

  /**
   * Lighthouse adapter mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_lighthouse\LighthouseInterface
   */
  protected $lighthouseAdapterMock;

  /**
   * Page manager.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Pager\PagerManagerInterface
   */
  protected $pageManagerMock;

  /**
   * Current request.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\HttpFoundation\RequestStack
   */
  protected $currentRequestMock;

  /**
   * Event dispatcher service.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcherMock;

  /**
   * Entity type manager service.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManagerMock;

  /**
   * The Widget Validation Manager service.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\entity_browser\WidgetValidationManager
   */
  protected $validationManagerMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->createMocks();
    $this->currentRequestMock->expects($this->any())->method('getCurrentRequest')->willReturn(new Request());
    $this->containerMock->set('request_stack', $this->currentRequestMock);
    \Drupal::setContainer($this->containerMock);
    $this->configuration = ['entity_browser_id' => 'media_library'];
  }

  /**
   * Test.
   *
   * @test
   */
  public function testShouldInstantiateProperly() {
    $this->containerMock
      ->expects($this->exactly(7))
      ->method('get')
      ->willReturnMap(
        [
          [
            'lighthouse.adapter',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->lighthouseAdapterMock,
          ],
          [
            'pager.manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->pageManagerMock,
          ],
          [
            'event_dispatcher',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->eventDispatcherMock,
          ],
          [
            'entity_type.manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->entityTypeManagerMock,
          ],
          [
            'plugin.manager.entity_browser.widget_validation',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->validationManagerMock,
          ],
          [
            'request_stack',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->currentRequestMock,
          ],
        ]
      );
  }

  /**
   * Test get form.
   *
   * @test
   *
   * @covers \Drupal\mars_lighthouse\Plugin\EntityBrowser\Widget\LighthouseView::getForm
   * @covers \Drupal\mars_lighthouse\Plugin\EntityBrowser\Widget\LighthouseVideoView::getForm
   */
  public function testGetForm() {
    $form = [];
    $this->formStateMock->expects($this->any())
      ->method('getValue')
      ->willReturn(
      'test'
    );
    $form = $this->viewClass->getForm($form, $this->formStateMock, []);
    $this->assertIsArray($form);
    $this->assertNotEmpty($form);
    $this->assertCount(4, $form);
    $this->assertArrayHasKey('actions', $form);
    $this->assertArrayHasKey('#attached', $form);
    $this->assertArrayHasKey('filter', $form);
    $this->assertArrayHasKey('view', $form);
  }

  /**
   * Test form submit.
   *
   * @test
   *
   * @covers \Drupal\mars_lighthouse\Plugin\EntityBrowser\Widget\LighthouseView::submit
   * @covers \Drupal\mars_lighthouse\Plugin\EntityBrowser\Widget\LighthouseVideoView::submit
   */
  public function testSubmitNotFail() {
    $form = [];
    $element = [];
    $this->lighthouseAdapterMock->expects($this->any())
      ->method('getMediaEntity')
      ->willReturn(
        $this->createMock(MediaInterface::class),
      );
    $this->formStateMock->expects($this->any())
      ->method('getUserInput')
      ->willReturn(
        ['view' => 1]
      );
    $this->formStateMock->expects($this->any())
      ->method('cleanValues')
      ->willReturn(
        $this->formStateMock
      );
    $this->formStateMock->expects($this->any())
      ->method('get')
      ->willReturn(
        [],
      );
    $this->assertEmpty($this->viewClass->submit($element, $form, $this->formStateMock));
  }

  /**
   * Create all mocks for tests.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->formStateMock = $this->createMock(FormStateInterface::class);
    $this->lighthouseAdapterMock = $this->createMock(LighthouseInterface::class);
    $this->pageManagerMock = $this->createMock(PagerManagerInterface::class);
    $this->currentRequestMock = $this->createMock(RequestStack::class);
    $this->eventDispatcherMock = $this->createMock(EventDispatcherInterface::class);
    $this->entityTypeManagerMock = $this->createMock(EntityTypeManagerInterface::class);
    $this->validationManagerMock = $this->createMock(WidgetValidationManager::class);
  }

}
