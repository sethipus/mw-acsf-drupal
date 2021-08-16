<?php

namespace Drupal\Tests\juicer_io\Unit\Model;

use Drupal\juicer_io\Model\FeedException;
use Drupal\Tests\UnitTestCase;

/**
 * Unit tests for FeedException class.
 *
 * @coversDefaultClass \Drupal\juicer_io\Model\FeedException
 */
class FeedExceptionTest extends UnitTestCase {

  /**
   * Tests that the wrapped exception contains the original one.
   *
   * @test
   */
  public function wrappedExceptionShouldContainOriginalOne() {
    $original_exception = new \Exception();

    $wrapped_exception = FeedException::wrap(
      $original_exception,
      'additional error message'
    );

    $this->assertSame($original_exception, $wrapped_exception->getPrevious());
  }

  /**
   * Tests that the wrapped exception contains the original one.
   *
   * @test
   */
  public function wrappedExceptionShouldBeFeedExceptionInstance() {
    $original_exception = new \Exception();

    $wrapped_exception = FeedException::wrap(
      $original_exception,
      'additional error message'
    );

    $this->assertInstanceOf(FeedException::class, $wrapped_exception);
  }

  /**
   * Tests that the wrapped exception contains the original one.
   *
   * @test
   */
  public function wrappedExceptionShouldContainOriginalErrorMessage() {
    $original_message = 'Original error message.';
    $original_exception = new \Exception($original_message);

    $wrapped_exception = FeedException::wrap(
      $original_exception,
      'additional error message'
    );

    $this->assertStringContainsString(
      $original_message,
      $wrapped_exception->getMessage()
    );
  }

  /**
   * Tests that the wrapped exception contains the original one.
   *
   * @test
   */
  public function wrappedExceptionShouldStartWithNewErrorMessage() {
    $original_exception = new \Exception();

    $new_error_message = 'Additional error message';
    $wrapped_exception = FeedException::wrap(
      $original_exception,
      $new_error_message
    );

    $this->assertStringStartsWith(
      $new_error_message,
      $wrapped_exception->getMessage()
    );
  }

}
