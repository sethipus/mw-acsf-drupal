<?php

namespace Drupal\Tests\mars_search\Unit\Plugin\Block;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\mars_search\Plugin\Block\SearchFaqBlock;
use Drupal\mars_search\SearchHelperInterface;
use Drupal\mars_search\SearchQueryParserInterface;
use Drupal\node\NodeInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @coversDefaultClass \Drupal\mars_search\Plugin\Block\SearchFaqBlock
 * @group mars
 * @group mars_search
 */
class SearchFaqBlockTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_search\Plugin\Block\SearchFaqBlock
   */
  private $block;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\DependencyInjection\ContainerInterface|\PHPUnit\Framework\MockObject\MockObject
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
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_search\SearchHelperInterface
   */
  private $searchHelperMock;

  /**
   * The form builder.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Form\FormBuilderInterface
   */
  private $formBuilderMock;

  /**
   * Search query parser.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_search\SearchQueryParserInterface
   */
  private $searchQueryParserMock;

  /**
   * Mars Search logger channel.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Psr\Log\LoggerInterface
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
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Config\ConfigFactory
   */
  private $configFactoryMock;

  /**
   * Request.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\HttpFoundation\Request
   */
  private $requestMock;

  /**
   * Renderer mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Render\RendererInterface
   */
  private $rendererMock;

  /**
   * Immutable config mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Config\ImmutableConfig
   */
  private $immutableConfig;

  /**
   * Node mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\node\NodeInterface
   */
  private $nodeMock;

  /**
   * Url mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Url
   */
  private $urlMock;

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
      $this->configFactoryMock,
      $this->rendererMock
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
      ->expects($this->exactly(6))
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
          [
            'renderer',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->rendererMock,
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
   * Test configuration form.
   */
  public function testShouldBuildConfigurationForm() {
    $config_form = $this->block->buildConfigurationForm([], $this->formStateMock);
    $this->assertArrayHasKey('faq_title', $config_form);
  }

  /**
   * Test configuration form submit.
   */
  public function testShouldBlockSubmit() {
    $form_data = [];

    $this->formStateMock
      ->expects($this->once())
      ->method('getValues')
      ->willReturn([]);

    $this->block->blockSubmit(
      $form_data,
      $this->formStateMock
    );
  }

  /**
   * Test block build.
   */
  public function testBuild() {
    $this->block->setConfiguration([
      'faq_title' => 'Test FAQ title',
    ]);

    $this->configFactoryMock
      ->expects($this->once())
      ->method('get')
      ->willReturn($this->immutableConfig);

    $this->immutableConfig
      ->expects($this->exactly(2))
      ->method('get')
      ->willReturnMap([
        [
          'no_results_heading',
          'test_heading with "@keys"',
        ],
        [
          'no_results_text',
          'test_text',
        ],
      ]);

    $this->searchQueryParserMock
      ->expects($this->once())
      ->method('parseQuery')
      ->willReturn([
        'keys' => 'test_key',
      ]);

    $this->requestMock
      ->expects($this->once())
      ->method('get');

    $this->searchHelperMock
      ->expects($this->once())
      ->method('getRequest')
      ->willReturn($this->requestMock);

    // Return 2 nodes, but only 1 as FAQ.
    $this->searchHelperMock
      ->expects($this->exactly(2))
      ->method('getSearchResults')
      ->willReturn(
        [
          'results' => [
            $this->nodeMock,
            $this->nodeMock,
          ],
          'facets' => [
            'faq_filter_topic' => [],
          ],
          'resultsCount' => 3,
          'highlighted_fields' => [
            0 => [
              'field_qa_item_question' => [
                'Test question',
              ],
              'field_qa_item_answer' => [
                'Test answer',
              ],
            ],
          ],
        ]
      );

    $this->nodeMock
      ->expects($this->exactly(2))
      ->method('bundle')
      ->willReturn('faq', 'test');

    $this->searchHelperMock
      ->expects($this->once())
      ->method('getCurrentUrl')
      ->willReturn($this->urlMock);

    $this->urlMock
      ->expects($this->once())
      ->method('getOptions');

    $build = $this->block->build();
    $this->assertEquals('mars_search_faq_block', $build['#theme']);
    $this->assertEquals('Test FAQ title', $build['#faq_title']);
    $this->assertEquals(2, $build['#search_result_counter']);
    $this->assertEquals('test_heading with "test_key"', $build['#no_results_heading']);
    $this->assertEquals('test_text', $build['#no_results_text']);
  }

  /**
   * Create all mocks for tests.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->formStateMock = $this->createMock(FormStateInterface::class);
    $this->searchHelperMock = $this->createMock(SearchHelperInterface::class);
    $this->formBuilderMock = $this->createMock(FormBuilderInterface::class);;
    $this->searchQueryParserMock = $this->createMock(SearchQueryParserInterface::class);
    $this->loggerMock = $this->createMock(LoggerChannelInterface::class);
    $this->loggerFactoryMock = $this->createMock(LoggerChannelFactoryInterface::class);
    $this->configFactoryMock = $this->createMock(ConfigFactoryInterface::class);
    $this->requestMock = $this->createMock(Request::class);
    $this->rendererMock = $this->createMock(RendererInterface::class);
    $this->immutableConfig = $this->createMock(ImmutableConfig::class);
    $this->nodeMock = $this->createMock(NodeInterface::class);
    $this->urlMock = $this->createMock(Url::class);
  }

}
