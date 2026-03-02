<?php
/**
 * Plugin Name: Importador Oficial SBTB
 * Description: Lê o arquivo de Posição de Estoque da SBTB e atualiza o WooCommerce.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action('admin_menu', function() {
    add_menu_page('Importar SBTB', 'Importar SBTB', 'manage_options', 'sbtb-import', 'sbtb_render_page', 'dashicons-book-alt');
});

function sbtb_render_page() {
    ?>
    <div class="wrap">
        <h1>Importar Planilha de Estoque SBTB</h1>
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="sbtb_file" required />
            <?php submit_button('Sincronizar Bíblias'); ?>
        </form>
        <?php if (isset($_FILES['sbtb_file'])) sbtb_process_csv($_FILES['sbtb_file']['tmp_name']); ?>
    </div>
    <?php
}

function sbtb_process_csv($file) {
    $handle = fopen($file, "r");
    $contagem = 0;

    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $ean = trim($data[0]);
        
        // Verifica se a linha começa com um código de barras (número longo ou código interno)
        if (!is_numeric($ean) || strlen($ean) < 5) continue;

        $nome  = $data[1];
        $estoque = (int)$data[3];
        // Limpa o "R$ " e troca virgula por ponto
        $preco = str_replace(['R$', ' ', '.', ','], ['', '', '', '.'], $data[4]);

        $product_id = wc_get_product_id_by_sku($ean);
        $product = $product_id ? wc_get_product($product_id) : new WC_Product_Simple();

        $product->set_sku($ean);
        $product->set_name($nome);
        $product->set_regular_price($preco);
        $product->set_manage_stock(true);
        $product->set_stock_quantity($estoque);
        $product->set_status('publish');
        
        $product->save();
        $contagem++;
    }
    fclose($handle);
    echo "<div class='updated'><p>Sincronização concluída! $contagem produtos processados.</p></div>";
}
