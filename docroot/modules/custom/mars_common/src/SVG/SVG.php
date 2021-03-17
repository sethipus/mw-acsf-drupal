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
   * @var string
   */
  private $id;

  /**
   * Reads a content of an svg file and creates an SVG object from it.
   *
   * @param string $uri
   *   The uri of the file.
   * @param string $id
   *   The unique id for this SVG object.
   *
   * @return \Drupal\mars_common\SVG\SVG
   *   The resulting SVG object.
   *
   * @throws \Drupal\mars_common\SVG\SVGException
   */
  public static function createFromFile(string $uri, string $id): self {
    if (!file_exists($uri)) {
      throw SVGException::missingPhysicalFile($uri);
    }

    $content = file_get_contents($uri);
    if ($content === FALSE) {
      throw SVGException::readingFromFileFailed($uri);
    }

    return new self($content, $id);
  }

  /**
   * SVG constructor.
   *
   * @param string $content
   *   The content of the svg file.
   * @param string $id
   *   The unique id for this SVG object.
   */
  public function __construct(string $content, string $id) {
    $this->content = $content;
    $this->id = $id;
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
    return new self($content, $this->id);
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
    return new self($content, $this->id);
  }

  /**
   * Creates an SVG that scale with keeping his initial aspect ratio.
   *
   * @return \Drupal\mars_common\SVG\SVG
   *   The new SVG object.
   */
  public function scaleWhileKeepingAspectRatio(): self {
    $dom = new \DOMDocument();
    $dom->loadXML($this->content);
    $svg = $dom->documentElement;
    $svg->setAttribute('preserveAspectRatio', 'xMidYMid meet');

    $content = $dom->saveXML();
    return new self($content, $this->id);
  }

  /**
   * Returns an SVG object that will repeat in the available space.
   *
   * @return \Drupal\mars_common\SVG\SVG
   *   The new SVG object.
   */
  public function repeated(): self {
    $pattern_id = $this->id . '-repeat-pattern';

    $dom = new \DOMDocument();
    $dom->loadXML($this->content);
    $svg = $dom->documentElement;
    $width = $svg->getAttributeNode('width');
    $height = $svg->getAttributeNode('height');
    $view_box = $svg->getAttributeNode('viewBox');
    $svg->removeAttribute('width');
    $svg->removeAttribute('viewBox');

    $pattern = $dom->createElement('pattern');
    $pattern->setAttribute('patternUnits', "userSpaceOnUse");
    $pattern->setAttribute('id', $pattern_id);
    if ($width) {
      $pattern->setAttributeNode($width->cloneNode());
    }
    if ($height) {
      $pattern->setAttributeNode($height->cloneNode());
    }
    if ($view_box) {
      $pattern->setAttributeNode($view_box->cloneNode());
    }

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
    if ($height) {
      $rect->setAttributeNode($height->cloneNode());
    }
    $rect->setAttribute('fill', 'url(#' . $pattern_id . ')');
    $svg->appendChild($rect);

    $content = $dom->saveXML();
    return new self($content, $this->id);
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

    $svg = $dom->documentElement;
    $this->removeFillInfo($svg);

    $xpath = new \DOMXPath($dom);
    /** @var \DOMNodeList $elements */
    $elements = $xpath->query('//*');

    /** @var \DOMNode $element */
    foreach ($elements as $element) {
      $this->removeFillInfo($element);
    }

    $content = $dom->saveXML();
    return new self($content, $this->id);
  }

  /**
   * Removes the fill attribute and fill style from the given node.
   *
   * @param \DOMNode $element
   *   The DOM node.
   */
  private function removeFillInfo(\DOMNode $element): void {
    $element->removeAttribute('fill');
    $style_attribute = $element->getAttribute('style');
    if ($style_attribute) {
      $style_attribute = preg_replace(
        '/(fill:[^;]+;?)/',
        '',
        $style_attribute
      );
      $element->setAttribute('style', $style_attribute);
    }
  }

  /**
   * Returns an SVG object without size information.
   *
   * @return \Drupal\mars_common\SVG\SVG
   *   The new SVG object.
   */
  public function withoutOpacityInfo(): self {
    $dom = new \DOMDocument();
    $dom->loadXML($this->content);
    $xpath = new \DOMXPath($dom);
    /** @var \DOMNodeList $elements */
    $elements = $xpath->query('//*');

    /** @var \DOMNode $element */
    foreach ($elements as $element) {
      $element->removeAttribute('opacity');
    }

    $content = $dom->saveXML();
    return new self($content, $this->id);
  }

}
