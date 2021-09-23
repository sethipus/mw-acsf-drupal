<?php

declare(strict_types=1);

namespace Drupal\Tests\juicer_io\Unit\Form;

if (!defined('SAVED_NEW')) {
  define('SAVED_NEW', 1);
}
if (!defined('SAVED_UPDATED')) {
  define('SAVED_UPDATED', 2);
}

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Url;
use Drupal\juicer_io\Entity\FeedConfiguration;
use Drupal\juicer_io\Form\FeedConfigurationForm;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Unit tests for the feed configuration form.
 *
 * @coversDefaultClass \Drupal\juicer_io\Form\FeedConfigurationForm
 */
class FeedConfigurationFormTest extends UnitTestCase {

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
   * Feed id of the entity in the form.
   *
   * @var string
   */
  private $entityFeedId;

  /**
   * Mocked url that will be returned on toUrl call on entity.
   *
   * @var \Drupal\Core\Url|\PHPUnit\Framework\MockObject\MockObject
   */
  private $entityCollectionUrl;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    \Drupal::setContainer($this->createMock(ContainerInterface::class));
    $this->formEntity = $this->createMock(FeedConfiguration::class);
    $this->messenger = $this->createMock(MessengerInterface::class);
    $this->entityCollectionUrl = $this->createMock(Url::class);

    $this->formEntity
      ->method('get')
      ->with('feed_id')
      ->willReturnReference($this->entityFeedId);

    $this->formEntity
      ->method('toUrl')
      ->with('collection')
      ->willReturn($this->entityCollectionUrl);

    $this->entityFeedId = 'feed_id';
  }

  /**
   * Data provider function for form field names.
   *
   * @return \string[][]
   *   The provided data.
   */
  public function formFields() {
    return [
      ['label'],
      ['id'],
      ['feed_id'],
    ];
  }

  /**
   * Tests if the question is correctly set.
   *
   * @param string $field_key
   *   The key for the field.
   *
   * @test
   * @dataProvider formFields
   */
  public function formShouldContainFields(string $field_key) {
    $entity_form = $this->createForm();
    $formState = $this->createMock(FormStateInterface::class);

    $form_array = $entity_form->form([], $formState);

    $this->assertArrayHasKey($field_key, $form_array);
  }

  /**
   * Tests if the entity will be deleted on submit.
   *
   * @test
   */
  public function shouldCallDeleteOnEntityIfSubmitted() {
    $form = $this->createForm();
    $formState = $this->createMock(FormStateInterface::class);

    $this->formEntity
      ->expects($this->once())
      ->method('save');

    $form->save([], $formState);
  }

  /**
   * Tests if there will be a redirect.
   *
   * @test
   */
  public function shouldRedirectAfterSave() {
    $form = $this->createForm();
    $formState = $this->createMock(FormStateInterface::class);

    $formState
      ->expects($this->once())
      ->method('setRedirectUrl')
      ->with($this->entityCollectionUrl);

    $form->save([], $formState);
  }

  /**
   * Creates a form with a mocked entity.
   *
   * @return \Drupal\juicer_io\Form\FeedConfigurationForm
   *   The form.
   */
  private function createForm() {
    $delete_form = new FeedConfigurationForm();
    $delete_form->setEntity($this->formEntity);
    $delete_form->setMessenger($this->messenger);
    return $delete_form;
  }

}
