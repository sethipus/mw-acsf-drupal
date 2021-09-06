<?php

namespace Drupal\mars_common\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\PagerSelectExtender;
use Drupal\Core\Database\Query\TableSortExtender;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\user\UserStorage;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class PollController is responsible for Poll endpoints logic.
 *
 * @package Drupal\mars_common\Controller
 */
class PollController extends ControllerBase {

  const SUBMISSIONS_PER_PAGE = 100;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Retrieves the currently active route match object.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  private $routeMatch;

  /**
   * The user storage.
   *
   * @var \Drupal\user\UserStorage
   */
  private $userStorage;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Constructs a new PollController.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   A Database connection to use for reading and writing database data.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Route match interface.
   * @param \Drupal\user\UserStorage $user_storage
   *   User storage.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language manager.
   */
  public function __construct(
    Connection $connection,
    RouteMatchInterface $route_match,
    UserStorage $user_storage,
    LanguageManagerInterface $language_manager
  ) {
    $this->connection = $connection;
    $this->routeMatch = $route_match;
    $this->userStorage = $user_storage;
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('current_route_match'),
      $container->get('entity_type.manager')->getStorage('user'),
      $container->get('language_manager')
    );
  }

  /**
   * Builds the page for this controller.
   *
   * @return array
   *   render array.
   */
  public function buildResultsView() {
    $poll = $this->routeMatch->getParameter('poll');
    $poll_id = $poll->id();

    $summary_header = [
      $this->t('Choice'),
      $this->t('Count'),
    ];
    $summary_rows = [];

    $submissions_header = [
      'date' => [
        'data' => $this->t('Date'),
        'field' => 'timestamp',
        'sort' => 'desc',
      ],
      'hostname' => [
        'data' => $this->t('Hostname'),
        'field' => 'hostname',
      ],
      'username' => [
        'data' => $this->t('Username'),
        'field' => 'username',
      ],
      'choice' => [
        'data' => $this->t('Choice'),
        'field' => 'choice',
      ],
    ];
    $submissions_rows = [];

    $choice_options = $poll->getOptions();
    $query = $this->connection->select('poll_vote', 'c');
    $query->condition('pid', $poll_id, '=');
    $results_total = $query->countQuery()->execute()->fetchField();

    foreach ($choice_options as $option_key => $option_name) {
      $choice_count_query = $this->connection->select('poll_vote', 'c');
      $choice_count_query->condition('pid', $poll_id, '=');
      $option_count_query = $choice_count_query->condition('chid', $option_key, '=')->countQuery()->execute()->fetchField();

      $summary_rows[] = [
        'data' => [
          'data' => $option_name,
          'value' => $option_count_query,
        ],
      ];
    }

    $query->fields('c', ['hostname', 'uid', 'chid', 'timestamp']);
    $table_sort = $query
      ->extend(TableSortExtender::class)
      ->orderByHeader($submissions_header);
    $pager = $table_sort
      ->extend(PagerSelectExtender::class)
      ->limit(self::SUBMISSIONS_PER_PAGE);
    $query_result = $pager->execute();

    foreach ($query_result as $row) {
      $username = $this->t('Anonymous');
      if ($row->uid) {
        $user = $this->userStorage->load($row->uid);
        if ($user) {
          $username = $user->getAccountName();
        }
        else {
          $username = $this->t('Deleted user');
        }

      }

      $submissions_rows[] = [
        'data' => [
          'date' => DrupalDateTime::createFromTimestamp($row->timestamp, date_default_timezone_get(), [
            'langcode' => $this->languageManager->getCurrentLanguage()->getId(),
          ]),
          'hostname' => $row->hostname,
          'username' => $username,
          'choice' => $choice_options[$row->chid] ?? '',
        ],
      ];
    }

    $build['summary'] = [
      '#type' => 'details',
      '#title' => $this->t('Summary'),
      '#open' => TRUE,
    ];
    $build['summary']['header'] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#value' => $this->t('<b>Submission count:</b> @count', ['@count' => $results_total]),
    ];
    $build['summary']['table'] = [
      '#type' => 'table',
      '#header' => $summary_header,
      '#rows' => $summary_rows,
    ];

    $build['submissions'] = [
      '#type' => 'details',
      '#title' => $this->t('Submissions'),
      '#open' => TRUE,
    ];
    $build['submissions']['table'] = [
      '#theme' => 'table',
      '#type' => 'table',
      '#header' => $submissions_header,
      '#rows' => $submissions_rows,
      '#empty' => $this->t('No results has been found.'),
    ];
    $build['submissions']['pager'] = [
      '#theme' => 'pager',
      '#type' => 'pager',
    ];

    return $build;
  }

  /**
   * Provides the page title for this controller.
   *
   * @param \Drupal\Core\Entity\EntityInterface $poll
   *   The poll entity.
   *
   * @return string
   *   The page title.
   */
  public function getResultsViewTitle(EntityInterface $poll) {
    return $this->t('Results of %poll_title', ['%poll_title' => $poll->get('question')->value]);
  }

}
