<?php

namespace Drupal\Tests\mars_common\Unit;

use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Drupal\Core\Config\Config;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\mars_common\Plugin\Block\FooterBlock;
use Drupal\Core\Entity\EntityStorageInterface;

/**
 * Class FooterBlockTest.
 *
 * @package Drupal\Tests\mars_common\Unit
 * @covers \Drupal\mars_common\Plugin\Block\FooterBlock
 */
class FooterBlockTest extends UnitTestCase {

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
   * Menu link tree mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Menu\MenuLinkTreeInterface
   */
  protected $menuLinkTreeMock;

  /**
   * Entity type manager mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManagerMock;

  /**
   * File storage.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject||\Drupal\Core\Entity\EntityStorageInterface
   */
  protected $fileStorageMock;

  /**
   * Config factory mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject||\Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactoryMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Config\Config
   */
  private $configMock;

  /**
   * Tested footer block.
   *
   * @var \Drupal\mars_common\Plugin\Block\FooterBlock
   */
  private $footerBlock;

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
      'top_footer_menu' => 'top footer menu',
      'legal_links' => 'legal menu links',
      'marketing' => [
        'value' => 'Marketing and copyright text',
        'format' => 'plain_text',
      ],
      'corporate_tout' => [
        'url' => 'http://mars.com',
        'title' => 'Corporate tout text',
      ],
      'social_links_toggle' => TRUE,
      'region_selector_toggle' => TRUE,
    ];
    $definitions = [
      'provider'    => 'test',
      'admin_label' => 'test',
    ];

    $this->entityTypeManagerMock
      ->expects($this->any())
      ->method('getStorage')
      ->withConsecutive(
        [$this->equalTo('menu')],
        [$this->equalTo('file')]
      )
      ->will($this->onConsecutiveCalls($this->menuLinkTreeMock, $this->fileStorageMock));

    // We should create it in test to import different configs.
    $this->footerBlock = new FooterBlock(
      $this->configuration,
      'footer_block',
      $definitions,
      $this->menuLinkTreeMock,
      $this->entityTypeManagerMock,
      $this->configFactoryMock
    );
  }

  /**
   * Create all mocks for tests.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->configFactoryMock = $this->createMock(ConfigFactoryInterface::class);
    $this->menuLinkTreeMock = $this->createMock(MenuLinkTreeInterface::class);
    $this->entityTypeManagerMock = $this->createMock(EntityTypeManagerInterface::class);
    $this->formStateMock = $this->createMock(FormStateInterface::class);
    $this->configMock = $this->createMock(Config::class);
    $this->fileStorageMock = $this->createMock(EntityStorageInterface::class);
  }

  /**
   * Test Block creation.
   */
  public function testBlockShouldInstantiateProperly() {
    $this->containerMock
      ->expects($this->exactly(3))
      ->method('get')
      ->withConsecutive(
        [$this->equalTo('menu.link_tree')],
        [$this->equalTo('entity_type.manager')],
        [$this->equalTo('config.factory')]
      )
      ->will($this->onConsecutiveCalls($this->menuLinkTreeMock, $this->entityTypeManagerMock, $this->configFactoryMock));

    $this->entityTypeManagerMock
      ->expects($this->exactly(2))
      ->method('getStorage')
      ->withConsecutive(
        [$this->equalTo('menu')],
        [$this->equalTo('file')]
      )
      ->will($this->onConsecutiveCalls($this->menuLinkTreeMock, $this->fileStorageMock));

    $definitions = [
      'provider'    => 'test',
      'admin_label' => 'test',
    ];
    $this->footerBlock::create($this->containerMock, $this->configuration, 'footer_block', $definitions);
  }

  /**
   * Test social links.
   */
  public function testSocialLinksPreparedProperly() {
    $fileMock = $this->getMockBuilder(stdClass::class)
      ->setMethods(['createFileUrl'])
      ->getMock();

    $this->fileStorageMock
      ->expects($this->exactly(1))
      ->method('load')
      ->willReturn($fileMock);

    $fileMock
      ->expects($this->any())
      ->method('createFileUrl')
      ->will($this->onConsecutiveCalls('http://mars.com', ''));

    $theme_settings = [
      'social' => [
        [
          'name' => 'name1',
          'link' => 'link.com',
          'icon' => [0],
        ],
        [
          'name' => 'name2',
          'link' => 'link.net',
        ],
      ],
    ];

    $reflection = new \ReflectionClass($this->footerBlock);
    $method = $reflection->getMethod('socialLinks');
    $method->setAccessible(TRUE);

    $social_menu = $method->invokeArgs($this->footerBlock, [$theme_settings]);
    $this->assertCount(2, $social_menu);
    $this->assertArrayHasKey('icon', $social_menu[0]);
    $this->assertEquals('http://mars.com', $social_menu[0]['icon']);
    $this->assertEquals('', $social_menu[1]['icon']);
  }

}
