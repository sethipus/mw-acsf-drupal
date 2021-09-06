<?php

namespace Drupal\Tests\mars_search\Unit\Plugin\Block;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\Context\Context;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\mars_search\Plugin\Block\SearchGridBlock;
use Drupal\mars_search\Processors\SearchBuilder;
use Drupal\mars_search\Processors\SearchHelperInterface;
use Drupal\mars_search\Processors\SearchCategoriesInterface;
use Drupal\mars_search\SearchProcessFactoryInterface;
use Drupal\node\Entity\Node;
use Drupal\pathauto\AliasCleanerInterface;
use Drupal\taxonomy\TermInterface;
use Drupal\taxonomy\TermStorageInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\mars_search\Plugin\Block\SearchGridBlock
 * @group mars
 * @group mars_search
 */
class SearchGridBlockTest extends UnitTestCase {

  const BLOCK_CONFIGURATION = [
    'title' => 'Test grid title',
    'grid_id' => 'test_grid_id',
  ];

  /**
   * System under test.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_search\Plugin\Block\SearchGridBlock
   */
  private $block;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\DependencyInjection\ContainerInterface
   */
  private $containerMock;

  /**
   * Search builder mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_search\Processors\SearchBuilderInterface
   */
  private $searchBuilderMock;

  /**
   * Search helper mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_search\Processors\SearchHelperInterface
   */
  private $searchHelperMock;

  /**
   * Test block configuration.
   *
   * @var array
   */
  private $configuration;

  /**
   * Search process factory mock.
   *
   * @var \Drupal\mars_search\SearchProcessFactoryInterface
   */
  private $searchProcessFactoryMock;

  /**
   * Search categories mock.
   *
   * @var \Drupal\mars_search\SearchCategoriesInterface
   */
  private $searchCategoriesMock;

  /**
   * Language helper mock.
   *
   * @var \Drupal\mars_common\LanguageHelper
   */
  private $languageHelperMock;

  /**
   * Theme configurator mock.
   *
   * @var \Drupal\mars_common\ThemeConfiguratorParser
   */
  private $themeConfiguratorMock;

  /**
   * Entity type manager mock.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManagerMock;

  /**
   * Form state mock.
   *
   * @var \Drupal\Core\Form\FormStateInterface
   */
  private $formStateMock;

