<?php
/*
Plugin Name: Custom Woo Modifications
Description: Custom modifications for WooCommerce.
Version: 1.2
Author: Anil Kunwar
*/

// Shop Page: Remove buttons and add out-of-stock button for out-of-stock products
add_action('woocommerce_after_shop_loop_item', 'themelocation_change_outofstock_to_contact_us', 1);
function themelocation_change_outofstock_to_contact_us() {
  global $product;
  if (!$product->is_in_stock()) {
    // Remove "Add to Cart" button
    remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
    // Remove "Buy Now" button (update with the correct hook from the "Quick Buy Now Button" plugin)
    remove_action('woocommerce_after_shop_loop_item', 'quick_buy_now_button_action_hook', 10);
    // Display out-of-stock message with contact link
    echo '<a href="/contact" class="button out-of-stock-button" style="background-color: red; color: white;">Out of Stock</a>';
  }
}

// Single Product Page: Modify the availability message and remove buttons
add_filter('woocommerce_get_availability', 'wcs_custom_get_availability', 1, 2);
function wcs_custom_get_availability($availability, $_product) {
  if (!$_product->is_in_stock()) {
    // Change availability message
    $availability['availability'] = __('Out of Stock.', 'woocommerce');
  }
  return $availability;
}

// Handle variable products to check variations stock status
add_action('woocommerce_single_variation', 'handle_variation_stock_status', 1);
function handle_variation_stock_status() {
  global $product;

  if ($product->is_type('variable')) {
    echo '<div id="variation-out-of-stock-message" style="display:none;"><a href="/contact" class="button out-of-stock-button" style="background-color: red; color: white;">Out of Stock</a></div>';
    echo '<div id="variation-not-selected-message" style="display:none;"><p style="color: red; font-size:1.3rem; font-weight: bold;">Please choose a variant to Checkout</p></div>';
  }
}

add_action('wp_footer', 'variation_stock_status_script');
function variation_stock_status_script() {
  if (is_product()) {
    ?>
    <script type="text/javascript">
      jQuery(document).ready(function($) {
        var $form = $('form.variations_form');
        var $addToCartButton = $('.single_add_to_cart_button');
        var $quickBuyButton = $('.quick_buy_now_button');
        var $outOfStockMessage = $('#variation-out-of-stock-message');
        var $notSelectedMessage = $('#variation-not-selected-message');

        function updateButtonsVisibility(variation) {
          if (variation && variation.is_in_stock) {
            $outOfStockMessage.hide();
            $notSelectedMessage.hide();
            $addToCartButton.show();
            $quickBuyButton.show();
          } else if (variation && !variation.is_in_stock) {
            $outOfStockMessage.show();
            $notSelectedMessage.hide();
            $addToCartButton.hide();
            $quickBuyButton.hide();
          } else {
            $outOfStockMessage.hide();
            $notSelectedMessage.show();
            $addToCartButton.hide();
            $quickBuyButton.hide();
          }
        }

        $form.on('show_variation', function(event, variation) {
          updateButtonsVisibility(variation);
        });

        $form.on('hide_variation', function() {
          updateButtonsVisibility(null);
        });

        // Initial check on page load
        updateButtonsVisibility(null);
      });
    </script>
    <?php
  }
}

// Hook to ensure the quick buy now button is removed for out-of-stock products on shop page
add_action('init', 'remove_quick_buy_now_button_for_outofstock');
function remove_quick_buy_now_button_for_outofstock() {
  remove_action('woocommerce_after_shop_loop_item', 'quick_buy_now_button_action_hook', 10);
}

// Hook to ensure the quick buy now button is removed for out-of-stock products on single product page
add_action('init', 'remove_single_product_quick_buy_now_button_for_outofstock');
function remove_single_product_quick_buy_now_button_for_outofstock() {
  add_action('woocommerce_single_product_summary', 'conditional_out_of_stock_button', 31);
}

function conditional_out_of_stock_button() {
  global $product;

  if (!$product->is_in_stock()) {
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
    echo '<a href="/contact" class="button out-of-stock-button" style="background-color: red; color: white;">Out of Stock</a>';
  }
}