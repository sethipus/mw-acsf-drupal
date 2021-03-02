<?php

namespace Drupal\Tests\mars_search\Unit\Form;

use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\mars_search\Form\SearchNoResultsSettingsForm;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * @coversDefaultClass \Drupal\mars_search\Form\SearchNoResultsSettingsForm
 * @group mars
 * @group mars_search
 */
class SearchNoResultsSettingsFormTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_search\Form\SearchNoResultsSettingsForm
   */
  private $form;

  /**
   * Form state mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Form\FormStateInterface
   */
  private $formStateMock;

  /**
   * Container mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\DependencyInjection\ContainerInterface
   */
  private $containerMock;

  /**
   * Config factory mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactoryMock;

  /**
   * Config immutable mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Config\Config
   */
  private $configMock;

  /**
   * Translation mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\StringTranslation\TranslationInterface
   */
  private $translationMock;

  /**
   * Messenger mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Messenger\Messenger
   */
  private $messengerMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);

    $this->form = new SearchNoResultsSettingsForm($this->configFactoryMock);
    $this->form->setMessenger($this->messengerMock);
  }

  /**
   * Test form id.
   */
  public function testFormId() {
    $id = $this->form->getFormId();
    $this->assertEquals('mars_search_no_results_settings', $id);
  }

  /**
   * Test form id.
   */
  public function testFormBuild() {
    $form = [];

    $this->containerMock
      ->expects($this->any())
      ->method('get')
      ->willReturnMap([
        [
          'string_translation',
          ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
          $this->translationMock,
        ],
      ]);

    $this->configFactoryMock
      ->expects($this->once())
      ->method('getEditable')
      ->with('mars_search.search_no_results')
      ->willReturn($this->configMock);

    $this->configMock
      ->expects($this->exactly(3))
      ->method('get');

    $form = $this->form->buildForm($form, $this->formStateMock);
    $this->assertArrayHasKey('no_results_heading', $form);
    $this->assertArrayHasKey('no_results_text', $form);
  }

  /**
   * Test form id.
   */
  public function testFormSubmit() {
    $form = [];

    $this->containerMock
      ->expects($this->any())
      ->method('get')
      ->willReturnMap([
        [
          'string_translation',
          ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
          $this->translationMock,
        ],
      ]);

    $this->configFactoryMock
      ->expects($this->once())
      ->method('getEditable')
      ->with('mars_search.search_no_results')
      ->willReturn($this->configMock);

    $this->formStateMock
      ->expects($this->exactly(3))
      ->method('getValue')
      ->withConsecutive(
        ['no_results_heading'],
        ['no_results_heading_empty_str'],
        ['no_results_text']
      );

    $this->configMock
      ->expects($this->exactly(3))
      ->method('set')
      ->willReturn($this->configMock);

    $this->configMock
      ->expects($this->once())
      ->method('save');

    $this->messengerMock
      ->expects($this->once())
      ->method('addStatus');

    $this->form->submitForm($form, $this->formStateMock);
  }

  /**
   * Create all mocks for tests.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->formStateMock = $this->createMock(FormStateInterface::class);
    $this->messengerMock = $this->createMock(MessengerInterface::class);
    $this->configFactoryMock = $this->createMock(ConfigFactoryInterface::class);
    $this->configMock = $this->createMock(Config::class);
    $this->configMock->set('no_results_heading', 'test_heading');
    $this->configMock->set('no_results_text', 'test_text');
  }

}
