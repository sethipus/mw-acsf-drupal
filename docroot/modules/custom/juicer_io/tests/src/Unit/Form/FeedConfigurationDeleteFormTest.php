<?php

declare(strict_types=1);

namespace Drupal\Tests\juicer_io\Unit\Form;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Drupal\juicer_io\Form\FeedConfigurationDeleteForm;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Unit tests for the feed delete form.
 *
 * @coversDefaultClass \Drupal\juicer_io\Form\FeedConfigurationDeleteForm
 */
class FeedConfigurationDeleteFormTest extends UnitTestCase {

  /**
   * Mocked entity for the form.
   *
   * @var \Drupal\Core\Entity\EntityInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $formEntity;

  /**
   * Mocked messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $messenger;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    \Drupal::setContainer($this->createMock(ContainerInterface::class));
    $this->formEntity = $this->createMock(EntityInterface::class);
    $this->messenger = $this->createMock(MessengerInterface::class);

  }

  /**
   * Tests if the question is correctly set.
   *
   * @test
   */
  public function shouldHaveProperQuestionSet() {
    $label = 'entity_label_value';
    $this->entityHasLabel($label);
    $delete_form = $this->createForm();

    $question = $delete_form->getQuestion();

    $this->assertEquals(
      'Are you sure you want to delete %name?',
      $question->getUntranslatedString()
    );
    $this->assertTranslatableStringArgumentIs($question, '%name', $label);
  }

  /**
   * Tests if the cancel url is properly set.
   *
   * @test
   */
  public function shouldHaveProperCancelUrl() {
    $delete_form = $this->createForm();

    $cancel_url = $delete_form->getCancelUrl();

    $this->assertEquals(
      'entity.juicer_io_feed.collection',
      $cancel_url->getRouteName()
    );
  }

  /**
   * Tests if the confirm text is properly set.
   *
   * @test
   */
  public function shouldHaveProperConfirmText() {
    $delete_form = $this->createForm();

    $confirm_text = $delete_form->getConfirmText();

    $this->assertEquals('Delete', $confirm_text->getUntranslatedString());
  }

  /**
   * Tests if the entity will be deleted on submit.
   *
   * @test
   */
  public function shouldCallDeleteOnEntityIfSubmitted() {
    $delete_form = $this->createForm();
    $formState = $this->createMock(FormStateInterface::class);
    $form = [];

    $this->formEntity
      ->expects($this->once())
      ->method('delete');

    $delete_form->submitForm($form, $formState);
  }

  /**
   * Tests if there will be a redirect.
   *
   * @test
   */
  public function shouldRedirectAfterSubmit() {
    $expected_redirect_route = 'entity.juicer_io_feed.collection';
    $delete_form = $this->createForm();
    $formState = $this->createMock(FormStateInterface::class);
    $form = [];

    $formState
      ->expects($this->once())
      ->method('setRedirectUrl')
      ->with(
        $this->callback(function (Url $url) use ($expected_redirect_route) {
          return $url->getRouteName() === $expected_redirect_route;
        })
      );

    $delete_form->submitForm($form, $formState);
  }

  /**
   * Tests if the entity will be deleted on submit.
   *
   * @test
   */
  public function shouldSetStatusMessageAfterSubmit() {
    $expected_markup = 'Juicer.io feed configuration deleted: @label.';
    $expected_label = 'label_value';
    $delete_form = $this->createForm();
    $this->entityHasLabel($expected_label);
    $formState = $this->createMock(FormStateInterface::class);
    $form = [];

    $this->messenger
      ->expects($this->once())
      ->method('addStatus')
      ->with($this->callback(
        function (TranslatableMarkup $markup) use (
          $expected_markup,
          $expected_label
        ) {
          $this->assertTranslatableStringArgumentIs(
            $markup,
            '@label',
            $expected_label
          );
          $this->assertEquals(
            $expected_markup,
            $markup->getUntranslatedString()
          );
          return TRUE;
        }
      ));

    $delete_form->submitForm($form, $formState);
  }

  /**
   * Creates a form with a mocked entity.
   */
  private function createForm() {
    $delete_form = new FeedConfigurationDeleteForm();
    $delete_form->setEntity($this->formEntity);
    $delete_form->setMessenger($this->messenger);
    return $delete_form;
  }

  /**
   * Asserts that the translatable markup has argument.
   *
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup $translatableMarkup
   *   The translatable markup.
   * @param string $arg_name
   *   The expected arg name.
   * @param string $value
   *   The expected arg value.
   */
  private function assertTranslatableStringArgumentIs(
    TranslatableMarkup $translatableMarkup,
    string $arg_name,
    string $value
  ): void {
    $args = $translatableMarkup->getArguments();
    $this->assertEquals($value, $args[$arg_name]);
  }

  /**
   * Mocks that the entity in the form has the given label.
   *
   * @param string $label
   *   The label.
   */
  protected function entityHasLabel(string $label): void {
    $this->formEntity->method('label')->willReturn($label);
  }

}
