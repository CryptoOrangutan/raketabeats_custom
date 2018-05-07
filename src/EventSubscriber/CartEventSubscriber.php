<?php

namespace Drupal\raketabeats_custom\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Drupal\commerce_cart\CartProviderInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\commerce_cart\Event\CartEntityAddEvent;
use Drupal\commerce_cart\Event\CartEvents;
use Drupal\commerce_order\Entity\Order;

/**
 * Class CartCompleteSubscriber.
 *
 * @package Drupal\raketabeats_custom
 */
class CartEventSubscriber implements EventSubscriberInterface {
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
  public static function getSubscribedEvents() {
    $events = [
      CartEvents::CART_ENTITY_ADD => 'changeTotalSumInCart',
    ];
    return $events;
  }
  
  /**
   * Test.
   *
   * @param \Drupal\commerce_cart\Event\CartEntityAddEvent $event
   *   The add to cart event.
   */
  public function changeTotalSumInCart(CartEntityAddEvent $event) {
    $purchased_entity = $event->getEntity();
    $items = $purchased_entity;
    $cart_price = $items->getPrice();
    $store_id = $purchased_entity->getStores();
    $store_id = 1;
    $order_type = $purchased_entity->getOrderItemTypeId();
    
    $cart_manager = \Drupal::service('commerce_cart.cart_manager');
    $cart_provider = \Drupal::service('commerce_cart.cart_provider');
    $entity_manager = \Drupal::entityManager();
    $store = $entity_manager->getStorage('commerce_store')->load($store_id);
    $cart = $cart_provider->getCart($order_type, $store);
    $total_price = $cart->getTotalPrice();
    $total_price = $total_price->getNumber();

    //$order = Order::load($cart->id());
  
    $total_items = count($cart->getItems());
    
  }
}