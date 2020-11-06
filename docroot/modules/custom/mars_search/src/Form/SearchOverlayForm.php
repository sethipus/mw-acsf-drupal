<?php

namespace Drupal\mars_search\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\mars_search\SearchHelperInterface;
use Drupal\mars_search\SearchQueryParserInterface;
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
   * Constructs a new SearchOverlayForm.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack object.
   */
  public function __construct(RequestStack $request_stack) {
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['search'] = [
      '#type' => 'textfield',
      '#attributes' => [
        'class' => [
          'mars-autocomplete-field',
          'mars-cards-view',
        ],
        'autocomplete' => 'off',
        'aria-label' => $this->t('Search input field'),
        // This is needed for correct work of SearchQueryParser.
        'data-grid-id' => SearchQueryParserInterface::MARS_SEARCH_DEFAULT_SEARCH_ID,
      ],
    ];
    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
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
