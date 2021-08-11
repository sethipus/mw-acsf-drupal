<?php

namespace Drupal\Tests\mars_common\Unit\Controller;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\PagerSelectExtender;
use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Database\Query\TableSortExtender;
use Drupal\Core\Database\StatementInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\mars_common\Controller\PollController;
use Drupal\node\Entity\Node;
use Drupal\poll\Entity\Poll;
use Drupal\Tests\UnitTestCase;
use Drupal\user\UserInterface;
use Drupal\user\UserStorage;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\mars_common\Controller\PollController
 * @group mars
 * @group mars_common
 */
class PollControllerTest extends UnitTestCase {

  const TEST_OPTION_KEY = 'option_1';

  const TEST_OPTIONS = [
    self::TEST_OPTION_KEY => 'Test option 1',
    'option_2' => 'Test option 1',
  ];

  const TEST_TITLE = 'test_title';
  const TEST_HOSTNAME = 'test_hostname';
  const EXISTING_USER_ID = 1;
  const NOT_EXISTING_USER_ID = 2;

  /**
   * System under test.
   *
   * @var \Drupal\mars_common\Controller\PollController
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
   * @var \Drupal\Core\Database\Connection|\PHPUnit\Framework\MockObject\MockObject
   */
  private $connectionMock;

  /**
   * Mock.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $routeMatchMock;

  /**
   * Mock.
   *
   * @var \Drupal\user\UserStorage|\PHPUnit\Framework\MockObject\MockObject
   */
  private $userStorageMock;

  /**
   * Mock.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $languageManagerMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);
    $this->controller = new PollController(
      $this->connectionMock,
      $this->routeMatchMock,
      $this->userStorageMock,
      $this->languageManagerMock
    );

    $this->controller->setStringTranslation($this->getStringTranslationStub());
  }

  /**
   * Test.
   */
  public function testShouldInstantiateProperly() {
    $entityManagerMock = $this->createMock(EntityTypeManagerInterface::class);
    $entityManagerMock
      ->expects($this->any())
      ->method('getStorage')
      ->willReturn($this->userStorageMock);

    $this->containerMock
      ->expects($this->exactly(4))
      ->method('get')
      ->willReturnMap(
        [
          [
            'database',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->connectionMock,
          ],
          [
            'current_route_match',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->routeMatchMock,
          ],
          [
            'entity_type.manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $entityManagerMock,
          ],
          [
            'language_manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->languageManagerMock,
          ],
        ]
      );
    $this->controller::create($this->containerMock);
  }

  /**
   * Test.
   */
  public function testShouldBuildResultsViewProperly() {
    $build = $this->controller->buildResultsView();
    $this->assertArrayHasKey('summary', $build);
    $this->assertIsArray($build['summary']);
    $this->assertArrayHasKey('submissions', $build);
    $this->assertIsArray($build['submissions']);
    $submission_rows = $build['submissions']['table']['#rows'];
    // Build array should have our mocked poll data created by the mocked user.
    $this->assertEquals(self::TEST_HOSTNAME, $submission_rows[0]['data']['hostname']);
    $this->assertEquals(self::TEST_OPTIONS[self::TEST_OPTION_KEY], $submission_rows[0]['data']['choice']);
  }

  /**
   * Test.
   */
  public function testShouldReturnPollTitleProperly() {
    $title = $this->controller->getResultsViewTitle($this->createEntityMock())->__toString();
    $expected_title = 'Results of <em class="placeholder">' . self::TEST_TITLE . '</em>';
    $this->assertEquals($expected_title, $title);
  }

