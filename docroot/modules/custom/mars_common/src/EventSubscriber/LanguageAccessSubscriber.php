<?php

namespace Drupal\mars_common\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StreamWrapper\PublicStream;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Redirect .html pages to corresponding Node page.
 */
class LanguageAccessSubscriber implements EventSubscriberInterface {

  /**
   * The current active user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs LanguageAccess Subscriber.
   *
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   */
  public function __construct(
    AccountInterface $current_user,
    LanguageManagerInterface $language_manager,
    RequestStack $request_stack,
    ConfigFactoryInterface $config_factory
  ) {
    $this->currentUser = $current_user;
    $this->languageManager = $language_manager;
    $this->requestStack = $request_stack;
    $this->configFactory = $config_factory;
  }

  /**
   * Redirect pattern based url.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   A GetResponseEvent instance.
   */
  public function customLanguageAccess(RequestEvent $event): void {
    $request = $this->requestStack->getCurrentRequest();
    $requestUrl = $request->server->get('REQUEST_URI', NULL);
    $language = $this->languageManager->getCurrentLanguage();

    // Allow user path and public file system to create image style derivative.
    if (strpos($requestUrl, '/user/') === FALSE && strpos($requestUrl, PublicStream::basePath()) === FALSE) {
      $disable_language = $this->configFactory->get('mars_common.disable_language');
      // Check access to language.
      if ($this->currentUser->isAnonymous() && $disable_language->get($language->getId()) == TRUE) {
        // Do not execute on drush.
        if (PHP_SAPI !== 'cli') {
          // Display the default access denied page.
          if ($event->getRequestType() === HttpKernelInterface::MASTER_REQUEST) {
            throw new AccessDeniedHttpException();
          }
        }
      }
    }
  }

  /**
   * Listen to kernel.request events and call customRedirection.
   *
   * @return array
   *   Event names to listen to (key) and methods to call (value).
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['customLanguageAccess'];
    return $events;
  }

}
