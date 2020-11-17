<?php

namespace Drupal\Tests\mars_common\Unit\SVG;

use Drupal\mars_common\SVG\SVG;
use Drupal\Tests\UnitTestCase;

/**
 * Unit tests for SVG class.
 */
class SVGTest extends UnitTestCase {

  const SAMPLE_SVG = <<<'SVG'
      <svg xmlns:default="http://www.w3.org/2000/svg" width="500" height="200" viewBox="0 0 500 200" fill="none" >
        <ellipse cx="240" cy="50" rx="220" ry="30" fill="yellow" />
        <ellipse cx="220" cy="50" rx="190" ry="20" fill="white" />
      </svg>
SVG;

  /**
   * @test
   */
  public function shouldRemoveSizeInformation() {
    $expected = <<<'SVG'
      <svg xmlns:default="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 500 200">
        <ellipse cx="240" cy="50" rx="220" ry="30" fill="yellow" />
        <ellipse cx="220" cy="50" rx="190" ry="20" fill="white" />
      </svg>
SVG;
    $svg = $this->createNewSVG();

    $svg = $svg->withoutSizeInfo();

    $this->assertXmlStringEqualsXmlString($expected, (string) $svg);
  }

  /**
   * @test
   */
  public function shouldRepeatAsPattern() {
    $expected = <<<'SVG'
      <svg xmlns:default="http://www.w3.org/2000/svg" height="200" fill="none">
        <defs>
            <pattern id="id-repeat-pattern" patternUnits="userSpaceOnUse" width="500" height="200" viewBox="0 0 500 200" >
                <ellipse cx="240" cy="50" rx="220" ry="30" fill="yellow" />
                <ellipse cx="220" cy="50" rx="190" ry="20" fill="white" />
            </pattern>
        </defs>
            <rect width="100%" height="200" fill="url(#id-repeat-pattern)"/>
      </svg>
SVG;
    $svg = $this->createNewSVG();

    $svg = $svg->repeated();

    $this->assertXmlStringEqualsXmlString($expected, (string) $svg);
  }

  /**
   * @test
   */
  public function shouldStretch() {
    $expected = <<<'SVG'
      <svg xmlns:default="http://www.w3.org/2000/svg" width="500" height="200" viewBox="0 0 500 200" preserveAspectRatio="none" fill="none">
        <ellipse cx="240" cy="50" rx="220" ry="30" fill="yellow" />
        <ellipse cx="220" cy="50" rx="190" ry="20" fill="white" />
      </svg>
SVG;
    $svg = $this->createNewSVG();

    $svg = $svg->stretched();

    $this->assertXmlStringEqualsXmlString($expected, (string) $svg);
  }

  /**
   * @test
   */
  public function shouldRemoveFillInformation() {
    $expected = <<<'SVG'
      <svg xmlns:default="http://www.w3.org/2000/svg" width="500" height="200" viewBox="0 0 500 200" >
        <ellipse cx="240" cy="50" rx="220" ry="30" />
        <ellipse cx="220" cy="50" rx="190" ry="20" />
      </svg>
SVG;
    $svg = $this->createNewSVG();

    $svg = $svg->withoutFillInfo();

    $this->assertXmlStringEqualsXmlString($expected, (string) $svg);
  }

  /**
   * Creates a new SVG object.
   *
   * @return \Drupal\mars_common\SVG\SVG
   *   The new SVG object.
   */
  private function createNewSVG(): SVG {
    return new SVG(self::SAMPLE_SVG, 'id');
  }

}
