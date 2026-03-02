<?php
/*
Plugin Name: Efata SKU
Description: Gera SKU automático FF-ID para WooCommerce
Version: 1.0
Author: EfataBook
*/

if (!defined('ABSPATH')) exit;

add_action('save_post_product', 'efata_sku_auto', 20, 3);

function efata_sku_auto($post_id, $post, $update){

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;

    if ($post->post_type != 'product')
        return;

    $sku = get_post_meta($post_id, '_sku', true);

    if(!empty($sku))
        return;

    $supplier = get_post_meta($post_id, 'supplier_code', true);

    if(empty($supplier))
        return;

    $supplier = str_pad($supplier, 2, "0", STR_PAD_LEFT);

    $newsku = $supplier . '-' . $post_id;

    update_post_meta($post_id, '_sku', $newsku);

}