<?php

namespace Drupal\Tests\mars_search\Unit\Form;

use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_search\Form\SearchForm;
use Drupal\mars_search\Processors\SearchHelperInterface;
use Drupal\mars_search\SearchProcessFactoryInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;
use Drupal\Core\Url;

/**
 * @coversDefaultClass \Drupal\mars_search\Form\SearchForm
 * @group mars
 * @group mars_search
 */
class SearchFormTest extends UnitTestCase {

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
   * Url mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Url
   */
  private $urlMock;

  /**
   * Request mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\HttpFoundation\Request
   */
  private $requestMock;

  /**
   * Search helper mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_search\Processors\SearchHelperInterface
   */
  private $searchHelperMock;

  /**
   * Search process factory mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_search\SearchProcessFactoryInterface
   */
  private $searchProcessFactoryMock;

  /**
   * String translation mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\StringTranslation\TranslationInterface
   */
  private $translationMock;

  /**
   * Parameter bag mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\HttpFoundation\ParameterBag
   */
  private $parameterBagMock;

  /**
   * Language helper mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_common\LanguageHelper
   */
  private $languageHelper;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);

    $this->searchProcessFactoryMock
      ->expects($this->any())
      ->method('getProcessManager')
      ->with('search_helper')
      ->willReturn($this->searchHelperMock);

    $this->form = new SearchForm(
      $this->searchProcessFactoryMock,
      $this->languageHelper
    );
  }

  /**
   * Test instantiation.
   */
  public function testShouldInstantiateProperly() {
    $this->containerMock
      ->expects($this->exactly(2))
      ->method('get')
      ->willReturnMap(
        [
          [
            'mars_search.search_factory',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->searchProcessFactoryMock,
          ],
          [
            'mars_common.language_helper',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->languageHelper,
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
    $this->assertEquals('mars_search_form', $id);
  }

  /**
   * Test build method.
   */
  public function testShouldBuild() {
    $form = [];
    $grid_options = [
      'filters' => [
        'test' => 'test',
        'faq' => TRUE,
      ],
    ];

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

    $this->requestMock
      ->expects($this->once())
      ->method('get')
      ->with(SearchHelperInterface::MARS_SEARCH_SEARCH_KEY)
      ->willReturn($this->parameterBagMock);

    $this->formStateMock
      ->expects($this->once())
      ->method('set')
      ->with('grid_options', $grid_options);

    $form = $this->form->buildForm(
      $form,
      $this->formStateMock,
      TRUE,
      $grid_options
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
      ->method('get')
      ->with('grid_options')
      ->willReturn([
        'grid_id' => 'test_grid_id',
      ]);

    $this->formStateMock
      ->expects($this->once())
      ->method('getValue')
      ->with('search')
      ->willReturn('search_string');

    $this->searchHelperMock
      ->expects($this->once())
      ->method('getCurrentUrl')
      ->willReturn($this->urlMock);

    $this->urlMock
      ->expects($this->once())
      ->method('getOptions');
    $this->urlMock
      ->expects($this->once())
      ->method('setOptions');

    $this->formStateMock
      ->expects($this->once())
      ->method('setRedirectUrl')
      ->with($this->urlMock);

    $this->form->submitForm($form, $this->formStateMock);
  }

  /**
   * Create all mocks for tests.
   */
  private function createMocks(): void {
    $this->requestMock = $this->createMock(Request::class);
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->formStateMock = $this->createMock(FormStateInterface::class);
    $this->translationMock = $this->createMock(TranslationInterface::class);
    $this->searchProcessFactoryMock = $this->createMock(SearchProcessFactoryInterface::class);
    $this->languageHelper = $this->createMock(LanguageHelper::class);
    $this->parameterBagMock = $this->createMock(ParameterBag::class);
    $this->urlMock = $this->createMock(Url::class);
    $this->searchHelperMock = $this->createMock(SearchHelperInterface::class);
    $this->searchHelperMock->request = $this->requestMock;
  }

}
