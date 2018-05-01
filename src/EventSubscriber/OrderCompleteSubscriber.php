<?php

namespace Drupal\raketabeats_custom\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Drupal\Core\Entity\EntityTypeManager;

/**
 * Class OrderCompleteSubscriber.
 *
 * @package Drupal\raketabeats_custom
 */
class OrderCompleteSubscriber implements EventSubscriberInterface {
  
  /**
   * Drupal\Core\Entity\EntityTypeManager definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;
  
  /**
   * Constructor.
   */
  public function __construct(EntityTypeManager $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }
  
  /**
   * {@inheritdoc}
   */
  static function getSubscribedEvents() {
    $events['commerce_order.place.post_transition'] = ['orderCompleteHandler'];
    
    return $events;
  }
  
  /**
   * This method is called whenever the commerce_order.place.post_transition event is
   * dispatched.
   *
   * @param WorkflowTransitionEvent $event
   */
  public function orderCompleteHandler(WorkflowTransitionEvent $event) {
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = $event->getEntity();
    // Order items in the cart.
    $items = $order->getItems();
    $config = \Drupal::config('raketabeats_custom.settings');
    
    foreach ($order->getItems() as $order_item) {
      $product_variation = $order_item->getPurchasedEntity();
      $product_id = $product_variation->id();
      $current_product = \Drupal\commerce_product\Entity\Product::load($product_id);
      $role_field = $config->get('field_product_roles');
      $product_roles = $current_product->get($role_field)->getValue();
    }
  
    $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
    if (!empty($product_roles)) {
      foreach ($product_roles as $key => $value) {
        $user->addRole($value);
      }
    }
    $user->save();

  }
}