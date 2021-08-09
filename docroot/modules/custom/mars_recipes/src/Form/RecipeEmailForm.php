<?php

namespace Drupal\mars_recipes\Form;

use Drupal\Component\Utility\Html;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeInterface;

/**
 * RecipeViewForm contains logic of email form.
 */
class RecipeEmailForm extends FormBase implements FormInterface {

  /**
   * The context recipe of the form.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $recipe;
  /**
   * The context data of the form.
   *
   * @var array|null
   */
  protected $contextData;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'recipe_email_form';
  }

  /**
   * Set the recipe of this form.
   *
   * @param \Drupal\node\NodeInterface $recipe
   *   The recipe that will be set in the form.
   */
  public function setRecipe(NodeInterface $recipe) {
    $this->recipe = $recipe;
  }

  /**
   * Set the context data of this form.
   *
   * @param array|null $data
   *   The context data that will be set in the form.
   */
  public function setContextData(?array $data) {
    $this->contextData = $data;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Add the context data to the form.
    $form['context_data']['#type'] = 'value';
    $form['context_data']['#value'] = $this->contextData;

    $form['#id'] = Html::getId($this->getFormId());
    if ($form_state->get('email_form_submitted')) {
      $form['#theme'] = 'recipe_email_final';
    }
    else {
      $form['grocery_list'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Email a grocery list'),
        '#default_value' => FALSE,
        '#description' => $this->t('Email a grocery lis'),
      ];

      $form['email_recipe'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Email a recipe'),
        '#default_value' => FALSE,
        '#description' => $this->t('Email a recipe'),
      ];

      $form['email'] = [
        '#type' => 'email',
        '#title' => $this->t('Email address'),
        '#required' => TRUE,
        '#description' => $this->t('Email address'),
      ];

      $form['#theme'] = 'recipe_email';

      $form['actions'] = [
        '#type' => 'actions',
      ];

      $form['actions']['submit'] = [
        '#type' => 'submit',
        '#name' => 'submit',
        '#value' => 'Submit',
        '#validate' => ['::validateEmailForm'],
        '#submit' => ['::submitEmailForm'],
        '#ajax' => [
          'callback' => [static::class, 'ajaxReplaceForm'],
          'event' => 'click',
          'wrapper' => $form['#id'],
        ],
      ];
    }

    return $form;
  }

  /**
   * Ajax callback to replace the email recipe form.
   */
  public static function ajaxReplaceForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\Core\Render\RendererInterface $renderer */
    $renderer = \Drupal::service('renderer');
    // Render the form.
    $output = $renderer->renderRoot($form);

    $response = new AjaxResponse();
    $response->setAttachments($form['#attached']);

    // Replace the form completely and return it.
    return $response->addCommand(new ReplaceCommand('#' . $form['#id'], $output));
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * Submit handler for email recipe form.
   *
   * @param array $form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state object.
   */
  public function submitEmailForm(array $form, FormStateInterface $form_state) {
    $form_state->set('email_form_submitted', TRUE);
    $form_state->setRebuild(TRUE);
  }

  /**
   * Validates the vote action.
   *
   * @param array $form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state object.
   */
  public function validateEmailForm(array &$form, FormStateInterface $form_state) {

  }

}