  /**
   * Language helper mock.
   *
   * @var \Drupal\pathauto\AliasCleanerInterface
   */
  private $aliasCleanerMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);
    $this->configuration = self::BLOCK_CONFIGURATION;
    $definitions = [
      'provider'    => 'test',
      'admin_label' => 'test',
    ];

    $this->searchProcessFactoryMock
      ->expects($this->any())
      ->method('getProcessManager')
      ->willReturnMap([
        [
          'search_builder',
          $this->searchBuilderMock,
        ],
        [
          'search_helper',
          $this->searchBuilderMock,
        ],
        [
          'search_categories',
          $this->searchCategoriesMock,
        ],
      ]);

    $this->block = new SearchGridBlock(
      $this->configuration,
      'search_grid_block',
      $definitions,
      $this->entityTypeManagerMock,
      $this->themeConfiguratorMock,
      $this->searchProcessFactoryMock,
      $this->languageHelperMock,
      $this->aliasCleanerMock
    );
  }

  /**
   * Test.
   */
  public function testShouldInstantiateProperly() {
    $this->containerMock
      ->expects($this->exactly(5))
      ->method('get')
      ->willReturnMap(
        [
          [
            'entity_type.manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->entityTypeManagerMock,
          ],
          [
            'mars_common.theme_configurator_parser',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->themeConfiguratorMock,
          ],
          [
            'mars_search.search_factory',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->searchProcessFactoryMock,
          ],
          [
            'mars_common.language_helper',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->languageHelperMock,
          ],
          [
            'pathauto.alias_cleaner',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->aliasCleanerMock,
          ],
        ]
      );

    $this->block::create(
      $this->containerMock,
      $this->configuration,
      'search_grid_block',
      [
        'provider'    => 'test',
        'admin_label' => 'test',
      ],
    );
  }

  /**
   * Test block build.
   */
  public function testBuild() {
    // Mock node context.
    $node = $this->getMockBuilder(Node::class)
      ->disableOriginalConstructor()
      ->getMock();
    $node
      ->expects($this->once())
      ->method('id');
    $nodeContext = $this->getMockBuilder(Context::class)
      ->disableOriginalConstructor()
      ->getMock();
    $nodeContext->expects($this->once())
      ->method('getContextValue')
      ->willReturn($node);
    $this->block->setContext('node', $nodeContext);

    $this->languageHelperMock
      ->expects($this->any())
      ->method('translate');

    $this->aliasCleanerMock
      ->expects($this->once())
      ->method('cleanString')
      ->willReturn('test_grid_id');

    $this->searchBuilderMock
      ->expects($this->once())
      ->method('buildSearchResults')
      ->with('grid', $this->block->getConfiguration(), 'test_grid_id')
      ->willReturn([
        [
          'limit' => 1,
          'keys' => 'test_results_key',
        ],
        [
          'resultsCount' => 1,
        ],
        [
          '#items' => [],
        ],
      ]);
    $this->searchBuilderMock
      ->expects($this->once())
      ->method('buildSearchFacets')
      ->with('grid', $this->block->getConfiguration(), 'test_grid_id')
      ->willReturn([]);

    $this->themeConfiguratorMock
      ->expects($this->once())
      ->method('getGraphicDivider');
    $this->themeConfiguratorMock
      ->expects($this->once())
      ->method('getBrandBorder2');

    $build = $this->block->build();
    $this->assertEquals('mars_search_grid_block', $build['#theme']);
  }

  /**
   * Test max age.
   */
  public function testMaxAge() {
    $this->assertEquals(0, $this->block->getCacheMaxAge());
  }

  /**
   * Test block submit.
   */
  public function testBlockSubmit() {
    $form = [];
    $this->formStateMock
      ->expects($this->once())
      ->method('cleanValues')
      ->willReturn($this->formStateMock);
    $this->formStateMock
      ->expects($this->once())
      ->method('getValues');
    $this->block->blockSubmit($form, $this->formStateMock);
  }

  /**
   * Test block form.
   */
  public function testShouldBlockForm() {
    $form = [];
    $this->languageHelperMock
      ->expects($this->any())
      ->method('translate');
    $this->buildGeneralFiltersTest();
    $blockForm = $this->block->blockForm($form, $this->formStateMock);
    $this->assertArrayHasKey('exposed_filters_wrapper', $blockForm);
    $this->assertArrayHasKey('general_filters', $blockForm);
    $this->assertArrayHasKey('top_results_wrapper', $blockForm);
  }

  /**
   * General filters form test.
   */
  protected function buildGeneralFiltersTest() {
    $storage = $this->getMockBuilder(TermStorageInterface::class)
      ->disableOriginalConstructor()
      ->getMock();

    $term = $this->getMockBuilder(TermInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $term->expects($this->any())
      ->method('id')
      ->willReturn(1);
    $term->expects($this->any())
      ->method('label')
      ->willReturn('Test term');

    $this->entityTypeManagerMock
      ->expects($this->any())
      ->method('getStorage')
      ->willReturn($storage);
    $storage
      ->expects($this->any())
      ->method('loadTree')
      ->willReturn([$term]);

    $this->searchCategoriesMock
      ->expects($this->exactly(2))
      ->method('getCategories')
      ->willReturn(SearchCategoriesInterface::TAXONOMY_VOCABULARIES);

  }

  /**
   * Create all mocks for tests.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->entityTypeManagerMock = $this->createMock(EntityTypeManagerInterface::class);
    $this->searchProcessFactoryMock = $this->createMock(SearchProcessFactoryInterface::class);
    $this->languageHelperMock = $this->createMock(LanguageHelper::class);
    $this->searchBuilderMock = $this->createMock(SearchBuilder::class);
    $this->searchHelperMock = $this->createMock(SearchHelperInterface::class);
    $this->searchCategoriesMock = $this->createMock(SearchCategoriesInterface::class);
    $this->themeConfiguratorMock = $this->createMock(ThemeConfiguratorParser::class);
    $this->formStateMock = $this->createMock(FormStateInterface::class);
    $this->aliasCleanerMock = $this->createMock(AliasCleanerInterface::class);
  }

}
