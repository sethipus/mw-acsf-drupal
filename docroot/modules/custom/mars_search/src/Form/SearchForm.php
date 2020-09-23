<?php

namespace Drupal\mars_search\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mars_search\SearchHelperInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * SearchForm.
 */
class SearchForm extends FormBase {

  /**
   * Request stack that controls the lifecycle of requests.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  private $request;

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
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack object.
   * @param \Drupal\mars_search\SearchHelperInterface $search_helper
   *   Search helper.
   */
  public function __construct(RequestStack $request_stack, SearchHelperInterface $search_helper) {
    $this->request = $request_stack->getMasterRequest();
    $this->searchHelper = $search_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('mars_search.search_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['search'] = [
      '#type' => 'textfield',
      '#attributes' => [
        'placeholder' => $this->t('Search'),
        'class' => ['search-input__field'],
      ],
      '#default_value' => $this->request->get('search'),
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
    if ($keys) {
      $options = $url->getOptions();
      $options['query'][SearchHelperInterface::MARS_SEARCH_SEARCH_KEY] = $keys;
      $url->setOptions($options);
    }

    $form_state->setRedirectUrl($url);
  }

}
