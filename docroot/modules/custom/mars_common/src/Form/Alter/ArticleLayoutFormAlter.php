<?php

namespace Drupal\mars_common\Form\Alter;

/**
 * Class ArticleLayoutFormAlter contains list of required sections.
 *
 * @package Drupal\mars_common\Form\Alter
 */
class ArticleLayoutFormAlter extends LayoutFormAlterBase {

  const FIXED_SECTIONS = [
    'article_article_header',
    'article_article_body',
    'article_content_recommendations',
  ];

}
