<?php

namespace Drupal\mars_search\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\mars_search\Processors\SearchHelperInterface;
use Drupal\mars_search\Processors\SearchQueryParserInterface;
use Drupal\mars_common\LanguageHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * SearchOverlayForm.
 */
class SearchOverlayForm extends FormBase {

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'mars_search_overlay_form';
  }

  /**
   * The request object.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Language helper service.
   *
   * @var \Drupal\mars_common\LanguageHelper
   */
  private $languageHelper;

  /**
   * Config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $config;

  /**
   * Constructs a new SearchOverlayForm.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack object.
   * @param \Drupal\mars_common\LanguageHelper $language_helper
   *   The language helper service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The config factory service.
   */
  public function __construct(
    RequestStack $request_stack,
    LanguageHelper $language_helper,
    ConfigFactoryInterface $config
  ) {
    $this->requestStack = $request_stack;
    $this->languageHelper = $language_helper;
    $this->config = $config;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('mars_common.language_helper'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $search_placeholder = $this->config->get('mars_common.site_labels')->get('header_search_overlay');
    $form['search'] = [
      '#type' => 'textfield',
      '#attributes' => [
        'class' => [
          'mars-autocomplete-field',
          'mars-cards-view',
          'data-layer-search-form-input',
          'search-input__field',
        ],
        'autocomplete' => 'off',
        'placeholder' => $this->languageHelper->translate($search_placeholder),
        'aria-label' => $this->languageHelper->translate('Search input field'),
        // This is needed for correct work of SearchQueryParser.
        'data-grid-id' => SearchQueryParserInterface::MARS_SEARCH_DEFAULT_SEARCH_ID,
      ],
    ];
    $form['actions'] = [
      '#type' => 'actions',
      '#attributes' => [
        'aria-hidden' => 'true',
      ],
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->languageHelper->translate('Submit'),
      '#attributes' => [
        'aria-hidden' => 'true',
        'tabindex' => '-1',
      ],
    ];

    $form['#attached']['library'][] = 'mars_search/autocomplete';
    return $form;
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Default search ID is 1.
    $search_id = SearchQueryParserInterface::MARS_SEARCH_DEFAULT_SEARCH_ID;

    $keys = $form_state->getValue('search');

    $url = Url::fromUri('internal:/' . SearchHelperInterface::MARS_SEARCH_SEARCH_PAGE_PATH);
    $options = $url->getOptions();

    if ($keys) {
      $options['query'][SearchHelperInterface::MARS_SEARCH_SEARCH_KEY][$search_id] = $keys;
    }
    else {
      unset($options['query'][SearchHelperInterface::MARS_SEARCH_SEARCH_KEY][$search_id]);
    }

    $url->setOptions($options);
    $form_state->setRedirectUrl($url);
  }

}
