<?php

namespace Drupal\mars_common\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\poll\Form\PollViewForm;
use Drupal\poll\PollInterface;
use Drupal\poll\PollVoteStorage;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Mars poll view form.
 */
class MarsPollViewForm extends PollViewForm {

  /**
   * The Date time Service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * The Poll vote storage service.
   *
   * @var \Drupal\poll\PollVoteStorage
   */
  protected $pollVoteStorage;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    TimeInterface $time,
    PollVoteStorage $voteStorage
  ) {
    $this->time = $time;
    $this->pollVoteStorage = $voteStorage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('datetime.time'),
      $container->get('poll_vote.storage')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(
    array $form,
    FormStateInterface $form_state,
    Request $request = NULL,
    $view_mode = 'full'
  ) {
    // Add the poll to the form.
    $form['poll']['#type'] = 'value';
    $form['poll']['#value'] = $this->poll;

    $form['#view_mode'] = $view_mode;

    if ($this->showResults($this->poll, $form_state)) {

      // Check if the user already voted. The form is still being built but
      // the Vote button won't be added so the submit callbacks will not be
      // called. Directly check for the request method and use the raw user
      // input.
      if ($request->isMethod('POST') && $this->poll->hasUserVoted()) {
        $input = $form_state->getUserInput();
        if (isset($input['op']) && $input['op'] == $this->t('Vote')) {
          $this->messenger()->addError($this->t('Your vote for this poll has already been submitted.'));
        }
      }

      $form['results'] = $this->showPollResults($this->poll, $view_mode);

      // For all view modes except full and block (as block displays it as the
      // block title), display the question.
      if ($view_mode != 'full' && $view_mode != 'block') {
        $form['results']['#show_question'] = TRUE;
      }
    }
    else {
      $options = $this->poll->getOptions();
      if ($options) {
        $form['choice'] = [
          '#type' => 'radios',
          '#title' => $this->t('Choices'),
          '#title_display' => 'invisible',
          '#options' => $options,
        ];
      }
      $form['#theme'] = 'poll_vote';
      $form['#entity'] = $this->poll;
      $form['#action'] = $this->poll->toUrl()->setOption(
        'query',
        $this->getRedirectDestination()->getAsArray()
      )->toString();
      // Set a flag to hide results which will be removed if we want to view
      // results when the form is rebuilt.
      $form_state->set('show_results', FALSE);

      // For all view modes except full and block (as block displays it as the
      // block title), display the question.
      if ($view_mode != 'full' && $view_mode != 'block') {
        $form['#show_question'] = TRUE;
      }

    }

    $form['actions'] = $this->actions($form, $form_state, $this->poll);

    $form['#cache'] = ['max-age' => 0];
    $form['actions']['cancel'] = NULL;
    if ($form['actions']['vote']['#ajax'] ?? NULL) {
      $form['actions']['vote']['#ajax']['progress'] = FALSE;
    }
    $form['actions']['vote']['#submit'][] = 'poll_form_submit_action_callback';
    if ($form['actions']['result']['#ajax'] ?? NULL) {
      $form['actions']['result']['#ajax']['progress'] = FALSE;
    }

    return $form;
  }

  /**
   * Save a user's vote submit function.
   *
   * @param array $form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form State object.
   */
  public function save(array $form, FormStateInterface $form_state) {
    $options = [];
    $options['chid'] = $form_state->getValue('choice');
    $options['uid'] = $this->currentUser()->id();
    $options['pid'] = $form_state->getValue('poll')->id();
    $options['hostname'] = $this->getRequest()
      ->getClientIp();
    $options['timestamp'] = $this->time->getRequestTime();
    // Save vote.
    $this->pollVoteStorage->saveVote($options);
    $this->messenger()->addMessage($this->t('Your vote has been recorded.'));

    // In case of an ajax submission, trigger a form rebuild so that we can
    // return an updated form through the ajax callback.
    if ($this->getRequest()->query->get('ajax_form')) {
      $form_state->setRebuild(TRUE);
    }

    // No explicit redirect, so that we stay on the current page, which might
    // be the poll form or another page that is displaying this poll, for
    // example as a block.
  }

  /**
   * Checks if the current user is allowed to cancel on the given poll.
   *
   * @param \Drupal\poll\PollInterface $poll
   *   Poll entity.
   *
   * @return bool
   *   TRUE if the user can cancel.
   */
  protected function isCancelAllowed(PollInterface $poll) {
    // Allow access if the user has voted.
    return $poll->hasUserVoted()
      // And the poll allows to cancel votes.
      && $poll->getCancelVoteAllow()
      // And the user has the cancel own vote permission.
      && $this->currentUser()->hasPermission('cancel own vote')
      // And the user is authenticated.
      && ($this->currentUser()->isAuthenticated())
      // And poll is open.
      && $poll->isOpen();
  }

  /**
   * {@inheritdoc}
   */
  public function showResults(PollInterface $poll, FormStateInterface $form_state) {
    $account = $this->currentUser();
    switch (TRUE) {
      // The "View results" button, when available, has been clicked.
      case $form_state->get('show_results'):
        return TRUE;

      // The poll is closed.
      case ($poll->isClosed()):
        return TRUE;

      // Anonymous user is trying to view a poll in same ip.
      case ($account->isAnonymous() && $this->isSameHost($poll)):
        return TRUE;

      // The user has already voted.
      case ($account->isAuthenticated() && $poll->hasUserVoted()):
        return TRUE;

      default:
        return FALSE;
    }
  }

  /**
   * Check whether anonymous user is votting from same ip or not.
   */
  protected function isSameHost(PollInterface $poll): bool {
    $hostname = $this->getRequest()
      ->getClientIp();

    return $poll->hasUserVoted()
      && ($poll->hasUserVoted()['hostname'] == $hostname);

  }

}
