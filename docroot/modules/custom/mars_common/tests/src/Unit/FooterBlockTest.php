<?php

namespace Drupal\Tests\mars_common\Unit;

use Drupal;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
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
   * Menu link tree mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject
   */
  protected $menuLinkTree;

  /**
   * Entity type manager mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject
   */
  protected $entityTypeManager;

  /**
   * Config factory mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject
   */
  protected $config;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    Drupal::setContainer($this->createMock(ContainerInterface::class));

    $this->menuLinkTree = $this->createMock(MenuLinkTreeInterface::class);
    $this->entityTypeManager = $this->createMock(EntityTypeManagerInterface::class);
    $this->config = $this->createMock(ConfigFactoryInterface::class);

    $theme_config = [
      'logo' =>
        [
          'use_default' => 0,
          'path' => 'profiles/contrib/lightning/lightning.png',
        ],
      'social' => [],
    ];
    $this->config->method('get')
      ->with('emulsifymars.settings')
      ->willReturn($theme_config);

    $menu_entity_mock = $this->createMock(MenuLinkTreeInterface::class);
    $menu_entity_mock->method('transform')->willReturn($menu_entity_mock);
    $menu_entity_mock->method('build')->willReturn([]);

    $menu_storage = $this->createMock(EntityStorageInterface::class)
      ->method('load')
      ->withAnyParameters()
      ->willReturn($menu_entity_mock);
    $this->entityTypeManager->method('getStorage')
      ->with('menu')
      ->willReturn($menu_storage);
  }

  /**
   * Create block obj.
   *
   * @return \Drupal\mars_common\Plugin\Block\FooterBlock
   *   Footer block.
   */
  private function createBlock() {
    $configuration = [
      'top_footer_menu' => 'top-footer',
      'legal_links' => 'footer',
      'marketing' => [],
      'copyright' => [],
      'corporate_tout' => [
        'url' => 'https://www.mars.com',
        'title' => 'Check out more brands from Mars.',
      ],
      'social_links_toggle' => 0,
      'region_selector_toggle' => 0,
    ];

    return new FooterBlock(
      $configuration,
      'footer_block',
      [],
      $this->menuLinkTree,
      $this->entityTypeManager,
      $this->config
    );
  }

  /**
   * Test footer build.
   */
  public function testBuild() {
    $build_assert_keys = [
      '#logo',
      '#social_links',
      '#top_footer_menu',
      '#legal_links',
      '#marketing',
      '#copyright',
      '#corporate_tout',
    ];

    $footer = $this->createBlock();
    $build_array = $footer->build();
    foreach ($build_assert_keys as $assert_key) {
      $this->assertArrayHasKey($assert_key, $build_array);
    }
  }

}