  /**
   * Create all mocks for tests in this file.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->connectionMock = $this->createMock(Connection::class);
    $this->connectionMock
      ->expects($this->any())
      ->method('select')
      ->willReturn($this->createQueryMock());

    $this->routeMatchMock = $this->createMock(RouteMatchInterface::class);
    $this->routeMatchMock
      ->expects($this->any())
      ->method('getParameter')
      ->willReturn($this->createPollMock());

    $userMock = $this->createMock(UserInterface::class);
    $userMock
      ->expects($this->any())
      ->method('getAccountName')
      ->willReturn('test');
    $this->userStorageMock = $this->createMock(UserStorage::class);
    $this->userStorageMock
      ->expects($this->any())
      ->method('load')
      ->willReturnMap([
        [self::EXISTING_USER_ID, $userMock],
        [self::NOT_EXISTING_USER_ID, NULL],
      ]);

    $languageMock = $this->createMock(Language::class);
    $languageMock
      ->expects($this->any())
      ->method('getId')
      ->willReturn('en');
    $this->languageManagerMock = $this->createMock(LanguageManagerInterface::class);
    $this->languageManagerMock
      ->expects($this->any())
      ->method('getCurrentLanguage')
      ->willReturn($languageMock);
  }

  /**
   * Create a poll entity mock.
   *
   * @return \Drupal\poll\Entity\Poll|\PHPUnit\Framework\MockObject\MockObject
   *   Mock poll object.
   */
  private function createPollMock() {
    $pollMock = $this->getMockBuilder(Poll::class)
      ->disableOriginalConstructor()
      ->getMock();;
    $pollMock
      ->expects($this->any())
      ->method('id')
      ->willReturn(1);
    $pollMock
      ->expects($this->any())
      ->method('getOptions')
      ->willReturn(self::TEST_OPTIONS);

    return $pollMock;
  }

  /**
   * Create a content entity mock.
   *
   * @return \Drupal\Core\Entity\ContentEntityBase|\PHPUnit\Framework\MockObject\MockObject
   *   Mock content entity object.
   */
  private function createEntityMock() {
    $fieldMock = $this->getMockBuilder(FieldItemListInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $fieldMock
      ->expects($this->any())
      ->method('__get')
      ->with('value')
      ->willReturn(self::TEST_TITLE);

    $entityMock = $this->getMockBuilder(Node::class)
      ->disableOriginalConstructor()
      ->getMock();
    $entityMock
      ->expects($this->any())
      ->method('get')
      ->willReturn($fieldMock);

    return $entityMock;
  }

  /**
   * Create mocks associated with queries.
   *
   * @return \Drupal\Core\Database\Query\SelectInterface|\PHPUnit\Framework\MockObject\MockObject
   *   Mock query object.
   */
  private function createQueryMock() {
    $statementMock = $this->createMock(StatementInterface::class);
    $queryMock = $this->createMock(SelectInterface::class);

    $queryMock->expects($this->any())
      ->method('condition')
      ->willReturn($queryMock);
    $queryMock->expects($this->any())
      ->method('countQuery')
      ->willReturn($queryMock);
    $queryMock->expects($this->any())
      ->method('fields')
      ->willReturn($queryMock);
    $queryMock->expects($this->any())
      ->method('execute')
      ->withConsecutive(
        [], [], [], []
      )
      ->will($this->onConsecutiveCalls($statementMock, $statementMock, $statementMock, $this->createMockQueryResult()));

    $pagerSelectExtenderMock = $this->createMock(PagerSelectExtender::class);
    $pagerSelectExtenderMock->expects($this->any())
      ->method('limit')
      ->willReturn($queryMock);

    $tableSelectExtenderMock = $this->createMock(TableSortExtender::class);
    $tableSelectExtenderMock->expects($this->any())
      ->method('orderByHeader')
      ->willReturn($queryMock);

    $queryMock->expects($this->any())
      ->method('extend')
      ->willReturnMap([
        [TableSortExtender::class, $tableSelectExtenderMock],
        [PagerSelectExtender::class, $pagerSelectExtenderMock],
      ]);

    return $queryMock;
  }

  /**
   * Create an array with mock poll results.
   *
   * @return \stdClass[]
   *   Array of results.
   */
  private function createMockQueryResult() {
    $row_1 = new \stdClass();
    $row_1->uid = self::EXISTING_USER_ID;
    $row_1->hostname = self::TEST_HOSTNAME;
    $row_1->chid = self::TEST_OPTION_KEY;
    $row_1->timestamp = 1608660167;

    $row_2 = new \stdClass();
    $row_2->uid = self::NOT_EXISTING_USER_ID;
    $row_2->hostname = 'test';
    $row_2->chid = 'test';
    $row_2->timestamp = 1608660167;

    return [$row_1, $row_2];
  }

}
