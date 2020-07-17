<?php

namespace Drupal\pagedesigner_favourite\EventSubscriber;

use Drupal\user\UserData;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\pagedesigner\ElementEvents;
use Drupal\pagedesigner\Event\ElementEvent;
use Drupal\pagedesigner_favourite\Plugin\rest\resource\FavouriteRessource;

/**
 * Event subscriber to update pattern for favourites.
 */
class FavouritesSubscriber implements EventSubscriberInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser = NULL;

  /**
   * The user data service.
   *
   * @var \Drupal\user\UserData
   */
  protected $_currentRoute = NULL;

  /**
   * Create the UpdateCategorySubscriber.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $user
   *   The current user.
   * @param \Drupal\user\UserData $user_data
   *   The user data service.
   */
  public function __construct(AccountProxyInterface $current_user, UserData $user_data) {
    $this->currentUser = $current_user;
    $this->userData = $user_data;
  }

  /**
   * Update the data for favourite patterns.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The event.
   */
  public function addAttachment(ElementEvent $event) {
    $attachments = &$event->getData()[0];
    $attachments['library'][] = 'pagedesigner_favourite/pagedesigner';
  }

  /**
   * Update the data for favourite patterns.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The event.
   */
  public function setFavourites(ElementEvent $event) {
    $favourites = json_decode($this->userData->get(FavouriteRessource::KEY, $this->currentUser->id(), FavouriteRessource::KEY), TRUE);
    $patterns = &$event->getData()[0];
    foreach ($patterns as $id => $pattern) {
      if (!empty($favourites[$id])) {
        $patterns[$id]['additional']['favourite'] = 1;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      ElementEvents::COLLECTATTACHMENTS_AFTER => [['addAttachment', 50]],
      ElementEvents::ADAPTPATTERNS_AFTER => [['setFavourites', 50]],
    ];
  }

}
