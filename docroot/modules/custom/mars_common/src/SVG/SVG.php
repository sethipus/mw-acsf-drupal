<?php

namespace Drupal\mars_common\SVG;

/**
 * Class that represents an svg structure.
 */
class SVG {

  /**
   * The svg content.
   *
   * @var string
   */
  private $content;

  /**
   * Reads a content of an svg file and creates an SVG object from it.
   *
   * @param string $uri
   *   The uri of the file.
   *
   * @return \Drupal\mars_common\SVG\SVG
   *   The resulting SVG object.
   *
   * @throws \Drupal\mars_common\SVG\SVGException
   */
  public static function createFromFile(string $uri): self {
    if (!file_exists($uri)) {
      throw SVGException::missingPhysicalFile($uri);
    }

    $content = file_get_contents($uri);
    if ($content === FALSE) {
      throw SVGException::readingFromFileFailed($uri);
    }

    return new self($content);
  }

  /**
   * SVG constructor.
   *
   * @param string $content
   *   The content of the svg file.
   */
  public function __construct(string $content) {
    $this->content = $content;
  }

  /**
   * Returns the base64 encoded svg.
   *
   * @return string
   *   The resulting string.
   */
  public function toBase64(): string {
    return base64_encode((string) $this);
  }

  /**
   * @return string
   *   The SVG as string.
   */
  public function __toString() {
    return $this->content;
  }

  /**
   * Returns an SVG object without size information.
   *
   * @return \Drupal\mars_common\SVG\SVG
   *   The new SVG object.
   */
  public function withoutSizeInfo(): self {
    $dom = new \DOMDocument();
    $dom->loadXML($this->content);
    $svg = $dom->documentElement;
    $svg->removeAttribute('width');
    $svg->removeAttribute('height');

    $content = $dom->saveXML();
    return new self($content);
  }

  /**
   * Returns an SVG object that will stretch to the available space.
   *
   * @return \Drupal\mars_common\SVG\SVG
   *   The new SVG object.
   */
  public function stretched(): self {
    $dom = new \DOMDocument();
    $dom->loadXML($this->content);
    $svg = $dom->documentElement;
    $svg->setAttribute('preserveAspectRatio', 'none');

    $content = $dom->saveXML();
    return new self($content);
  }

  /**
   * Returns an SVG object that will repeat in the available space.
   *
   * @return \Drupal\mars_common\SVG\SVG
   *   The new SVG object.
   */
  public function repeated(): self {
    $id = 'repeat-pattern';

    $dom = new \DOMDocument();
    $dom->loadXML($this->content);
    $svg = $dom->documentElement;
    $width = $svg->getAttributeNode('width');
    $height = $svg->getAttributeNode('height');
    $svg->removeAttribute('width');
    $svg->removeAttribute('height');
    $svg->removeAttribute('viewBox');

    $pattern = $dom->createElement('pattern');
    $pattern->setAttribute('patternUnits', "userSpaceOnUse");
    $pattern->setAttribute('id', $id);
    $pattern->setAttributeNode($width);
    $pattern->setAttributeNode($height);


    foreach (iterator_to_array($svg->childNodes) as $key => $child_node) {
      if ($child_node->nodeType == XML_ELEMENT_NODE) {
        $pattern->appendChild($child_node);
      }
    }

    $defs = $dom->createElement('defs');
    $svg->appendChild($defs);

    $defs->appendChild($pattern);

    $rect = $dom->createElement('rect');
    $rect->setAttribute('width', '100%');
    $rect->setAttribute('height', '100%');
    $rect->setAttribute('fill', 'url(#' . $id . ')');
    $svg->appendChild($rect);

    $content = $dom->saveXML();
    return new self($content);
  }

  /**
   * Returns an SVG object without the fill information in it.
   *
   * @return \Drupal\mars_common\SVG\SVG
   *   The new SVG object.
   */
  public function withoutFillInfo() {
    $dom = new \DOMDocument();
    $dom->loadXML($this->content);

    $xpath = new \DOMXPath($dom);
    $elements = $xpath->query('//*');
    foreach ($elements as $element) {
      $element->removeAttribute('fill');
    }

    $content = $dom->saveXML();
    return new self($content);
  }

}
