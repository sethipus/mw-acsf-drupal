<?php

namespace Drupal\mars_common\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse; 

/**
 * Configuration form for character limit page.
 *
 * @internal
 */
class MarsCharacterLimitPageForm extends ConfigFormBase {

  /**
   * Config settings.
   *
   * @var string
   */
  const FIELD_LIMIT_DESCRIPTION = 'Default character limit of the field';
  const EYEBROW = 15;
  const HERO_BLOCK_EYEBROW = 15;
  const HERO_BLOCK_TITLE_LINK_URL = 2048;
  const HERO_BLOCK_TITLE_LABEL = 55;
  const HERO_BLOCK_OVERRIDE_TITLE_LABEL = 200;
  const HERO_BLOCK_CTA_LINK_URL = 2048;
  const HERO_BLOCK_CTA_LINK_TITLE = 15;
  const HERO_BLOCK_CARD_EYEBROW = 15;
  const HERO_BLOCK_CARD_TITLE_LABEL = 55;
  const HERO_BLOCK_CARD_TITLE_LINK_URL = 2048;
  const HERO_BLOCK_CARD_CTA_LINK_TITLE = 15;
  const HERO_BLOCK_CARD_CTA_LINK_URL = 2048;
  const ALERT_BANNER_TEXT = 100;
  const CAROUSEL_COMPONENT_TITLE = 55;
  const CAROUSEL_ITEM_DESCRIPTION = 255;
  const CONTACT_HELP_BANNER_TITLE = 55;
  const CONTACT_HELP_BANNER_DESCRIPTION = 255;
  const CONTACT_HELP_BANNER_SOCIAL_LINK_LABEL = 35;
  const CONTENT_FEATURE_MODULE_EYEBROW = 15;
  const CONTENT_FEATURE_MODULE_TITLE = 55;
  const CONTENT_FEATURE_MODULE_DESCRIPTION = 300;
  const CONTENT_FEATURE_MODULE_BUTTON_LABEL = 15;
  const FLEXIBLE_DRIVER_TITLE = 65;
  const FLEXIBLE_DRIVER_DESCRIPTION = 160;
  const FLEXIBLE_DRIVER_CTA_LABEL  = 15;
  const FLEXIBLE_FRAME_HEADER = 55;
  const FLEXIBLE_FRAME_ITEM_TITLE = 60;
  const FLEXIBLE_FRAME_CTA_LINK_TITLE = 15;
  const FLEXIBLE_FRAME_CTA_LINK_URL = 2048;
  const FLEXIBLE_FRAME_ITEM_DESCRIPTION = 255;
  const FOOTER_BLOCK_LINK_URL = 2048;
  const FOOTER_BLOCK_LINK_TITLE = 2048;
  const FREEFORM_STORY_BLOCK_HEADER1 = 60;
  const FREEFORM_STORY_BLOCK_HEADER2 = 60;
  const FREEFORM_STORY_BLOCK_DESCRIPTION = 1000;
  const HEADER_BLOCK_ALERT_BANNER_TEXT = 100;
  const IFRAME_ACCESSIBILITY_TITLE = 150;
  const LIST_COMPONENT_TITLE = 55;
  const LIST_COMPONENT_ELEMENT_NUMBER = 5;
  const PARENT_PAGE_HEADER_EYEBROW = 30;
  const PARENT_PAGE_HEADER_TITLE = 55;
  const PARENT_PAGE_HEADER_DESCRIPTION = 255;
  const PRODUCT_FEATURE_BLOCK_EYEBROW = 15;
  const PRODUCT_FEATURE_BLOCK_TITLE = 55;
  const PRODUCT_FEATURE_BLOCK_BACKGROUND_COLOR_OVERRIDE = 7;
  const PRODUCT_FEATURE_BLOCK_BUTTON_LABEL = 15;
  const SOCIAL_FEED_BLOCK_TITLE = 55;
  const STORY_HIGHLIGHT_TITLE = 55;
  const STORY_HIGHLIGHT_DESCRIPTION = 255;
  const STORY_HIGHLIGHT_ITEM_TITLE = 300;
  const TEXT_BLOCK_HEADER = 55;
  const GRID_CARD_TITLE = 55;
  const PDP_HERO_EYEBROW = 15;
  const PDP_HERO_AVAILABLE_SIZES = 50;
  const PDP_HERO_NUTRITION_SECTION_LABEL = 18;
  const PDP_HERO_NUTRITION_BENEFITS_LABEL = 55;
  const PDP_HERO_DIET_ALLERGENS_PART_LABEL = 18;
  const PDP_HERO_COOKING_INSTRUCTION_LABEL = 55;
  const PDP_HERO_MORE_INFORMATION_LABEL = 18;
  const PRODUCT_CONTENT_PAIR_TITLE = 55;
  const PRODUCT_CONTENT_PAIR_MASTER_CARD_EYEBROW = 15;
  const PRODUCT_CONTENT_PAIR_MASTER_CARD_TITLE = 33;
  const PRODUCT_CONTENT_PAIR_CTA_LINK_TEXT = 15;
  const PRODUCT_CONTENT_PAIR_CARD_EYEBROW = 15;
  const RECIPE_DETAIL_HERO_HINT = 160;
  const RECIPE_DETAIL_HERO_OVERLAY_TITLE = 55;
  const RECIPE_DETAIL_HERO_OVERLAY_DESCRIPTION = 150;
  const RECIPE_DETAIL_HERO_GROCERY_LIST_LABEL = 55;
  const RECIPE_DETAIL_HERO_EMAIL_RECIPE_LABEL = 55;
  const RECIPE_DETAIL_HERO_EMAIL_ADDRESS_HINT = 35;
  const RECIPE_DETAIL_HERO_ERROR_MESSAGE = 100;
  const RECIPE_DETAIL_HERO_CTA_TITLE = 35;
  const RECIPE_DETAIL_HERO_CONFIRMATION_MESSAGE = 55;
  const RECIPE_FEATURE_BLOCK_FEATURED_RECIPE = 55;
  const RECIPE_FEATURE_BLOCK_EYEBROW = 15;
  const RECIPE_FEATURE_BLOCK_RECIPE_TITLE = 60;
  const RECIPE_FEATURE_BLOCK_CTA_LINK_TITLE = 15;
  const RECOMMENDATIONS_MODULE_TITLE = 55;
  const SEARCH_FAQS_BLOCK_TITLE = 55;
  const SEARCH_PAGE_HEADER_TITLE = 55;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'character_limit_page';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['mars_common.character_limit_page'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $site_label_config = $this->config('mars_common.character_limit_page');

    $form['ui_disclaimer'] = array(
      '#type' => 'label',
      '#title' => $this->t('<span class="ui-disclaimer">IMPORTANT!</span> Once the character count limitation is changed, the onus lies with the Site admin/ Content creator to verify how the modification in the Character Limit page is reflecting in the front end before publishing.'),
    );

    // MARS: Article header Block Fields
    $form['article_header'] = array(
      '#type' => 'details',
      '#title' => $this->t('MARS: Article header'),
      '#open' => TRUE,
    );
    $form['article_header']['article_eyebrow'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Eyebrow'),
      '#default_value' => !empty($site_label_config->get('article_eyebrow')) ? $site_label_config->get('article_eyebrow') : static::EYEBROW,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::EYEBROW.'</strong>'
    ];

    // MARS: Homepage Hero block fields
    $form['homepage_hero_block'] = array(
      '#type' => 'details',
      '#title' => $this->t('MARS: Homepage Hero block'),
    );
    $form['homepage_hero_block']['hero_block_eyebrow'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Eyebrow'),
      '#default_value' => !empty($site_label_config->get('hero_block_eyebrow')) ? $site_label_config->get('hero_block_eyebrow') : static::HERO_BLOCK_EYEBROW,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::HERO_BLOCK_EYEBROW.'</strong>'
    ];
    $form['homepage_hero_block']['hero_block_title_link_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title Link URL'),
      '#default_value' => !empty($site_label_config->get('hero_block_title_link_url')) ? $site_label_config->get('hero_block_title_link_url') : static::HERO_BLOCK_TITLE_LINK_URL,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::HERO_BLOCK_TITLE_LINK_URL.'</strong>'
    ];
    $form['homepage_hero_block']['hero_block_title_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title label'),
      '#default_value' => !empty($site_label_config->get('hero_block_title_label')) ? $site_label_config->get('hero_block_title_label') : static::HERO_BLOCK_TITLE_LABEL,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::HERO_BLOCK_TITLE_LABEL.'</strong>'
    ];
    $form['homepage_hero_block']['hero_block_override_title_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Override Title label'),
      '#default_value' => !empty($site_label_config->get('hero_block_override_title_label')) ? $site_label_config->get('hero_block_override_title_label') : static::HERO_BLOCK_OVERRIDE_TITLE_LABEL,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::HERO_BLOCK_OVERRIDE_TITLE_LABEL.'</strong>'
    ];
    $form['homepage_hero_block']['hero_block_cta_link_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('CTA Link URL'),
      '#default_value' => !empty($site_label_config->get('hero_block_cta_link_url')) ? $site_label_config->get('hero_block_cta_link_url') : static::HERO_BLOCK_CTA_LINK_URL,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::HERO_BLOCK_CTA_LINK_URL.'</strong>'
    ];
    $form['homepage_hero_block']['hero_block_cta_link_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('CTA Link Title'),
      '#default_value' => !empty($site_label_config->get('hero_block_cta_link_title')) ? $site_label_config->get('hero_block_cta_link_title') : static::HERO_BLOCK_CTA_LINK_TITLE,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::HERO_BLOCK_CTA_LINK_TITLE.'</strong>'
    ];
    $form['homepage_hero_block']['hero_block_card_eyebrow'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Product Card Eyebrow'),
      '#default_value' => !empty($site_label_config->get('hero_block_card_eyebrow')) ? $site_label_config->get('hero_block_card_eyebrow') : static::HERO_BLOCK_CARD_EYEBROW,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::HERO_BLOCK_CARD_EYEBROW.'</strong>'
    ];
    $form['homepage_hero_block']['hero_block_card_title_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Product Card Title label'),
      '#default_value' => !empty($site_label_config->get('hero_block_card_title_label')) ? $site_label_config->get('hero_block_card_title_label') : static::HERO_BLOCK_CARD_TITLE_LABEL,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::HERO_BLOCK_CARD_TITLE_LABEL.'</strong>'
    ];
    $form['homepage_hero_block']['hero_block_card_title_link_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Product Card Title Link URL'),
      '#default_value' => !empty($site_label_config->get('hero_block_card_title_link_url')) ? $site_label_config->get('hero_block_card_title_link_url') : static::HERO_BLOCK_CARD_TITLE_LINK_URL,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::HERO_BLOCK_CARD_TITLE_LINK_URL.'</strong>'
    ];
    $form['homepage_hero_block']['hero_block_card_cta_link_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Product Card CTA Link Title'),
      '#default_value' => !empty($site_label_config->get('hero_block_card_cta_link_title')) ? $site_label_config->get('hero_block_card_cta_link_title') : static::HERO_BLOCK_CARD_CTA_LINK_TITLE,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::HERO_BLOCK_CARD_CTA_LINK_TITLE.'</strong>'
    ];
    $form['homepage_hero_block']['hero_block_card_cta_link_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Product Card CTA Link URL'),
      '#default_value' => !empty($site_label_config->get('hero_block_card_cta_link_url')) ? $site_label_config->get('hero_block_card_cta_link_url') : static::HERO_BLOCK_CARD_CTA_LINK_URL,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::HERO_BLOCK_CARD_CTA_LINK_URL.'</strong>'
    ];

    // MARS: Alert banner block fields
    $form['alert_banner_block'] = array(
      '#type' => 'details',
      '#title' => $this->t('MARS: Alert banner block'),
    );
    $form['alert_banner_block']['alert_banner_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Alert Banner text'),
      '#default_value' => !empty($site_label_config->get('alert_banner_text')) ? $site_label_config->get('alert_banner_text') : static::ALERT_BANNER_TEXT,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::ALERT_BANNER_TEXT.'</strong>'
    ];

    // MARS: Carousel component block fields
    $form['carousel_component'] = array(
      '#type' => 'details',
      '#title' => $this->t('MARS: Carousel component'),
    );
    $form['carousel_component']['carousel_component_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Carousel title'),
      '#default_value' => !empty($site_label_config->get('carousel_component_title')) ? $site_label_config->get('carousel_component_title') : static::CAROUSEL_COMPONENT_TITLE,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::CAROUSEL_COMPONENT_TITLE.'</strong>'
    ];
    $form['carousel_component']['carousel_item_description'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Carousel item description'),
      '#default_value' => !empty($site_label_config->get('carousel_item_description')) ? $site_label_config->get('carousel_item_description') : static::CAROUSEL_ITEM_DESCRIPTION,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::CAROUSEL_ITEM_DESCRIPTION.'</strong>'
    ];

    // MARS: Contact Help Banner block fields
    $form['contact_help_banner'] = array(
      '#type' => 'details',
      '#title' => $this->t('MARS: Contact Help Banner'),
    );
    $form['contact_help_banner']['contact_help_banner_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => !empty($site_label_config->get('contact_help_banner_title')) ? $site_label_config->get('contact_help_banner_title') : static::CONTACT_HELP_BANNER_TITLE,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::CONTACT_HELP_BANNER_TITLE.'</strong>'
    ];
    $form['contact_help_banner']['contact_help_banner_description'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Description'),
      '#default_value' => !empty($site_label_config->get('contact_help_banner_description')) ? $site_label_config->get('contact_help_banner_description') : static::CONTACT_HELP_BANNER_DESCRIPTION,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::CONTACT_HELP_BANNER_DESCRIPTION.'</strong>'
    ];
    $form['contact_help_banner']['contact_help_banner_social_link_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Social Links label'),
      '#default_value' => !empty($site_label_config->get('contact_help_banner_social_link_label')) ? $site_label_config->get('contact_help_banner_social_link_label') : static::CONTACT_HELP_BANNER_SOCIAL_LINK_LABEL,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::CONTACT_HELP_BANNER_SOCIAL_LINK_LABEL.'</strong>'
    ];

    // MARS: Content Feature Module block fields
    $form['content_feature_module'] = array(
      '#type' => 'details',
      '#title' => $this->t('MARS: Content Feature Module'),
    );
    $form['content_feature_module']['content_feature_module_eyebrow'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Eyebrow'),
      '#default_value' => !empty($site_label_config->get('content_feature_module_eyebrow')) ? $site_label_config->get('content_feature_module_eyebrow') : static::CONTENT_FEATURE_MODULE_EYEBROW,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::CONTENT_FEATURE_MODULE_EYEBROW.'</strong>'
    ];
    $form['content_feature_module']['content_feature_module_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => !empty($site_label_config->get('content_feature_module_title')) ? $site_label_config->get('content_feature_module_title') : static::CONTENT_FEATURE_MODULE_TITLE,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::CONTENT_FEATURE_MODULE_TITLE.'</strong>'
    ];
    $form['content_feature_module']['content_feature_module_description'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Description'),
      '#default_value' => !empty($site_label_config->get('content_feature_module_description')) ? $site_label_config->get('content_feature_module_description') : static::CONTENT_FEATURE_MODULE_DESCRIPTION,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::CONTENT_FEATURE_MODULE_DESCRIPTION.'</strong>'
    ];
    $form['content_feature_module']['content_feature_module_button_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Button Label'),
      '#default_value' => !empty($site_label_config->get('content_feature_module_button_label')) ? $site_label_config->get('content_feature_module_button_label') : static::CONTENT_FEATURE_MODULE_BUTTON_LABEL,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::CONTENT_FEATURE_MODULE_BUTTON_LABEL.'</strong>'
    ];

    // MARS: Flexible driver  block fields
    $form['flexible_driver'] = array(
      '#type' => 'details',
      '#title' => $this->t('MARS: Flexible driver'),
    );
    $form['flexible_driver']['flexible_driver_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => !empty($site_label_config->get('flexible_driver_title')) ? $site_label_config->get('flexible_driver_title') : static::FLEXIBLE_DRIVER_TITLE,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::FLEXIBLE_DRIVER_TITLE.'</strong>'
    ];
    $form['flexible_driver']['flexible_driver_description'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Description'),
      '#default_value' => !empty($site_label_config->get('flexible_driver_description')) ? $site_label_config->get('flexible_driver_description') : static::FLEXIBLE_DRIVER_DESCRIPTION,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::FLEXIBLE_DRIVER_DESCRIPTION.'</strong>'
    ];
    $form['flexible_driver']['flexible_driver_cta_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('CTA Label'),
      '#default_value' => !empty($site_label_config->get('flexible_driver_cta_label')) ? $site_label_config->get('flexible_driver_cta_label') : static::FLEXIBLE_DRIVER_CTA_LABEL,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::FLEXIBLE_DRIVER_CTA_LABEL.'</strong>'
    ];

    // MARS: Flexible Framer block
     $form['flexible_frame_block'] = array(
      '#type' => 'details',
      '#title' => $this->t('MARS: Flexible Framer block'),
    );
    $form['flexible_frame_block']['flexible_frame_header'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Header'),
      '#default_value' => !empty($site_label_config->get('flexible_frame_header')) ? $site_label_config->get('flexible_frame_header') : static::FLEXIBLE_FRAME_HEADER,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::FLEXIBLE_FRAME_HEADER.'</strong>'
    ];
    $form['flexible_frame_block']['flexible_frame_item_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Item title'),
      '#default_value' => !empty($site_label_config->get('flexible_frame_item_title')) ? $site_label_config->get('flexible_frame_item_title') : static::FLEXIBLE_FRAME_ITEM_TITLE,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::FLEXIBLE_FRAME_ITEM_TITLE.'</strong>'
    ];
    $form['flexible_frame_block']['flexible_frame_cta_link_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('CTA Link Title'),
      '#default_value' => !empty($site_label_config->get('flexible_frame_cta_link_title')) ? $site_label_config->get('flexible_frame_cta_link_title') : static::FLEXIBLE_FRAME_CTA_LINK_TITLE,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::FLEXIBLE_FRAME_CTA_LINK_TITLE.'</strong>'
    ];
    $form['flexible_frame_block']['flexible_frame_cta_link_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('CTA Link URL'),
      '#default_value' => !empty($site_label_config->get('flexible_frame_cta_link_url')) ? $site_label_config->get('flexible_frame_cta_link_url') : static::FLEXIBLE_FRAME_CTA_LINK_URL,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::FLEXIBLE_FRAME_CTA_LINK_URL.'</strong>'
    ];
    $form['flexible_frame_block']['flexible_frame_item_description'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Item description'),
      '#default_value' => !empty($site_label_config->get('flexible_frame_item_description')) ? $site_label_config->get('flexible_frame_item_description') : static::FLEXIBLE_FRAME_ITEM_DESCRIPTION,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::FLEXIBLE_FRAME_ITEM_DESCRIPTION.'</strong>'
    ];

    // MARS: Footer block
    $form['footer_block'] = array(
      '#type' => 'details',
      '#title' => $this->t('MARS: Footer block'),
    );
    $form['footer_block']['footer_block_link_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Link URL'),
      '#default_value' => !empty($site_label_config->get('footer_block_link_url')) ? $site_label_config->get('footer_block_link_url') : static::FOOTER_BLOCK_LINK_URL,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::FOOTER_BLOCK_LINK_URL.'</strong>'
    ];
    $form['footer_block']['footer_block_link_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Link Title'),
      '#default_value' => !empty($site_label_config->get('footer_block_link_title')) ? $site_label_config->get('footer_block_link_title') : static::FOOTER_BLOCK_LINK_TITLE,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::FOOTER_BLOCK_LINK_TITLE.'</strong>'
    ];

    // MARS: Freeform Story Block
    $form['freeform_story_block'] = array(
      '#type' => 'details',
      '#title' => $this->t('MARS: Freeform Story Block'),
    );
    $form['freeform_story_block']['freeform_story_block_header_1'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Header 1'),
      '#default_value' => !empty($site_label_config->get('freeform_story_block_header_1')) ? $site_label_config->get('freeform_story_block_header_1') : static::FREEFORM_STORY_BLOCK_HEADER1,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::FREEFORM_STORY_BLOCK_HEADER1.'</strong>'
    ];
    $form['freeform_story_block']['freeform_story_block_header_2'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Header 2'),
      '#default_value' => !empty($site_label_config->get('freeform_story_block_header_2')) ? $site_label_config->get('freeform_story_block_header_2') : static::FREEFORM_STORY_BLOCK_HEADER2,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::FREEFORM_STORY_BLOCK_HEADER2.'</strong>'
    ];
    $form['freeform_story_block']['freeform_story_block_description'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Description'),
      '#default_value' => !empty($site_label_config->get('freeform_story_block_description')) ? $site_label_config->get('freeform_story_block_description') : static::FREEFORM_STORY_BLOCK_DESCRIPTION,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::FREEFORM_STORY_BLOCK_DESCRIPTION.'</strong>'
    ];

    // MARS: Header block
    $form['header_block'] = array(
      '#type' => 'details',
      '#title' => $this->t('MARS: Header block'),
    );
    $form['header_block']['header_block_alert_banner_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Alert Banner text'),
      '#default_value' => !empty($site_label_config->get('header_block_alert_banner_text')) ? $site_label_config->get('header_block_alert_banner_text') : static::HEADER_BLOCK_ALERT_BANNER_TEXT,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::HEADER_BLOCK_ALERT_BANNER_TEXT.'</strong>'
    ];

    // MARS: iFrame block
    $form['iframe'] = array(
      '#type' => 'details',
      '#title' => $this->t('MARS: iFrame'),
    );
    $form['iframe']['iframe_accessibility_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Accessibility Title'),
      '#default_value' => !empty($site_label_config->get('iframe_accessibility_title')) ? $site_label_config->get('iframe_accessibility_title') : static::IFRAME_ACCESSIBILITY_TITLE,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::IFRAME_ACCESSIBILITY_TITLE.'</strong>'
    ];

    // MARS: List component block
    $form['list_component'] = array(
      '#type' => 'details',
      '#title' => $this->t('MARS: List component'),
    );
    $form['list_component']['list_component_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('List title'),
      '#default_value' => !empty($site_label_config->get('list_component_title')) ? $site_label_config->get('list_component_title') : static::LIST_COMPONENT_TITLE,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::LIST_COMPONENT_TITLE.'</strong>'
    ];
    $form['list_component']['list_component_element_number'] = [
      '#type' => 'textfield',
      '#title' => $this->t('List element number'),
      '#default_value' => !empty($site_label_config->get('list_component_element_number')) ? $site_label_config->get('list_component_element_number') : static::LIST_COMPONENT_ELEMENT_NUMBER,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::LIST_COMPONENT_ELEMENT_NUMBER.'</strong>'
    ];

    // MARS: Parent Page Header block
    $form['parent_page_header'] = array(
      '#type' => 'details',
      '#title' => $this->t('MARS: Parent Page Header'),
    );
    $form['parent_page_header']['parent_page_header_eyebrow'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Eyebrow'),
      '#default_value' => !empty($site_label_config->get('parent_page_header_eyebrow')) ? $site_label_config->get('parent_page_header_eyebrow') : static::PARENT_PAGE_HEADER_EYEBROW,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::PARENT_PAGE_HEADER_EYEBROW.'</strong>'
    ];
    $form['parent_page_header']['parent_page_header_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => !empty($site_label_config->get('parent_page_header_title')) ? $site_label_config->get('parent_page_header_title') : static::PARENT_PAGE_HEADER_TITLE,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::PARENT_PAGE_HEADER_TITLE.'</strong>'
    ];
    $form['parent_page_header']['parent_page_header_description'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Description'),
      '#default_value' => !empty($site_label_config->get('parent_page_header_description')) ? $site_label_config->get('parent_page_header_description') : static::PARENT_PAGE_HEADER_DESCRIPTION,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::PARENT_PAGE_HEADER_DESCRIPTION.'</strong>'
    ];

    // MARS: Product Feature Block
    $form['product_feature_block'] = array(
      '#type' => 'details',
      '#title' => $this->t('MARS: Product Feature Block'),
    );
    $form['product_feature_block']['product_feature_block_eyebrow'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Eyebrow'),
      '#default_value' => !empty($site_label_config->get('product_feature_block_eyebrow')) ? $site_label_config->get('product_feature_block_eyebrow') : static::PRODUCT_FEATURE_BLOCK_EYEBROW,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::PRODUCT_FEATURE_BLOCK_EYEBROW.'</strong>'
    ];
    $form['product_feature_block']['product_feature_block_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => !empty($site_label_config->get('product_feature_block_title')) ? $site_label_config->get('product_feature_block_title') : static::PRODUCT_FEATURE_BLOCK_TITLE,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::PRODUCT_FEATURE_BLOCK_TITLE.'</strong>'
    ];
    $form['product_feature_block']['product_feature_block_background_color_override'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Background Color Override'),
      '#default_value' => !empty($site_label_config->get('product_feature_block_background_color_override')) ? $site_label_config->get('product_feature_block_background_color_override') : static::PRODUCT_FEATURE_BLOCK_BACKGROUND_COLOR_OVERRIDE,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::PRODUCT_FEATURE_BLOCK_BACKGROUND_COLOR_OVERRIDE.'</strong>'
    ];
    $form['product_feature_block']['product_feature_block_button_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Button Label'),
      '#default_value' => !empty($site_label_config->get('product_feature_block_button_label')) ? $site_label_config->get('product_feature_block_button_label') : static::PRODUCT_FEATURE_BLOCK_BUTTON_LABEL,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::PRODUCT_FEATURE_BLOCK_BUTTON_LABEL.'</strong>'
    ];

    // MARS: Social feed block
    $form['social_feed_block'] = array(
      '#type' => 'details',
      '#title' => $this->t('MARS: Social feed'),
    );
    $form['social_feed_block']['social_feed_block_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => !empty($site_label_config->get('social_feed_block_title')) ? $site_label_config->get('social_feed_block_title') : static::SOCIAL_FEED_BLOCK_TITLE,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::SOCIAL_FEED_BLOCK_TITLE.'</strong>'
    ];

    // MARS: Story Highlight block
    $form['story_highlight'] = array(
      '#type' => 'details',
      '#title' => $this->t('MARS: Story Highlight'),
    );
    $form['story_highlight']['story_highlight_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => !empty($site_label_config->get('story_highlight_title')) ? $site_label_config->get('story_highlight_title') : static::STORY_HIGHLIGHT_TITLE,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::STORY_HIGHLIGHT_TITLE.'</strong>'
    ];
    $form['story_highlight']['story_highlight_description'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Story description'),
      '#default_value' => !empty($site_label_config->get('story_highlight_description')) ? $site_label_config->get('story_highlight_description') : static::STORY_HIGHLIGHT_DESCRIPTION,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::STORY_HIGHLIGHT_DESCRIPTION.'</strong>'
    ];
    $form['story_highlight']['story_highlight_item_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Story Item Title'),
      '#default_value' => !empty($site_label_config->get('story_highlight_item_title')) ? $site_label_config->get('story_highlight_item_title') : static::STORY_HIGHLIGHT_ITEM_TITLE,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::STORY_HIGHLIGHT_ITEM_TITLE.'</strong>'
    ];

    // MARS: Text block
    $form['text_block'] = array(
      '#type' => 'details',
      '#title' => $this->t('MARS: Text block'),
    );
    $form['text_block']['text_block_header'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Header'),
      '#default_value' => !empty($site_label_config->get('text_block_header')) ? $site_label_config->get('text_block_header') : static::TEXT_BLOCK_HEADER,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::TEXT_BLOCK_HEADER.'</strong>'
    ];

    // MARS: Grid Card block
    $form['grid_card'] = array(
      '#type' => 'details',
      '#title' => $this->t('MARS: Grid Card'),
    );
    $form['grid_card']['grid_card_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => !empty($site_label_config->get('grid_card_title')) ? $site_label_config->get('grid_card_title') : static::GRID_CARD_TITLE,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::GRID_CARD_TITLE.'</strong>'
    ];

    // MARS: PDP Hero block
    $form['pdp_hero'] = array(
      '#type' => 'details',
      '#title' => $this->t('MARS: PDP Hero'),
    );
    $form['pdp_hero']['pdp_hero_eyebrow'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Eyebrow'),
      '#default_value' => !empty($site_label_config->get('pdp_hero_eyebrow')) ? $site_label_config->get('pdp_hero_eyebrow') : static::PDP_HERO_EYEBROW,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::PDP_HERO_EYEBROW.'</strong>'
    ];
    $form['pdp_hero']['pdp_hero_available_sizes'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Available sizes'),
      '#default_value' => !empty($site_label_config->get('pdp_hero_available_sizes')) ? $site_label_config->get('pdp_hero_available_sizes') : static::PDP_HERO_AVAILABLE_SIZES,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::PDP_HERO_AVAILABLE_SIZES.'</strong>'
    ];
    $form['pdp_hero']['pdp_hero_nutrition_section_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Nutrition section label'),
      '#default_value' => !empty($site_label_config->get('pdp_hero_nutrition_section_label')) ? $site_label_config->get('pdp_hero_nutrition_section_label') : static::PDP_HERO_NUTRITION_SECTION_LABEL,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::PDP_HERO_NUTRITION_SECTION_LABEL.'</strong>'
    ];
    $form['pdp_hero']['pdp_hero_nutrition_benefits_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Nutritional claims and benefits label'),
      '#default_value' => !empty($site_label_config->get('pdp_hero_nutrition_benefits_label')) ? $site_label_config->get('pdp_hero_nutrition_benefits_label') : static::PDP_HERO_NUTRITION_BENEFITS_LABEL,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::PDP_HERO_NUTRITION_BENEFITS_LABEL.'</strong>'
    ];
    $form['pdp_hero']['pdp_hero_diet_allergens_part_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Diet & Allergens part label'),
      '#default_value' => !empty($site_label_config->get('pdp_hero_diet_allergens_part_label')) ? $site_label_config->get('pdp_hero_diet_allergens_part_label') : static::PDP_HERO_DIET_ALLERGENS_PART_LABEL,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::PDP_HERO_DIET_ALLERGENS_PART_LABEL.'</strong>'
    ];
    $form['pdp_hero']['pdp_hero_cooking_instruction_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Cooking instructions label'),
      '#default_value' => !empty($site_label_config->get('pdp_hero_cooking_instruction_label')) ? $site_label_config->get('pdp_hero_cooking_instruction_label') : static::PDP_HERO_COOKING_INSTRUCTION_LABEL,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::PDP_HERO_COOKING_INSTRUCTION_LABEL.'</strong>'
    ];
    $form['pdp_hero']['pdp_hero_more_information_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('More information label'),
      '#default_value' => !empty($site_label_config->get('pdp_hero_more_information_label')) ? $site_label_config->get('pdp_hero_more_information_label') : static::PDP_HERO_MORE_INFORMATION_LABEL,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::PDP_HERO_MORE_INFORMATION_LABEL.'</strong>'
    ];

    // MARS: Product Content Pair Up block
    $form['product_content_pair'] = array(
      '#type' => 'details',
      '#title' => $this->t('MARS: Product Content Pair Up'),
    );
    $form['product_content_pair']['product_content_pair_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => !empty($site_label_config->get('product_content_pair_title')) ? $site_label_config->get('product_content_pair_title') : static::PRODUCT_CONTENT_PAIR_TITLE,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::PRODUCT_CONTENT_PAIR_TITLE.'</strong>'
    ];
    $form['product_content_pair']['product_content_pair_master_card_eyebrow'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Master Card Eyebrow'),
      '#default_value' => !empty($site_label_config->get('product_content_pair_master_card_eyebrow')) ? $site_label_config->get('product_content_pair_master_card_eyebrow') : static::PRODUCT_CONTENT_PAIR_MASTER_CARD_EYEBROW,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::PRODUCT_CONTENT_PAIR_MASTER_CARD_EYEBROW.'</strong>'
    ];
    $form['product_content_pair']['product_content_pair_master_card_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Master Card Title'),
      '#default_value' => !empty($site_label_config->get('product_content_pair_master_card_title')) ? $site_label_config->get('product_content_pair_master_card_title') : static::PRODUCT_CONTENT_PAIR_MASTER_CARD_TITLE,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::PRODUCT_CONTENT_PAIR_MASTER_CARD_TITLE.'</strong>'
    ];
    $form['product_content_pair']['product_content_pair_cta_link_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('CTA Link text'),
      '#default_value' => !empty($site_label_config->get('product_content_pair_cta_link_text')) ? $site_label_config->get('product_content_pair_cta_link_text') : static::PRODUCT_CONTENT_PAIR_CTA_LINK_TEXT,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::PRODUCT_CONTENT_PAIR_CTA_LINK_TEXT.'</strong>'
    ];
    $form['product_content_pair']['product_content_pair_card_eyebrow'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Supporting Card Eyebrow'),
      '#default_value' => !empty($site_label_config->get('product_content_pair_card_eyebrow')) ? $site_label_config->get('product_content_pair_card_eyebrow') : static::PRODUCT_CONTENT_PAIR_CARD_EYEBROW,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::PRODUCT_CONTENT_PAIR_CARD_EYEBROW.'</strong>'
    ];

    // MARS: Recipe detail hero block
    $form['recipe_detail_hero'] = array(
      '#type' => 'details',
      '#title' => $this->t('MARS: Recipe detail hero'),
    );
    $form['recipe_detail_hero']['recipe_detail_hero_hint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Hint'),
      '#default_value' => !empty($site_label_config->get('recipe_detail_hero_hint')) ? $site_label_config->get('recipe_detail_hero_hint') : static::RECIPE_DETAIL_HERO_HINT,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::RECIPE_DETAIL_HERO_HINT.'</strong>'
    ];
    $form['recipe_detail_hero']['recipe_detail_hero_overlay_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Overlay title'),
      '#default_value' => !empty($site_label_config->get('recipe_detail_hero_overlay_title')) ? $site_label_config->get('recipe_detail_hero_overlay_title') : static::RECIPE_DETAIL_HERO_OVERLAY_TITLE,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::RECIPE_DETAIL_HERO_OVERLAY_TITLE.'</strong>'
    ];
    $form['recipe_detail_hero']['recipe_detail_hero_overlay_description'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Overlay description'),
      '#default_value' => !empty($site_label_config->get('recipe_detail_hero_overlay_description')) ? $site_label_config->get('recipe_detail_hero_overlay_description') : static::RECIPE_DETAIL_HERO_OVERLAY_DESCRIPTION,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::RECIPE_DETAIL_HERO_OVERLAY_DESCRIPTION.'</strong>'
    ];
    $form['recipe_detail_hero']['recipe_detail_hero_grocery_list_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Grocery list label'),
      '#default_value' => !empty($site_label_config->get('recipe_detail_hero_grocery_list_label')) ? $site_label_config->get('recipe_detail_hero_grocery_list_label') : static::RECIPE_DETAIL_HERO_GROCERY_LIST_LABEL,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::RECIPE_DETAIL_HERO_GROCERY_LIST_LABEL.'</strong>'
    ];
    $form['recipe_detail_hero']['recipe_detail_hero_email_recipe_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email a recipe label'),
      '#default_value' => !empty($site_label_config->get('recipe_detail_hero_email_recipe_label')) ? $site_label_config->get('recipe_detail_hero_email_recipe_label') : static::RECIPE_DETAIL_HERO_EMAIL_RECIPE_LABEL,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::RECIPE_DETAIL_HERO_EMAIL_RECIPE_LABEL.'</strong>'
    ];
    $form['recipe_detail_hero']['recipe_detail_hero_email_address_hint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email address hint'),
      '#default_value' => !empty($site_label_config->get('recipe_detail_hero_email_address_hint')) ? $site_label_config->get('recipe_detail_hero_email_address_hint') : static::RECIPE_DETAIL_HERO_EMAIL_ADDRESS_HINT,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::RECIPE_DETAIL_HERO_EMAIL_ADDRESS_HINT.'</strong>'
    ];
    $form['recipe_detail_hero']['recipe_detail_hero_error_message'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Error message'),
      '#default_value' => !empty($site_label_config->get('recipe_detail_hero_error_message')) ? $site_label_config->get('recipe_detail_hero_error_message') : static::RECIPE_DETAIL_HERO_ERROR_MESSAGE,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::RECIPE_DETAIL_HERO_ERROR_MESSAGE.'</strong>'
    ];
    $form['recipe_detail_hero']['recipe_detail_hero_cta_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('CTA title'),
      '#default_value' => !empty($site_label_config->get('recipe_detail_hero_cta_title')) ? $site_label_config->get('recipe_detail_hero_cta_title') : static::RECIPE_DETAIL_HERO_CTA_TITLE,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::RECIPE_DETAIL_HERO_CTA_TITLE.'</strong>'
    ];
    $form['recipe_detail_hero']['recipe_detail_hero_confirmation_message'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Confirmation message'),
      '#default_value' => !empty($site_label_config->get('recipe_detail_hero_confirmation_message')) ? $site_label_config->get('recipe_detail_hero_confirmation_message') : static::RECIPE_DETAIL_HERO_CONFIRMATION_MESSAGE,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::RECIPE_DETAIL_HERO_CONFIRMATION_MESSAGE.'</strong>'
    ];

    // MARS: Recipe feature block
    $form['recipe_feature_block'] = array(
      '#type' => 'details',
      '#title' => $this->t('MARS: Recipe feature block'),
    );
    $form['recipe_feature_block']['recipe_feature_block_featured_recipe'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Featured Recipe'),
      '#default_value' => !empty($site_label_config->get('recipe_feature_block_featured_recipe')) ? $site_label_config->get('recipe_feature_block_featured_recipe') : static::RECIPE_FEATURE_BLOCK_FEATURED_RECIPE,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::RECIPE_FEATURE_BLOCK_FEATURED_RECIPE.'</strong>'
    ];
    $form['recipe_feature_block']['recipe_feature_block_eyebrow'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Eyebrow'),
      '#default_value' => !empty($site_label_config->get('recipe_feature_block_eyebrow')) ? $site_label_config->get('recipe_feature_block_eyebrow') : static::RECIPE_FEATURE_BLOCK_EYEBROW,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::RECIPE_FEATURE_BLOCK_EYEBROW.'</strong>'
    ];
    $form['recipe_feature_block']['recipe_feature_block_recipe_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Recipe title'),
      '#default_value' => !empty($site_label_config->get('recipe_feature_block_recipe_title')) ? $site_label_config->get('recipe_feature_block_recipe_title') : static::RECIPE_FEATURE_BLOCK_RECIPE_TITLE,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::RECIPE_FEATURE_BLOCK_RECIPE_TITLE.'</strong>'
    ];
    $form['recipe_feature_block']['recipe_feature_block_cta_link_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('CTA Link Title'),
      '#default_value' => !empty($site_label_config->get('recipe_feature_block_cta_link_title')) ? $site_label_config->get('recipe_feature_block_cta_link_title') : static::RECIPE_FEATURE_BLOCK_CTA_LINK_TITLE,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::RECIPE_FEATURE_BLOCK_CTA_LINK_TITLE.'</strong>'
    ];

    // MARS: Recommendations Module block
    $form['recommendations_module'] = array(
      '#type' => 'details',
      '#title' => $this->t('MARS: Recommendations Module'),
    );
    $form['recommendations_module']['recommendations_module_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => !empty($site_label_config->get('recommendations_module_title')) ? $site_label_config->get('recommendations_module_title') : static::RECOMMENDATIONS_MODULE_TITLE,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::RECOMMENDATIONS_MODULE_TITLE.'</strong>'
    ];

    // MARS: Search FAQs block
    $form['search_faqs'] = array(
      '#type' => 'details',
      '#title' => $this->t('MARS: Search FAQs'),
    );
    $form['search_faqs']['search_faqs_block_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('FAQ block title'),
      '#default_value' => !empty($site_label_config->get('search_faqs_block_title')) ? $site_label_config->get('search_faqs_block_title') : static::SEARCH_FAQS_BLOCK_TITLE,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::SEARCH_FAQS_BLOCK_TITLE.'</strong>'
    ];

    //MARS: Search page header block
    $form['search_page_header'] = array(
      '#type' => 'details',
      '#title' => $this->t('MARS: Search page header'),
    );
    $form['search_page_header']['search_page_header_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Search heading title'),
      '#default_value' => !empty($site_label_config->get('search_page_header_title')) ? $site_label_config->get('search_page_header_title') : static::SEARCH_PAGE_HEADER_TITLE,
      '#required' => TRUE,
      '#description' => static::FIELD_LIMIT_DESCRIPTION. ': <strong>'.static::SEARCH_PAGE_HEADER_TITLE.'</strong>'
    ];
    $form['actions']['reset'] = [
      '#type' => 'submit',
      '#weight' => 999,
      '#value' => $this->t('Reset'),
      '#submit' => array([$this, 'resetForm']),
      '#limit_validation_errors' => array()
    ];

    $form['#attached']['library'][] = 'mars_common/mars_common.character_page';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('mars_common.character_limit_page');
 
    $config->set('article_eyebrow', $form_state->getValue('article_eyebrow'))
      ->set('hero_block_eyebrow', $form_state->getValue('hero_block_eyebrow'))
      ->set('hero_block_title_link_url', $form_state->getValue('hero_block_title_link_url'))
      ->set('hero_block_title_label', $form_state->getValue('hero_block_title_label'))
      ->set('hero_block_override_title_label', $form_state->getValue('hero_block_override_title_label'))
      ->set('hero_block_cta_link_url', $form_state->getValue('hero_block_cta_link_url'))
      ->set('hero_block_cta_link_title', $form_state->getValue('hero_block_cta_link_title'))
      ->set('hero_block_card_eyebrow', $form_state->getValue('hero_block_card_eyebrow'))
      ->set('hero_block_card_title_label', $form_state->getValue('hero_block_card_title_label'))
      ->set('hero_block_card_title_link_url', $form_state->getValue('hero_block_card_title_link_url'))
      ->set('hero_block_card_cta_link_title', $form_state->getValue('hero_block_card_cta_link_title'))
      ->set('hero_block_card_cta_link_url', $form_state->getValue('hero_block_card_cta_link_url'))
      ->set('alert_banner_text', $form_state->getValue('alert_banner_text'))
      ->set('carousel_component_title', $form_state->getValue('carousel_component_title'))
      ->set('carousel_item_description', $form_state->getValue('carousel_item_description'))
      ->set('contact_help_banner_title', $form_state->getValue('contact_help_banner_title'))
      ->set('contact_help_banner_description', $form_state->getValue('contact_help_banner_description'))
      ->set('contact_help_banner_social_link_label', $form_state->getValue('contact_help_banner_social_link_label'))
      ->set('content_feature_module_eyebrow', $form_state->getValue('content_feature_module_eyebrow'))
      ->set('content_feature_module_title', $form_state->getValue('content_feature_module_title'))
      ->set('content_feature_module_description', $form_state->getValue('content_feature_module_description'))
      ->set('content_feature_module_button_label', $form_state->getValue('content_feature_module_button_label'))
      ->set('flexible_driver_title', $form_state->getValue('flexible_driver_title'))
      ->set('flexible_driver_description', $form_state->getValue('flexible_driver_description'))
      ->set('flexible_driver_cta_label', $form_state->getValue('flexible_driver_cta_label'))
      ->set('flexible_frame_header', $form_state->getValue('flexible_frame_header'))
      ->set('flexible_frame_item_title', $form_state->getValue('flexible_frame_item_title'))
      ->set('flexible_frame_cta_link_title', $form_state->getValue('flexible_frame_cta_link_title'))
      ->set('flexible_frame_cta_link_url', $form_state->getValue('flexible_frame_cta_link_url'))
      ->set('flexible_frame_item_description', $form_state->getValue('flexible_frame_item_description'))
      ->set('footer_block_link_url', $form_state->getValue('footer_block_link_url'))
      ->set('footer_block_link_title', $form_state->getValue('footer_block_link_title'))
      ->set('freeform_story_block_header_1', $form_state->getValue('freeform_story_block_header_1'))
      ->set('freeform_story_block_header_2', $form_state->getValue('freeform_story_block_header_2'))
      ->set('freeform_story_block_description', $form_state->getValue('freeform_story_block_description'))
      ->set('header_block_alert_banner_text', $form_state->getValue('header_block_alert_banner_text'))
      ->set('iframe_accessibility_title', $form_state->getValue('iframe_accessibility_title'))
      ->set('list_component_title', $form_state->getValue('list_component_title'))
      ->set('list_component_element_number', $form_state->getValue('list_component_element_number'))
      ->set('parent_page_header_eyebrow', $form_state->getValue('parent_page_header_eyebrow'))
      ->set('parent_page_header_title', $form_state->getValue('parent_page_header_title'))
      ->set('parent_page_header_description', $form_state->getValue('parent_page_header_description'))
      ->set('product_feature_block_eyebrow', $form_state->getValue('product_feature_block_eyebrow'))
      ->set('product_feature_block_title', $form_state->getValue('product_feature_block_title'))
      ->set('product_feature_block_background_color_override', $form_state->getValue('product_feature_block_background_color_override'))
      ->set('product_feature_block_button_label', $form_state->getValue('product_feature_block_button_label'))
      ->set('social_feed_block_title', $form_state->getValue('social_feed_block_title'))
      ->set('story_highlight_title', $form_state->getValue('story_highlight_title'))
      ->set('story_highlight_description', $form_state->getValue('story_highlight_description'))
      ->set('story_highlight_item_title', $form_state->getValue('story_highlight_item_title'))
      ->set('text_block_header', $form_state->getValue('text_block_header'))
      ->set('grid_card_title', $form_state->getValue('grid_card_title'))
      ->set('pdp_hero_eyebrow', $form_state->getValue('pdp_hero_eyebrow'))
      ->set('pdp_hero_available_sizes', $form_state->getValue('pdp_hero_available_sizes'))
      ->set('pdp_hero_nutrition_section_label', $form_state->getValue('pdp_hero_nutrition_section_label'))
      ->set('pdp_hero_nutrition_benefits_label', $form_state->getValue('pdp_hero_nutrition_benefits_label'))
      ->set('pdp_hero_diet_allergens_part_label', $form_state->getValue('pdp_hero_diet_allergens_part_label'))
      ->set('pdp_hero_cooking_instruction_label', $form_state->getValue('pdp_hero_cooking_instruction_label'))
      ->set('pdp_hero_more_information_label', $form_state->getValue('pdp_hero_more_information_label'))
      ->set('product_content_pair_title', $form_state->getValue('product_content_pair_title'))
      ->set('product_content_pair_master_card_eyebrow', $form_state->getValue('product_content_pair_master_card_eyebrow'))
      ->set('product_content_pair_master_card_title', $form_state->getValue('product_content_pair_master_card_title'))
      ->set('product_content_pair_cta_link_text', $form_state->getValue('product_content_pair_cta_link_text'))
      ->set('product_content_pair_card_eyebrow', $form_state->getValue('product_content_pair_card_eyebrow'))
      ->set('recipe_detail_hero_hint', $form_state->getValue('recipe_detail_hero_hint'))
      ->set('recipe_detail_hero_overlay_title', $form_state->getValue('recipe_detail_hero_overlay_title'))
      ->set('recipe_detail_hero_overlay_description', $form_state->getValue('recipe_detail_hero_overlay_description'))
      ->set('recipe_detail_hero_grocery_list_label', $form_state->getValue('recipe_detail_hero_grocery_list_label'))
      ->set('recipe_detail_hero_email_recipe_label', $form_state->getValue('recipe_detail_hero_email_recipe_label'))
      ->set('recipe_detail_hero_email_address_hint', $form_state->getValue('recipe_detail_hero_email_address_hint'))
      ->set('recipe_detail_hero_error_message', $form_state->getValue('recipe_detail_hero_error_message'))
      ->set('recipe_detail_hero_cta_title', $form_state->getValue('recipe_detail_hero_cta_title'))
      ->set('recipe_detail_hero_confirmation_message', $form_state->getValue('recipe_detail_hero_confirmation_message'))
      ->set('recipe_feature_block_featured_recipe', $form_state->getValue('recipe_feature_block_featured_recipe'))
      ->set('recipe_feature_block_eyebrow', $form_state->getValue('recipe_feature_block_eyebrow'))
      ->set('recipe_feature_block_recipe_title', $form_state->getValue('recipe_feature_block_recipe_title'))
      ->set('recipe_feature_block_cta_link_title', $form_state->getValue('recipe_feature_block_cta_link_title'))
      ->set('recommendations_module_title', $form_state->getValue('recommendations_module_title'))
      ->set('search_faqs_block_title', $form_state->getValue('search_faqs_block_title'))
      ->set('search_page_header_title', $form_state->getValue('search_page_header_title'))
      ->save();
      
    parent::submitForm($form, $form_state);
  }
  
    /**
   * {@inheritdoc}
   */
  public function resetForm(array &$form, FormStateInterface $form_state){
    $config = $this->config('mars_common.character_limit_page');

    $config->set('article_eyebrow', static::EYEBROW)
      ->set('hero_block_eyebrow', static::HERO_BLOCK_EYEBROW)
      ->set('hero_block_title_link_url', static::HERO_BLOCK_TITLE_LINK_URL)
      ->set('hero_block_title_label', static::HERO_BLOCK_TITLE_LABEL)
      ->set('hero_block_override_title_label', static::HERO_BLOCK_OVERRIDE_TITLE_LABEL)
      ->set('hero_block_cta_link_url', static::HERO_BLOCK_CTA_LINK_URL)
      ->set('hero_block_cta_link_title', static::HERO_BLOCK_CTA_LINK_TITLE)
      ->set('hero_block_card_eyebrow', static::HERO_BLOCK_CARD_EYEBROW)
      ->set('hero_block_card_title_label', static::HERO_BLOCK_CARD_TITLE_LABEL)
      ->set('hero_block_card_title_link_url', static::HERO_BLOCK_CARD_TITLE_LINK_URL)
      ->set('hero_block_card_cta_link_title', static::HERO_BLOCK_CARD_CTA_LINK_TITLE)
      ->set('hero_block_card_cta_link_url', static::HERO_BLOCK_CARD_CTA_LINK_URL)
      ->set('alert_banner_text', static::ALERT_BANNER_TEXT)
      ->set('carousel_component_title', static::CAROUSEL_COMPONENT_TITLE)
      ->set('carousel_item_description', static::CAROUSEL_ITEM_DESCRIPTION)
      ->set('contact_help_banner_title', static::CONTACT_HELP_BANNER_TITLE)
      ->set('contact_help_banner_description', static::CONTACT_HELP_BANNER_DESCRIPTION)
      ->set('contact_help_banner_social_link_label', static::CONTACT_HELP_BANNER_SOCIAL_LINK_LABEL)
      ->set('content_feature_module_eyebrow', static::CONTENT_FEATURE_MODULE_EYEBROW)
      ->set('content_feature_module_title', static::CONTENT_FEATURE_MODULE_TITLE)
      ->set('content_feature_module_description', static::CONTENT_FEATURE_MODULE_DESCRIPTION)
      ->set('content_feature_module_button_label', static::CONTENT_FEATURE_MODULE_BUTTON_LABEL)
      ->set('flexible_driver_title', static::FLEXIBLE_DRIVER_TITLE)
      ->set('flexible_driver_description', static::FLEXIBLE_DRIVER_DESCRIPTION)
      ->set('flexible_driver_cta_label', static::FLEXIBLE_DRIVER_CTA_LABEL)
      ->set('flexible_frame_header', static::FLEXIBLE_FRAME_HEADER)
      ->set('flexible_frame_item_title', static::FLEXIBLE_FRAME_ITEM_TITLE)
      ->set('flexible_frame_cta_link_title', static::FLEXIBLE_FRAME_CTA_LINK_TITLE)
      ->set('flexible_frame_cta_link_url', static::FLEXIBLE_FRAME_CTA_LINK_URL)
      ->set('flexible_frame_item_description', static::FLEXIBLE_FRAME_ITEM_DESCRIPTION)
      ->set('footer_block_link_url', static::FOOTER_BLOCK_LINK_URL)
      ->set('footer_block_link_title', static::FOOTER_BLOCK_LINK_TITLE)
      ->set('freeform_story_block_header_1', static::FREEFORM_STORY_BLOCK_HEADER1)
      ->set('freeform_story_block_header_2', static::FREEFORM_STORY_BLOCK_HEADER2)
      ->set('freeform_story_block_description', static::FREEFORM_STORY_BLOCK_DESCRIPTION)
      ->set('header_block_alert_banner_text', static::HEADER_BLOCK_ALERT_BANNER_TEXT)
      ->set('iframe_accessibility_title', static::IFRAME_ACCESSIBILITY_TITLE)
      ->set('list_component_title', static::LIST_COMPONENT_TITLE)
      ->set('list_component_element_number', static::LIST_COMPONENT_ELEMENT_NUMBER)
      ->set('parent_page_header_eyebrow', static::PARENT_PAGE_HEADER_EYEBROW)
      ->set('parent_page_header_title', static::PARENT_PAGE_HEADER_TITLE)
      ->set('parent_page_header_description', static::PARENT_PAGE_HEADER_DESCRIPTION)
      ->set('product_feature_block_eyebrow', static::PRODUCT_FEATURE_BLOCK_EYEBROW)
      ->set('product_feature_block_title', static::PRODUCT_FEATURE_BLOCK_TITLE)
      ->set('product_feature_block_background_color_override', static::PRODUCT_FEATURE_BLOCK_BACKGROUND_COLOR_OVERRIDE)
      ->set('product_feature_block_button_label', static::PRODUCT_FEATURE_BLOCK_BUTTON_LABEL)
      ->set('social_feed_block_title', static::SOCIAL_FEED_BLOCK_TITLE)
      ->set('story_highlight_title', static::STORY_HIGHLIGHT_TITLE)
      ->set('story_highlight_description', static::STORY_HIGHLIGHT_DESCRIPTION)
      ->set('story_highlight_item_title', static::STORY_HIGHLIGHT_ITEM_TITLE)
      ->set('text_block_header', static::TEXT_BLOCK_HEADER)
      ->set('grid_card_title', static::GRID_CARD_TITLE)
      ->set('pdp_hero_eyebrow', static::PDP_HERO_EYEBROW)
      ->set('pdp_hero_available_sizes', static::PDP_HERO_AVAILABLE_SIZES)
      ->set('pdp_hero_nutrition_section_label', static::PDP_HERO_NUTRITION_SECTION_LABEL)
      ->set('pdp_hero_nutrition_benefits_label', static::PDP_HERO_NUTRITION_BENEFITS_LABEL)
      ->set('pdp_hero_diet_allergens_part_label', static::PDP_HERO_DIET_ALLERGENS_PART_LABEL)
      ->set('pdp_hero_cooking_instruction_label', static::PDP_HERO_COOKING_INSTRUCTION_LABEL)
      ->set('pdp_hero_more_information_label', static::PDP_HERO_MORE_INFORMATION_LABEL)
      ->set('product_content_pair_title', static::PRODUCT_CONTENT_PAIR_TITLE)
      ->set('product_content_pair_master_card_eyebrow', static::PRODUCT_CONTENT_PAIR_MASTER_CARD_EYEBROW)
      ->set('product_content_pair_master_card_title', static::PRODUCT_CONTENT_PAIR_MASTER_CARD_TITLE)
      ->set('product_content_pair_cta_link_text', static::PRODUCT_CONTENT_PAIR_CTA_LINK_TEXT)
      ->set('product_content_pair_card_eyebrow', static::PRODUCT_CONTENT_PAIR_CARD_EYEBROW)
      ->set('recipe_detail_hero_hint', static::RECIPE_DETAIL_HERO_HINT)
      ->set('recipe_detail_hero_overlay_title', static::RECIPE_DETAIL_HERO_OVERLAY_TITLE)
      ->set('recipe_detail_hero_overlay_description', static::RECIPE_DETAIL_HERO_OVERLAY_DESCRIPTION)
      ->set('recipe_detail_hero_grocery_list_label', static::RECIPE_DETAIL_HERO_GROCERY_LIST_LABEL)
      ->set('recipe_detail_hero_email_recipe_label', static::RECIPE_DETAIL_HERO_EMAIL_RECIPE_LABEL)
      ->set('recipe_detail_hero_email_address_hint', static::RECIPE_DETAIL_HERO_EMAIL_ADDRESS_HINT)
      ->set('recipe_detail_hero_error_message', static::RECIPE_DETAIL_HERO_ERROR_MESSAGE)
      ->set('recipe_detail_hero_cta_title', static::RECIPE_DETAIL_HERO_CTA_TITLE)
      ->set('recipe_detail_hero_confirmation_message', static::RECIPE_DETAIL_HERO_CONFIRMATION_MESSAGE)
      ->set('recipe_feature_block_featured_recipe', static::RECIPE_FEATURE_BLOCK_FEATURED_RECIPE)
      ->set('recipe_feature_block_eyebrow', static::RECIPE_FEATURE_BLOCK_EYEBROW)
      ->set('RECIPE_FEATURE_BLOCK_RECIPE_TITLE', static::HERO_BLOCK_EYEBROW)
      ->set('recipe_feature_block_cta_link_title', static::RECIPE_FEATURE_BLOCK_CTA_LINK_TITLE)
      ->set('recommendations_module_title', static::RECOMMENDATIONS_MODULE_TITLE)
      ->set('search_faqs_block_title', static::SEARCH_FAQS_BLOCK_TITLE)
      ->set('search_page_header_title', static::SEARCH_PAGE_HEADER_TITLE)
      ->save();

      \Drupal::messenger()->addStatus($this->t('The configuration options have been reset.'));
  }
}
