<?php

namespace Drupal\Tests\mars_search\Unit\Plugin\Block;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\mars_search\Plugin\Block\SearchFaqBlock;
use Drupal\mars_search\SearchHelperInterface;
use Drupal\mars_search\SearchQueryParserInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\mars_search\Plugin\Block\SearchFaqBlock
 * @group mars
 * @group mars_common
 */
class SearchFaqBlockTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \Drupal\mars_search\Plugin\Block\SearchFaqBlock
   */
  private $block;

  /**
   * Mock.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $containerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Form\FormStateInterface
   */
  private $formStateMock;

  /**
   * Search helper.
   *
   * @var \Drupal\mars_search\SearchHelperInterface
   */
  private $searchHelperMock;

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  private $formBuilderMock;

  /**
   * Search query parser.
   *
   * @var \Drupal\mars_search\SearchQueryParserInterface
   */
  private $searchQueryParserMock;

  /**
   * Mars Search logger channel.
   *
   * @var \Psr\Log\LoggerInterface
   */
  private $loggerMock;

  /**
   * Mars Search logger channel.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  private $loggerFactoryMock;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  private $configFactoryMock;

  /**
   * Test block configuration.
   *
   * @var array
   */
  private $configuration;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);
    $this->configuration = [
      'faq_title' => 'FAQ block title',
    ];
    $definitions = [
      'provider'    => 'test',
      'admin_label' => 'test',
    ];

    $this->block = new SearchFaqBlock(
      $this->configuration,
      'list_block',
      $definitions,
      $this->searchHelperMock,
      $this->formBuilderMock,
      $this->searchQueryParserMock,
      $this->loggerMock,
      $this->configFactoryMock
    );
  }

  /**
   * Test.
   */
  public function testShouldInstantiateProperly() {
    $this->loggerFactoryMock
      ->expects($this->exactly(1))
      ->method('get')
      ->willReturn($this->loggerMock);
    $this->containerMock
      ->expects($this->exactly(5))
      ->method('get')
      ->willReturnMap(
        [
          [
            'mars_search.search_helper',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->searchHelperMock,
          ],
          [
            'form_builder',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->formBuilderMock,
          ],
          [
            'mars_search.search_query_parser',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->searchQueryParserMock,
          ],
          [
            'logger.factory',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->loggerFactoryMock,
          ],
          [
            'config.factory',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->configFactoryMock,
          ],
        ]
      );

    $this->block::create(
      $this->containerMock,
      $this->configuration,
      'search_faq_block',
      [
        'provider'    => 'test',
        'admin_label' => 'test',
      ],
    );
  }

  /**
   * Create all mocks for tests.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->formStateMock = $this->createMock(FormStateInterface::class);
    $this->searchHelperMock = $this->createMock(SearchHelperInterface::class);
    $this->formBuilderMock = $this->createMock(FormBuilderInterface::class);;
    $this->searchQueryParserMock = $this->createMock(SearchQueryParserInterface::class);;
    $this->loggerMock = $this->createMock(LoggerChannelInterface::class);
    $this->loggerFactoryMock = $this->createMock(LoggerChannelFactoryInterface::class);
    $this->configFactoryMock = $this->createMock(ConfigFactoryInterface::class);;
  }

}
