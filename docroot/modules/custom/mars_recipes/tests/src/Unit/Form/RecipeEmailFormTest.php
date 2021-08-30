<?php

namespace Drupal\Tests\mars_recipes\Unit\Form;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Url;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\mars_recipes\Form\RecipeEmailForm;
use Drupal\node\NodeInterface;
use Drupal\Tests\UnitTestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\mars_recipes\Form\RecipeEmailForm
 * @group mars
 * @group mars_recipe
 */
class RecipeEmailFormTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \Drupal\mars_recipes\Form\RecipeEmailForm
   */
  private $form;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\DependencyInjection\ContainerInterface
   */
  private $containerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\StringTranslation\TranslationInterface
   */
  private $translationMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_common\ThemeConfiguratorParser
   */
  private $themeConfiguratorParserMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Form\FormStateInterface
   */
  private $formStateMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Psr\Log\LoggerInterface
   */
  private $loggerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\node\NodeInterface
   */
  private $recipeMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Url
   */
  private $urlMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Field\FieldItemListInterface
   */
  private $fieldItemListMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Render\RendererInterface
   */
  private $rendererMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Component\Render\MarkupInterface
   */
  private $markupMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);
    $this->form = new RecipeEmailForm(
      $this->themeConfiguratorParserMock
    );
  }

  /**
   * Test.
   */
  public function testShouldInstantiateProperly() {
    $this->containerMock
      ->expects($this->exactly(1))
      ->method('get')
      ->willReturnMap(
        [
          [
            'mars_common.theme_configurator_parser',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->themeConfiguratorParserMock,
          ],
        ]
      );

    $this->form::create($this->containerMock);
  }

  /**
   * Test.
   */
  public function testShouldGetFormId() {
    $form_id = $this->form->getFormId();
    $this->assertSame(
      'recipe_email_form',
      $form_id
    );
  }

  /**
   * Test.
   */
  public function testShouldBuildFormWhenSubmitted() {
    $form = [];
    $this->form->setRecipe($this->recipeMock);
    $this->form->setContextData([]);

    $this->formStateMock
      ->expects($this->exactly(2))
      ->method('get')
      ->willReturnMap(
        [
          [
            'email_form_submitted',
            TRUE,
          ],
          [
            'validation_error',
            FALSE,
          ],
        ]
      );

    $this->themeConfiguratorParserMock
      ->expects($this->once())
      ->method('getUrlForFile')
      ->willReturn($this->urlMock);

    $this->urlMock
      ->expects($this->once())
      ->method('toString')
      ->willReturn('url/url');

    $form = $this->form->buildForm(
      $form,
      $this->formStateMock
    );
    $this->assertIsArray($form);
    $this->assertEquals('recipe_email_final', $form['#theme']);
  }

  /**
   * Test.
   */
  public function testShouldBuildFormWhenNotSubmitted() {
    $form = [];
    $this->form->setRecipe($this->recipeMock);
    $this->form->setContextData([
      'checkboxes_container' => [
        'grocery_list' => 'label',
        'email_recipe' => 'label',
      ],
      'email_address_hint' => 'hint',
      'captcha' => TRUE,
      'cta_title' => 'Submit',
    ]);

    $this->formStateMock
      ->expects($this->any())
      ->method('get')
      ->willReturnMap(
        [
          [
            'email_form_submitted',
            FALSE,
          ],
          [
            'validation_error',
            TRUE,
          ],
        ]
      );

    $this->recipeMock
      ->expects($this->once())
      ->method('get')
      ->willReturn($this->fieldItemListMock);

    $this->fieldItemListMock
      ->expects($this->once())
      ->method('getValue')
      ->willReturn(['array of ingredients']);

    $form = $this->form->buildForm(
      $form,
      $this->formStateMock
    );
    $this->assertIsArray($form);
    $this->assertEquals('recipe_email', $form['#theme']);
    $this->assertNotNull($form['captcha']);
    $this->assertNotNull($form['grocery_list']);
    $this->assertNotNull($form['email_recipe']);
  }

  /**
   * Test.
   */
  public function testShouldAjaxReplaceFormWhenIsNotValid() {
    $form = [
      '#id' => 'id',
      'email_recipe' => ['element'],
      'captcha' => ['element'],
    ];

    $this->formStateMock
      ->expects($this->any())
      ->method('getValue')
      ->willReturnMap([
        [
          'email',
          NULL,
          'email',
        ],
        [
          'grocery_list',
          NULL,
          0,
        ],
        [
          'email_recipe',
          NULL,
          0,
        ],
        [
          'context_data',
          NULL,
          ['error_message' => 'message'],
        ],
      ]);

    $this->formStateMock
      ->expects($this->any())
      ->method('get')
      ->willReturn(FALSE);

    $response = $this->form::ajaxReplaceForm($form, $this->formStateMock);
    $this->assertEquals(5, count($response->getCommands()));
  }

  /**
   * Test.
   */
  public function testShouldAjaxReplaceFormWhenIsValid() {
    $form = [
      '#id' => 'id',
      'email_recipe' => ['element'],
      '#attached' => [],
    ];

    $this->formStateMock
      ->expects($this->any())
      ->method('getValue')
      ->willReturnMap([
        [
          'email',
          NULL,
          'email@email.com',
        ],
        [
          'grocery_list',
          NULL,
          1,
        ],
        [
          'email_recipe',
          NULL,
          1,
        ],
        [
          'context_data',
          NULL,
          ['error_message' => 'message'],
        ],
      ]);

    $this->formStateMock
      ->expects($this->any())
      ->method('get')
      ->willReturn(TRUE);

    $this->containerMock
      ->expects($this->exactly(1))
      ->method('get')
      ->willReturnMap(
        [
          [
            'renderer',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->rendererMock,
          ],
        ]
      );

    $this->rendererMock
      ->expects($this->once())
      ->method('renderRoot')
      ->willReturn($this->markupMock);

    $response = $this->form::ajaxReplaceForm($form, $this->formStateMock);
    $this->assertEquals(2, count($response->getCommands()));
  }

  /**
   * Create all mocks for tests in this file.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->translationMock = $this->createMock(TranslationInterface::class);
    $this->formStateMock = $this->createMock(FormStateInterface::class);
    $this->themeConfiguratorParserMock = $this->createMock(ThemeConfiguratorParser::class);
    $this->loggerMock = $this->createMock(LoggerInterface::class);
    $this->recipeMock = $this->createMock(NodeInterface::class);
    $this->urlMock = $this->createMock(Url::class);
    $this->fieldItemListMock = $this->createMock(FieldItemListInterface::class);
    $this->rendererMock = $this->createMock(RendererInterface::class);
    $this->markupMock = $this->createMock(MarkupInterface::class);
  }

}

namespace Drupal\mars_recipes\Form;

/**
 * {@inheritdoc}
 */
function t(string $string) {
  return $string;
}

/**
 * {@inheritdoc}
 */
function recaptcha_captcha_validation(
  $solution,
  $response,
  $element,
  $form_state
) {
  return FALSE;
}
