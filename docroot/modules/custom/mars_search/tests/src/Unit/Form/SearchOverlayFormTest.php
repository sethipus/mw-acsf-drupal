<?php

namespace Drupal\Tests\mars_search\Unit\Form;

use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Path\PathValidatorInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_search\Form\SearchOverlayForm;
use Drupal\mars_search\Processors\SearchHelperInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @coversDefaultClass \Drupal\mars_search\Form\SearchOverlayForm
 * @group mars
 * @group mars_search
 */
class SearchOverlayFormTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_search\Form\SearchForm
   */
  private $form;

  /**
   * Container mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\DependencyInjection\ContainerInterface
   */
  private $containerMock;

  /**
   * Form state mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Form\FormStateInterface
   */
  private $formStateMock;

  /**
   * Request stack mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\HttpFoundation\RequestStack
   */
  private $requestStackMock;

  /**
   * Url mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Url
   */
  private $urlMock;

  /**
   * String translation mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\StringTranslation\TranslationInterface
   */
  private $translationMock;

  /**
   * Path validator mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Path\PathValidatorInterface
   */
  private $pathValidatorMock;

  /**
   * Language helper mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_common\LanguageHelper
   */
  private $languageHelper;

  /**
   * Config factory mock.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $configMock;

  /**
   * Search helper mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_search\Processors\SearchHelperInterface
   */
  private $searchHelperMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);

    $this->form = new SearchOverlayForm(
      $this->requestStackMock,
      $this->languageHelper,
      $this->configMock,
      $this->searchHelperMock
    );
  }

  /**
   * Test instantiation.
   */
  public function testShouldInstantiateProperly() {
    $this->containerMock
      ->expects($this->exactly(4))
      ->method('get')
      ->willReturnMap(
        [
          [
            'request_stack',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->requestStackMock,
          ],
          [
            'mars_common.language_helper',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->languageHelper,
          ],
          [
            'config.factory',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->configMock,
          ],
          [
            'mars_search.search_helper',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->searchHelperMock,
          ],
        ]
      );

    $this->form::create(
      $this->containerMock
    );
  }

  /**
   * Test form id.
   */
  public function testFormId() {
    $id = $this->form->getFormId();
    $this->assertEquals('mars_search_overlay_form', $id);
  }

  /**
   * Test build method.
   */
  public function testShouldBuild() {
    $form = [];

    $this->containerMock
      ->expects($this->any())
      ->method('get')
      ->willReturnMap(
        [
          [
            'string_translation',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->translationMock,
          ],
        ]
      );

    $this->languageHelper
      ->expects($this->exactly(3))
      ->method('translate');

    $this->configMock
      ->expects($this->any())
      ->method('get')
      ->willReturn($this->createMock(Config::class));

    $form = $this->form->buildForm(
      $form,
      $this->formStateMock
    );
    $this->assertContains('mars_search/autocomplete', $form['#attached']['library']);
    $this->assertArrayHasKey('search', $form);
    $this->assertArrayHasKey('submit', $form['actions']);
  }

  /**
   * Test build method.
   */
  public function testShouldFormSubmit() {
    $form = [];

    $this->formStateMock
      ->expects($this->once())
      ->method('getValue')
      ->with('search')
      ->willReturn('search_string');

    $this->containerMock
      ->expects($this->any())
      ->method('get')
      ->willReturnMap(
        [
          [
            'path.validator',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->pathValidatorMock,
          ],
        ]
      );
    $this->pathValidatorMock
      ->expects($this->once())
      ->method('getUrlIfValidWithoutAccessCheck')
      ->willReturn($this->urlMock);
    $this->urlMock
      ->expects($this->any())
      ->method('getOptions')
      ->willReturn([]);
    $this->urlMock
      ->expects($this->any())
      ->method('setOptions');
    $this->urlMock
      ->expects($this->any())
      ->method('toString')
      ->willReturn('/example');
    $response = new RedirectResponse('/example');
    $this->formStateMock
      ->expects($this->once())
      ->method('setResponse')
      ->with($response);

    $this->form->submitForm($form, $this->formStateMock);
  }

  /**
   * Create all mocks for tests.
   */
  private function createMocks(): void {
    $this->requestStackMock = $this->createMock(RequestStack::class);
    $this->urlMock = $this->createMock(Url::class);
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->formStateMock = $this->createMock(FormStateInterface::class);
    $this->translationMock = $this->createMock(TranslationInterface::class);
    $this->pathValidatorMock = $this->createMock(PathValidatorInterface::class);
    $this->languageHelper = $this->createMock(LanguageHelper::class);
    $this->configMock = $this->createMock(ConfigFactoryInterface::class);
    $this->searchHelperMock = $this->createMock(SearchHelperInterface::class);
  }

}
