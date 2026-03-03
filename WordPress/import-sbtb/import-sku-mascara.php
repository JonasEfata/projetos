<?php
/**
 * Plugin Name: Importador Oficial SBTB (SKU + Máscara Comercial)
 * Description: Unifica a geração de SKU (2 dígitos + ID) e protege os nomes comerciais.
 * Version: 1.5
 * Author: Moacir Filho
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Garante que as funções de ficheiros do WP estão carregadas
if ( ! function_exists( 'post_exists' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/post.php' );
}

add_action('admin_menu', function() {
    add_menu_page('Importar SBTB', 'Importar SBTB', 'manage_options', 'sbtb-import', 'sbtb_render_page', 'dashicons-book-alt');
});

function sbtb_render_page() {
    ?>
    <div class="wrap">
        <h1>Sincronização Inteligente SBTB</h1>
        <p>Ajusta Preços e Estoques. O SKU vira [2 dígitos EAN + ID]. Seu Nome Comercial é preservado.</p>
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="sbtb_file" required />
            <?php submit_button('Sincronizar Agora'); ?>
        </form>
        <?php 
        if (isset($_FILES['sbtb_file'])) {
            sbtb_process_csv($_FILES['sbtb_file']['tmp_name']);
        }
        ?>
    </div>
    <?php
}

function sbtb_process_csv($file) {
    if (!function_exists('wc_get_product_id_by_sku')) return;
    global $wpdb;
    
    // Melhora a leitura do CSV vindo de Windows/Excel
    ini_set('auto_detect_line_endings', true);
    $handle = fopen($file, "r");
    $contagem = 0;

    while (($data = fgetcsv($handle, 2000, ",")) !== FALSE) {
        
        // Se a linha estiver vazia ou não tiver o código na coluna 0, pula
        if (!isset($data[0])) continue;
        
        $ean_bruto = preg_replace('/[^0-9]/', '', trim($data[0]));

        // Filtro: Só processa se o código tiver entre 4 e 14 dígitos (EAN ou códigos internos)
        if (empty($ean_bruto) || strlen($ean_bruto) < 4) {
            continue; 
        }

        $nome_planilha = isset($data[1]) ? trim($data[1]) : '';
        if (empty($nome_planilha)) continue;

        $estoque = isset($data[3]) ? (int)$data[3] : 0;
        $preco_bruto = isset($data[4]) ? $data[4] : '0';
        $preco = str_replace(['R$', ' ', '.', ','], ['', '', '', '.'], $preco_bruto);
        
        $prefixo = substr($ean_bruto, 0, 2);

        // BUSCA: Primeiro tenta achar um SKU que comece com o prefixo
        $product_id = $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value LIKE %s",
            $prefixo . '%'
        ));

        // Se não achou, tenta pelo nome exato (caso o produto tenha sido criado agora)
        if (!$product_id) {
            $product_id = post_exists($nome_planilha);
        }

        if ($product_id && get_post_type($product_id) === 'product') {
            $product = wc_get_product($product_id);
            // MÁSCARA: Se existe, NÃO mexe no nome.
        } else {
            $product = new WC_Product_Simple();
            $product->set_name($nome_planilha);
        }

        $product->set_regular_price($preco);
        $product->set_manage_stock(true);
        $product->set_stock_quantity($estoque);
        $product->set_status('publish');
        
        $id_final = $product->save();

        // GERA SKU: 2 dígitos + ID (ex: 781877)
        $novo_sku = $prefixo . $id_final;
        $product->set_sku($novo_sku);
        $product->save();

        $contagem++;
    }
    fclose($handle);
    echo "<div class='updated'><p><b>Sucesso!</b> $contagem produtos sincronizados de toda a planilha.</p></div>";
}