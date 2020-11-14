<?php

namespace Drupal\Tests\mars_common\Unit\SVG;

use Drupal\mars_common\SVG\SVG;
use Drupal\Tests\UnitTestCase;

/**
 * Unit tests for SVG class.
 */
class SVGTest extends UnitTestCase {

  const SAMPLE_SVG = <<<'SVG'
      <svg height="100" width="500" viewBox="0 0 500 100" fill="none" >
        <ellipse cx="240" cy="50" rx="220" ry="30" fill="yellow" />
        <ellipse cx="220" cy="50" rx="190" ry="20" fill="white" />
      </svg>
SVG;

  /**
   * @test
   */
  public function shouldRemoveSizeInformation() {
    $expected = <<<'SVG'
      <svg fill="none" viewBox="0 0 500 100">
        <ellipse cx="240" cy="50" rx="220" ry="30" fill="yellow" />
        <ellipse cx="220" cy="50" rx="190" ry="20" fill="white" />
      </svg>
SVG;
    $svg = new SVG(self::SAMPLE_SVG);

    $svg = $svg->withoutSizeInfo();

    $this->assertXmlStringEqualsXmlString($expected, (string) $svg);
  }

  /**
   * @test
   */
  public function shouldRepeatAsPattern() {
    $expected = <<<'SVG'
      <svg fill="none">
        <defs>
            <pattern id="repeat-pattern" patternUnits="userSpaceOnUse" height="100" width="500">
                <ellipse cx="240" cy="50" rx="220" ry="30" fill="yellow" />
                <ellipse cx="220" cy="50" rx="190" ry="20" fill="white" />
            </pattern>
        </defs>
            <rect width="100%" height="100%" fill="url(#repeat-pattern)"/>
      </svg>
SVG;
    $svg = new SVG(self::SAMPLE_SVG);

    $svg = $svg->repeated();

    $this->assertXmlStringEqualsXmlString($expected, (string) $svg,(string) $svg);
  }

  /**
   * @test
   */
  public function shouldStretch() {
    $expected = <<<'SVG'
      <svg height="100" width="500" viewBox="0 0 500 100" preserveAspectRatio="none" fill="none">
        <ellipse cx="240" cy="50" rx="220" ry="30" fill="yellow" />
        <ellipse cx="220" cy="50" rx="190" ry="20" fill="white" />
      </svg>
SVG;
    $svg = new SVG(self::SAMPLE_SVG);

    $svg = $svg->stretched();

    $this->assertXmlStringEqualsXmlString($expected, (string) $svg,(string) $svg);
  }

  /**
   * @test
   */
  public function shouldRemoveFillInformation() {
    $expected = <<<'SVG'
      <svg height="100" width="500" viewBox="0 0 500 100" >
        <ellipse cx="240" cy="50" rx="220" ry="30" />
        <ellipse cx="220" cy="50" rx="190" ry="20" />
      </svg>
SVG;
    $svg = new SVG(self::SAMPLE_SVG);

    $svg = $svg->withoutFillInfo();

    $this->assertXmlStringEqualsXmlString($expected, (string) $svg,(string) $svg);
  }

}
