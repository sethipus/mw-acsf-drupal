<?php

namespace Drupal\mars_recipes\Form;

use Drupal\Component\Utility\Html;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * RecipeViewForm contains logic of email form.
 */
class RecipeEmailForm extends FormBase implements FormInterface, ContainerInjectionInterface {

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
   * ThemeConfiguratorParser.
   *
   * @var \Drupal\mars_common\ThemeConfiguratorParser
   */
  protected $themeConfiguratorParser;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    ThemeConfiguratorParser $theme_config_parser
  ) {
    $this->themeConfiguratorParser = $theme_config_parser;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('mars_common.theme_configurator_parser')
    );
  }

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
    // Add the context data to the form.
    $form['context_recipe']['#type'] = 'value';
    $form['context_recipe']['#value'] = $this->recipe ? $this->recipe->id() : NULL;

    $form['#id'] = Html::getId($this->getFormId());
    if ($form_state->get('email_form_submitted') && !$form_state->get('validation_error')) {
      $form['#theme'] = 'recipe_email_final';
      $form['png_asset']['#type'] = 'value';
      $form['png_asset']['#value'] = $this->themeConfiguratorParser
        ->getUrlForFile('png_asset')
        ->toString();
    }
    else {
      if (($this->recipe && $this->recipe->get('field_recipe_ingredients')->getValue()) &&
        !empty($this->contextData['checkboxes_container']['grocery_list'])) {
        $form['grocery_list'] = [
          '#type' => 'checkbox',
          '#title' => $this->contextData['checkboxes_container']['grocery_list'] ?? $this->t('Email a grocery list'),
          '#default_value' => FALSE,
          '#description' => $this->contextData['checkboxes_container']['grocery_list'] ?? $this->t('Email a grocery list'),
        ];
      }

      if (!empty($this->contextData['checkboxes_container']['email_recipe'])) {
        $form['email_recipe'] = [
          '#type' => 'checkbox',
          '#title' => $this->contextData['checkboxes_container']['email_recipe'] ?? $this->t('Email a recipe'),
          '#default_value' => FALSE,
          '#description' => $this->contextData['checkboxes_container']['email_recipe'] ?? $this->t('Email a recipe'),
        ];
      }

      $form['email'] = [
        '#type' => 'textfield',
        '#title' => $this->contextData['email_address_hint'] ?? $this->t('Email address'),
        '#description' => $this->contextData['email_address_hint'] ?? $this->t('Email address'),
      ];

      if ($this->contextData['captcha']) {
        $form['captcha'] = [
          '#type' => 'captcha',
          '#captcha_type' => 'recaptcha/reCAPTCHA',
          '#captcha_admin_mode' => TRUE,
        ];
      }

      $form['#theme'] = 'recipe_email';
      $form['brand_shape']['#type'] = 'value';
      $form['brand_shape']['#value'] = $this->themeConfiguratorParser
        ->getBrandShapeWithoutFill();

      $form['actions'] = [
        '#type' => 'actions',
      ];

      $form['actions']['submit'] = [
        '#type' => 'submit',
        '#name' => 'submit',
        '#value' => $this->contextData['cta_title'] ?? $this->t('Submit'),
        '#submit' => ['::submitEmailForm'],
        '#ajax' => [
          'callback' => [static::class, 'ajaxReplaceForm'],
          'event' => 'click',
          'wrapper' => $form['#id'],
          'progress' => [],
        ],
      ];
    }

    return $form;
  }

  /**
   * Ajax callback to replace the email recipe form.
   */
  public static function ajaxReplaceForm(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    $email = $form_state->getValue('email');
    $grocery_value = $form_state->getValue('grocery_list');
    $email_recipe_value = $form_state->getValue('email_recipe');

    // Clear error styles.
    $response->addCommand(new InvokeCommand(
      '#' . $form['#id'] . ' input, .g-recaptcha iframe',
      'removeClass',
      ['error-border']
    ));

    // Error border for checkboxes.
    if (!$grocery_value && !$email_recipe_value &&
      (isset($form['email_recipe']) || isset($form['grocery_list']))) {
      $response->addCommand(new InvokeCommand(
        '[name=email_recipe], [name=grocery_list]',
        'addClass',
        ['error-border']
      ));
    }

    // Error border for email field.
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $response->addCommand(new InvokeCommand(
        '[name=email]',
        'addClass',
        ['error-border']
      ));
    }

    if (isset($form['captcha']) && !$form_state->get('is_captcha_valid')) {
      $response->addCommand(new InvokeCommand(
        '.g-recaptcha iframe',
        'addClass',
        ['error-border']
      ));
    }

    // Add error message.
    if (!static::isValidSubmit($form, $form_state)) {
      $error_message = $form_state->getValue('context_data')['error_message'];
      $response->addCommand(new HtmlCommand(
        '.email-recipe-message',
        $error_message
      ));
    }
    else {
      // Success response.
      /** @var \Drupal\Core\Render\RendererInterface $renderer */
      $renderer = \Drupal::service('renderer');
      // Render the form.
      $output = $renderer->renderRoot($form);
      $response->setAttachments($form['#attached']);

      // Replace the form completely and return it.
      $response->addCommand(new ReplaceCommand('#' . $form['#id'], $output));
    }
    return $response;
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
  public static function submitEmailForm(array $form, FormStateInterface $form_state) {
    if (static::isValidSubmit($form, $form_state)) {
      $form_state->set('validation_error', FALSE);
      $form_state->set('email_form_submitted', TRUE);
      $email = $form_state->getValue('email');

      $current_langcode = \Drupal::languageManager()
        ->getCurrentLanguage()
        ->getId();

      $recipe_id = $form_state->getValue('context_recipe');
      $recipe = Node::load($recipe_id);

      $send_grocery_list = $form_state->getValue('grocery_list');
      if ($send_grocery_list) {
        try {
          $ingredients = ($recipe)
            ? $recipe->get('field_recipe_ingredients')->getValue()
            : NULL;
          \Drupal::service('plugin.manager.mail')->mail(
            'mars_recipe',
            'grocery',
            $email,
            $current_langcode,
            [
              'ingredients' => $ingredients,
            ]
          );
        }
        catch (\Exception $e) {
          \Drupal::logger('mars_recipe')->error(
            t('There was an error during sending email report: @message', ['@message' => $e->getMessage()])
          );
        }
      }

      $send_recipe_email = $form_state->getValue('email_recipe');
      if ($send_recipe_email || (!isset($form['email_recipe']) && !isset($form['grocery_list']))) {
        try {
          \Drupal::service('plugin.manager.mail')->mail(
            'mars_recipe',
            'email',
            $email,
            $current_langcode,
            [
              'recipe_url' => ($recipe)
              ? \Drupal::request()->getSchemeAndHttpHost() . $recipe->toUrl()->toString()
              : NULL,
            ]
          );
        }
        catch (\Exception $e) {
          \Drupal::logger('mars_recipe')->error(
            t('There was an error during sending email report: @message', ['@message' => $e->getMessage()])
          );
        }
      }
    }
    else {
      $form_state->set('validation_error', TRUE);
    }
    $form_state->setRebuild(TRUE);
  }

  /**
   * Validate submit data.
   *
   * @param array $form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state object.
   *
   * @return bool
   *   Valid or not.
   */
  public static function isValidSubmit(array $form, FormStateInterface $form_state): bool {
    $email = $form_state->getValue('email');
    $grocery_value = $form_state->getValue('grocery_list');
    $email_recipe_value = $form_state->getValue('email_recipe');

    if (isset($form['captcha'])) {
      $is_captcha_valid = (!$form_state->get('is_captcha_valid'))
        ? recaptcha_captcha_validation(
          'recaptcha',
          'response',
          $form['captcha'],
          $form_state
        )
        : $form_state->get('is_captcha_valid');
      $form_state->set('is_captcha_valid', $is_captcha_valid);
    }

    return !((!$grocery_value && !$email_recipe_value &&
        (isset($form['email_recipe']) || isset($form['grocery_list'])))
    || empty($email)
    || !filter_var($email, FILTER_VALIDATE_EMAIL)
    || (isset($is_captcha_valid) && !$is_captcha_valid));
  }

}
