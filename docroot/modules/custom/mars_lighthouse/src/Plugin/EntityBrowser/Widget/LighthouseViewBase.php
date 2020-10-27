<?php

namespace Drupal\mars_lighthouse\Plugin\EntityBrowser\Widget;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\entity_browser\WidgetBase;
use Drupal\entity_browser\WidgetValidationManager;
use Drupal\mars_lighthouse\LighthouseException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Drupal\mars_lighthouse\LighthouseInterface;
use Drupal\Core\Pager\PagerManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Base class for lighthouse widget view.
 */
abstract class LighthouseViewBase extends WidgetBase implements ContainerFactoryPluginInterface {

  /**
   * Limit of items presented in a gallery.
   */
  const PAGE_LIMIT = 12;

  /**
   * Lighthouse adapter.
   *
   * @var \Drupal\mars_lighthouse\LighthouseInterface
   */
  protected $lighthouseAdapter;

  /**
   * Page manager.
   *
   * @var \Drupal\Core\Pager\PagerManagerInterface
   */
  protected $pageManager;

  /**
   * Current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * Media Type.
   *
   * @var string
   */
  protected $mediaType = 'image';

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EventDispatcherInterface $event_dispatcher,
    EntityTypeManagerInterface $entity_type_manager,
    WidgetValidationManager $validation_manager,
    LighthouseInterface $lighthouse,
    PagerManagerInterface $page_manager,
    RequestStack $request_stack
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $event_dispatcher, $entity_type_manager, $validation_manager);
    $this->lighthouseAdapter = $lighthouse;
    $this->pageManager = $page_manager;
    $this->currentRequest = $request_stack->getCurrentRequest();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('event_dispatcher'),
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.entity_browser.widget_validation'),
      $container->get('lighthouse.adapter'),
      $container->get('pager.manager'),
      $container->get('request_stack')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareEntities(array $form, FormStateInterface $form_state) {
    $userInput = $form_state->cleanValues()->getUserInput();
    // If you are using checkboxes - you will get
    // an array of them, which might be filtered.
    // But if you are using radios - then you will get a string.
    $selected_rows = is_array($userInput['view']) ? array_filter($userInput['view']) : [$userInput['view'] => ''];

    $entities = [];
    if (!empty($selected_rows)) {
      foreach ($selected_rows as $assetId => $true) {
        $entities[] = $this->lighthouseAdapter->getMediaEntity($assetId);
      }
    }
    return $entities;
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(array &$original_form, FormStateInterface $form_state, array $additional_widget_parameters) {
    $form = parent::getForm($original_form, $form_state, $additional_widget_parameters);
    // Disable caching on this form.
    $form_state->setCached(FALSE);

    // Get filter values.
    $text_value = $form_state->getValue('text') ?? $this->currentRequest->query->get('text') ?? '';
    $brand_value = $form_state->getValue('brand') ?? $this->currentRequest->query->get('brand') ?? '';
    $market_value = $form_state->getValue('market') ?? $this->currentRequest->query->get('market') ?? '';
    // Get filter options.
    try {
      $brand_options = $this->lighthouseAdapter->getBrands();
      $market_options = $this->lighthouseAdapter->getMarkets();
    }
    catch (LighthouseException $e) {
      $brand_options = $market_options = [];
    }

    $form['#attached']['library'] = [
      'mars_lighthouse/lighthouse-gallery',
    ];

    $form['filter']['text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Text'),
      '#size' => 60,
      '#default_value' => $text_value,
    ];
    $form_state->setValue('text', $text_value);

    $form['filter']['brand'] = [
      '#type' => 'select',
      '#title' => $this->t('Brand'),
      '#options' => $brand_options,
      '#default_value' => $brand_value,
    ];
    $form['filter']['market'] = [
      '#type' => 'select',
      '#title' => $this->t('Market'),
      '#options' => $market_options,
      '#default_value' => $market_value,
    ];

    $form['filter']['submit'] = [
      '#type' => 'submit',
      '#submit' => [[$this, 'searchCallback']],
      '#value' => $this->t('Filter'),
    ];

    $total_found = 0;
    $form['view'] = $this->getView($total_found, $form_state);

    if ($total_found) {
      $this->pageManager->createPager($total_found, self::PAGE_LIMIT);
      $form['pagination'] = [
        '#type' => 'pager',
        '#quantity' => 3,
        '#parameters' => [
          'text' => $text_value,
          'brand' => $brand_value,
          'market' => $market_value,
        ],
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validate(array &$form, FormStateInterface $form_state) {
    // TODO: Add a validation.
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array &$element, array &$form, FormStateInterface $form_state) {
    $entities = $this->prepareEntities($form, $form_state);
    $this->selectEntities($entities, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    return [];
  }

  /**
   * Ajax search response.
   */
  public function searchCallback(array &$form, FormStateInterface $form_state) {
    $form_state->setRebuild(TRUE);
  }

  /**
   * Get render array to view Lighthouse gallery.
   *
   * @param int $total_found
   *   Returns the amount of results.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state object which is used to process pager and search text.
   *
   * @return array
   *   Render array.
   */
  abstract protected function getView(&$total_found, FormStateInterface $form_state);

}
