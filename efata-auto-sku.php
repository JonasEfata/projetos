<?php
/*
Plugin Name: Efatá Auto SKU
Description: Gera SKU automático no formato Fornecedor-ID para WooCommerce
Version: 1.0
Author: EfatáBook
*/

if (!defined('ABSPATH')) exit;

// Gera SKU automaticamente quando produto é salvo
add_action('save_post_product', 'efata_generate_sku', 20, 3);

function efata_generate_sku($post_id, $post, $update){

    // Evita autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;

    // Só produtos
    if ($post->post_type != 'product')
        return;

    // Já tem SKU?
    $sku = get_post_meta($post_id, '_sku', true);

    if(!empty($sku))
        return;

    // Pega código fornecedor
    $supplier = get_post_meta($post_id, 'supplier_code', true);

    if(empty($supplier))
        return;

    // Formata fornecedor 2 dígitos
    $supplier = str_pad($supplier, 2, "0", STR_PAD_LEFT);

    // ID do produto
    $id = $post_id;

    // SKU final
    $newsku = $supplier . '-' . $id;

    update_post_meta($post_id, '_sku', $newsku);

}