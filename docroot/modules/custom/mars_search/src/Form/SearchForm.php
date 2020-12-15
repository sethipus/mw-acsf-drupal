<?php

namespace Drupal\mars_search\Form;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mars_search\Processors\SearchHelperInterface;
use Drupal\mars_search\Processors\SearchQueryParserInterface;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_search\SearchProcessFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * SearchForm.
 */
class SearchForm extends FormBase {


  /**
   * Search helper.
   *
   * @var \Drupal\mars_search\Processors\SearchHelperInterface
   */
  protected $searchHelper;

  /**
   * Language helper service.
   *
   * @var \Drupal\mars_common\LanguageHelper
   */
  private $languageHelper;

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'mars_search_form';
  }

  /**
   * Constructs a new SearchForm.
   *
   * @param \Drupal\mars_search\SearchProcessFactoryInterface $searchProcessor
   *   Search process factory.
   * @param \Drupal\mars_common\LanguageHelper $language_helper
   *   Language helper service.
   */
  public function __construct(
    SearchProcessFactoryInterface $searchProcessor,
    LanguageHelper $language_helper
  ) {
    $this->searchHelper = $searchProcessor->getProcessManager('search_helper');;
    $this->languageHelper = $language_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('mars_search.search_factory'),
      $container->get('mars_common.language_helper')
    );
  }

  /**
   * Builds search form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param bool $autocomplete
   *   Enables/disables autocomplete for the form.
   * @param array $grid_options
   *   Search grid specific options like preset filters, grid id etc.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $autocomplete = TRUE, array $grid_options = []) {
    $keys = $this->searchHelper->request->get(SearchHelperInterface::MARS_SEARCH_SEARCH_KEY);
    $search_input_classes = ['search-input__field'];
    if ($autocomplete) {
      $search_input_classes[] = 'mars-autocomplete-field';
      $form['#attached']['library'][] = 'mars_search/autocomplete';
    }

    // Generating grid options query string.
    $grid_query = '';
    if (!empty($grid_options['filters'])) {
      $grid_query = UrlHelper::buildQuery($grid_options['filters']);
    }
    // Adding FAQ-specific class just to have it.
    if (!empty($grid_options['filters']['faq'])) {
      $search_input_classes[] = 'mars-autocomplete-field-faq';
    }
    $form['search'] = [
      '#type' => 'textfield',
      '#attributes' => [
        'placeholder' => $this->languageHelper->translate('Search'),
        'class' => $search_input_classes,
        'autocomplete' => 'off',
        'data-grid-query' => $grid_query,
        'data-grid-id' => !empty($grid_options['grid_id']) ? $grid_options['grid_id'] : SearchQueryParserInterface::MARS_SEARCH_DEFAULT_SEARCH_ID,
      ],
      '#default_value' => $keys,
    ];
    $form['actions'] = [
      '#type' => 'actions',
      // We don't need submit button be visible.
      '#attributes' => ['class' => ['hidden']],
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->languageHelper->translate('Submit'),
    ];
    $form_state->set('grid_options', $grid_options);

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
    $grid_options = $form_state->get('grid_options');
    // Default search ID is 1.
    $search_id = !empty($grid_options['grid_id']) ? $grid_options['grid_id'] : SearchQueryParserInterface::MARS_SEARCH_DEFAULT_SEARCH_ID;

    $keys = $form_state->getValue('search');

    $url = $this->searchHelper->getCurrentUrl();
    $options = $url->getOptions();

    // Either change or delete "search" URL parameter.
    if ($keys) {
      $options['query'][SearchHelperInterface::MARS_SEARCH_SEARCH_KEY][$search_id] = $keys;
      // Adding FAQ specific flag.
      if (!empty($grid_options['filters']['faq'])) {
        $options['query']['faq'] = TRUE;
      }
    }
    else {
      unset($options['query'][SearchHelperInterface::MARS_SEARCH_SEARCH_KEY][$search_id]);
    }

    $url->setOptions($options);

    $form_state->setRedirectUrl($url);
  }

}
