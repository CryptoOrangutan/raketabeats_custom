<?php

namespace Drupal\raketabeats_custom\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\commerce_cart\Event\CartEntityAddEvent;
use Drupal\commerce_cart\Event\CartEvents;
use Drupal\commerce_order\Entity\Order;
use Drupal\user\Entity\User;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_cart\CartManagerInterface;
use Drupal\commerce_cart\CartProviderInterface;

/**
 * Class CartCompleteSubscriber.
 *
 * @package Drupal\raketabeats_custom
 */
class CartEventSubscriber implements EventSubscriberInterface {
  /**
   * The cart manager.
   *
   * @var \Drupal\commerce_cart\CartManagerInterface
   */
  protected $cartManager;
  
  /**
   * The cart provider.
   *
   * @var \Drupal\commerce_cart\CartProviderInterface
   */
  protected $cartProvider;
  
  /**
   * Drupal\Core\Entity\EntityTypeManager definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;
  
  /**
   * Constructs a new CartController object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The EntityTypeManager.
   * @param \Drupal\commerce_cart\CartManagerInterface $cart_manager
   *   The cart manager.
   * @param \Drupal\commerce_cart\CartProviderInterface $cart_provider
   *   The cart provider.
   */
  public function __construct(EntityTypeManager $entity_type_manager, CartManagerInterface $cart_manager, CartProviderInterface $cart_provider) {
    $this->entityTypeManager = $entity_type_manager;
    $this->cartManager = $cart_manager;
    $this->cartProvider = $cart_provider;
  }
  
  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [
      CartEvents::CART_ENTITY_ADD => 'checkedPurchased',
    ];
    return $events;
  }
  
  /**
   * Removes product from the shopping cart if it is already purchased.
   *
   * @param \Drupal\commerce_cart\Event\CartEntityAddEvent $event
   *   The add to cart event.
   */
  public function checkedPurchased(CartEntityAddEvent $event) {
    $purchased_entity = $event->getEntity();
    $items = $purchased_entity;
    $store_id = 1;
    $order_type = $purchased_entity->getOrderItemTypeId();
    $cart_manager = \Drupal::service('commerce_cart.cart_manager');
    $cart_provider = \Drupal::service('commerce_cart.cart_provider');
    $entity_manager = \Drupal::entityManager();
    $store = $entity_manager->getStorage('commerce_store')->load($store_id);
    $cart = $cart_provider->getCart($order_type, $store);
    $total_price = $cart->getTotalPrice();
    $total_price = $total_price->getNumber();
  
    // Get all roles of current user.
    $userCurrent = \Drupal::currentUser();
    $user = User::load($userCurrent->id());
    $user_roles = $user->getRoles();
  
    // Get product roles field.
    $config = \Drupal::config('raketabeats_custom.settings');
    $role_field = $config->get('field_product_roles');
    
    // Get products roles.
    $product_id = $purchased_entity->id();
    $current_product = Product::load($product_id);
    $product_roles = $current_product->get($role_field)->getValue()[0]['target_id'];
    
    // Check purchased product.
    $order = Order::load($cart->id());
    foreach ($order->getItems() as $order_item) {
      if (in_array($product_roles, $user_roles)) {
        $cart_manager->removeOrderItem($cart, $order_item);
        drupal_set_message(t('@product has already been purchased by you.', [
          '@product' => $order_item->getTitle(),
        ]), 'error');
      }
    }
  }
  
}
