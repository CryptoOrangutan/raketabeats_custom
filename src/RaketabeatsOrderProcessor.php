<?php

namespace Drupal\raketabeats_custom;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\OrderProcessorInterface;
use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_order\Adjustment;

/**
 * Provides an order processor that modifies the cart according to the business
 * logic.
 */
class RaketabeatsOrderProcessor implements OrderProcessorInterface {
  
  /**
   * {@inheritdoc}
   */
  public function process(OrderInterface $order) {
    foreach ($order->getItems() as $order_item) {
      $order_item->setAdjustments([]);
      $product_variation = $order_item->getPurchasedEntity();
      
      if (!empty($product_variation)) {
        $product_id = $product_variation->get('product_id')->getValue()[0]['target_id'];
        $product = Product::load($product_id);
        $product_type = $product->get('type')->getValue()[0]['target_id'];
        $quantity = $order_item->getQuantity();
        $total_price = $order_item->getTotalPrice();
        
        if ($product_type == 'set') {
          $product_price = $order_item->getUnitPrice();
          $product_unit_price = $product_price->getNumber();
          if ($quantity > 1) {
            $new_adjustment = 0;
            $order_item->setQuantity('1');
          }
          else {
            continue;
          }
          $adjustments = $order_item->getAdjustments();
          $adjustments[] = new Adjustment([
            'type' => 'raketabeats_adjustment',
            'label' => t('Discounted Price') . ' ',
            'amount' => new Price('-' . $new_adjustment, 'RUB'),
          ]);
          $order_item->setAdjustments($adjustments);
          $order_item->save();
        }
      }
    }
  }
}
