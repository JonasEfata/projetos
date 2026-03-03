<?php
/**
 * Plugin Name: Importador Oficial SBTB (SKU + Máscara Comercial)
 * Description: Sistema de importação robusto para 77+ itens. SKU [Prefixo+ID] e Proteção de Títulos.
 * Version: 2.1
 */

if ( ! defined( 'ABSPATH' ) ) exit;

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
        <div style="background: #fff; padding: 15px; border-left: 4px solid #0073aa; margin-bottom: 20px;">
            <p><strong>Meta:</strong> Sincronizar os 77 itens da planilha preservando seus nomes comerciais.</p>
        </div>
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="sbtb_file" required />
            <?php submit_button('Sincronizar Todos os Produtos'); ?>
        </form>
        <?php if (isset($_FILES['sbtb_file'])) sbtb_process_csv($_FILES['sbtb_file']['tmp_name']); ?>
    </div>
    <?php
}

function sbtb_process_csv($file) {
    if (!function_exists('wc_get_product_id_by_sku')) return;
    global $wpdb;
    
    ini_set('auto_detect_line_endings', true);
    $handle = fopen($file, "r");
    $contagem = 0;

    // Pula as primeiras linhas de lixo do Excel se necessário
    while (($data = fgetcsv($handle, 2000, ",")) !== FALSE) {
        
        $ean_bruto = preg_replace('/[^0-9]/', '', trim($data[0]));

        // Se a linha não tem um código válido ou é um título de coluna, pula para a próxima
        if (empty($ean_bruto) || strlen($ean_bruto) < 4 || strpos($data[0], 'Cód') !== false) {
            continue; 
        }

        $nome_planilha = isset($data[1]) ? trim($data[1]) : '';
        if (empty($nome_planilha)) continue;

        $estoque = isset($data[3]) ? (int)$data[3] : 0;
        $preco_raw = isset($data[4]) ? $data[4] : '0';
        $preco = str_replace(['R$', ' ', '.', ','], ['', '', '', '.'], $preco_raw);
        
        $prefixo = substr($ean_bruto, 0, 2);

        // BUSCA pelo SKU que começa com o prefixo
        $product_id = $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value LIKE %s",
            $prefixo . '%'
        ));

        if ($product_id && get_post_type($product_id) === 'product') {
            $product = wc_get_product($product_id);
            // Máscara: Se existe, não altera o nome.
        } else {
            $product = new WC_Product_Simple();
            $product->set_name($nome_planilha);
        }

        $product->set_regular_price($preco);
        $product->set_manage_stock(true);
        $product->set_stock_quantity($estoque);
        $product->set_status('publish');
        
        $id_final = $product->save();

        // Garante SKU: Prefixo + ID
        $product->set_sku($prefixo . $id_final);
        $product->save();

        $contagem++;
    }
    fclose($handle);
    echo "<div class='updated'><p><strong>Processamento Finalizado:</strong> $contagem produtos foram importados/atualizados!</p></div>";
}