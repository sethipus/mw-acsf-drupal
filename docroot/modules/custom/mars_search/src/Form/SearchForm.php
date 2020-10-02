<?php

namespace Drupal\mars_search\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mars_search\SearchHelperInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * SearchForm.
 */
class SearchForm extends FormBase {


  /**
   * Search helper.
   *
   * @var \Drupal\mars_search\SearchHelperInterface
   */
  protected $searchHelper;

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
   * Constructs a new SearchOverlayForm.
   *
   * @param \Drupal\mars_search\SearchHelperInterface $search_helper
   *   Search helper.
   */
  public function __construct(SearchHelperInterface $search_helper) {
    $this->searchHelper = $search_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('mars_search.search_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $autocomplete = TRUE) {
    $keys = $this->searchHelper->request->get(SearchHelperInterface::MARS_SEARCH_SEARCH_KEY);
    $search_input_classes = ['search-input__field'];
    if ($autocomplete) {
      $search_input_classes[] = 'mars-autocomplete-field';
      $form['#attached']['library'][] = 'mars_search/autocomplete';
    }
    $form['search'] = [
      '#type' => 'textfield',
      '#attributes' => [
        'placeholder' => $this->t('Search'),
        'class' => $search_input_classes,
        'autocomplete' => 'off',
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
      '#value' => $this->t('Submit'),
    ];

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
    $keys = $form_state->getValue('search');

    $url = $this->searchHelper->getCurrentUrl();
    $options = $url->getOptions();

    // Either change or delete "search" URL parameter.
    if ($keys) {
      $options['query'][SearchHelperInterface::MARS_SEARCH_SEARCH_KEY] = $keys;
    }
    else {
      unset($options['query'][SearchHelperInterface::MARS_SEARCH_SEARCH_KEY]);
    }
    $url->setOptions($options);

    $form_state->setRedirectUrl($url);
  }

}
