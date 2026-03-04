

<?php
/**
 * Plugin Name: Efatabook Importer
 * Plugin URI:  https://www.efatabook.com.br
 * Description: Importação inteligente de produtos, SKU personalizado e Máscara Comercial para WooCommerce.
 * Version:     2.0.0
 * Author:      Efatabook
 * Text Domain: efatabook-importer
 * Requires Plugins: woocommerce
 */

defined( 'ABSPATH' ) || exit;

// ══════════════════════════════════════════════════════════════════
// PILAR B — SKU PERSONALIZADO
// Fórmula: [2 primeiros dígitos do EAN] + [ID do WordPress]
// Exemplo: EAN 9788573804157, ID 1877 → SKU 971877
// ══════════════════════════════════════════════════════════════════

/**
 * Gera o SKU a partir do EAN salvo no produto e do ID do WordPress.
 *
 * @param int $product_id ID do produto no WordPress.
 * @return string SKU gerado ou vazio se EAN não estiver definido.
 */
function efatabook_gerar_sku( int $product_id ): string {
    $ean = get_post_meta( $product_id, '_efatabook_ean', true );
    if ( empty( $ean ) ) {
        return '';
    }
    $prefixo = substr( preg_replace( '/\D/', '', $ean ), 0, 2 );
    return $prefixo . $product_id;
}

/**
 * Aplica o SKU ao produto e salva.
 *
 * @param int $product_id
 */
function efatabook_aplicar_sku( int $product_id ): void {
    $sku = efatabook_gerar_sku( $product_id );
    if ( $sku === '' ) {
        return;
    }
    $product = wc_get_product( $product_id );
    if ( ! $product ) {
        return;
    }
    // Só atualiza se o SKU for diferente do atual (evita saves desnecessários)
    if ( $product->get_sku() !== $sku ) {
        $product->set_sku( $sku );
        $product->save();
    }
}

// ══════════════════════════════════════════════════════════════════
// PILAR C — MÁSCARA COMERCIAL
// Campos visíveis na tela de edição do produto no WooCommerce.
// ══════════════════════════════════════════════════════════════════

/**
 * Exibe os campos da Máscara Comercial na aba "Geral" do produto.
 */
add_action( 'woocommerce_product_options_general_product_data', function (): void {
    echo '<div class="options_group efatabook-fields">';
    echo '<p style="padding:10px 12px;margin:0;background:#f0f6fc;border-left:4px solid #2271b1;font-weight:600;">
            🏷️ Efatabook — Dados do Fornecedor
          </p>';

    woocommerce_wp_text_input( [
        'id'          => '_efatabook_ean',
        'label'       => 'EAN (Cód. de Barras)',
        'description' => 'Código de barras do produto. Usado como identificador único na importação.',
        'desc_tip'    => true,
    ] );

    woocommerce_wp_text_input( [
        'id'          => '_efatabook_nome_fornecedor',
        'label'       => 'Nome Original (Fornecedor)',
        'description' => 'Nome técnico que veio da planilha do fornecedor. Nunca é alterado pela importação.',
        'desc_tip'    => true,
    ] );

    woocommerce_wp_text_input( [
        'id'          => '_efatabook_cod_interno',
        'label'       => 'Código Interno (Fornecedor)',
        'description' => 'Código interno do fornecedor (ex: 6801, 3110). Referência para pedidos.',
        'desc_tip'    => true,
    ] );

    woocommerce_wp_text_input( [
        'id'          => '_efatabook_fornecedor',
        'label'       => 'Fornecedor',
        'description' => 'Nome ou sigla do fornecedor (ex: ACF, Efatabook).',
        'desc_tip'    => true,
    ] );

    echo '</div>';
} );

/**
 * Salva os campos da Máscara Comercial ao salvar o produto.
 * Após salvar o EAN, regenera o SKU automaticamente.
 */
add_action( 'woocommerce_process_product_meta', function ( int $product_id ): void {
    $campos = [
        '_efatabook_ean',
        '_efatabook_nome_fornecedor',
        '_efatabook_cod_interno',
        '_efatabook_fornecedor',
    ];
    foreach ( $campos as $campo ) {
        if ( isset( $_POST[ $campo ] ) ) {
            update_post_meta( $product_id, $campo, sanitize_text_field( $_POST[ $campo ] ) );
        }
    }
    // Regenera SKU sempre que o produto for salvo manualmente
    efatabook_aplicar_sku( $product_id );
} );

// ══════════════════════════════════════════════════════════════════
// PILAR A — IMPORTAÇÃO INTELIGENTE
// ══════════════════════════════════════════════════════════════════

/**
 * Registra o menu de importação no painel WooCommerce.
 */
add_action( 'admin_menu', function (): void {
    add_submenu_page(
        'woocommerce',
        'Importar Planilha ACF',
        '📥 Importar Planilha ACF',
        'manage_woocommerce',
        'efatabook-importar',
        'efatabook_pagina_importar'
    );
    add_submenu_page(
        'woocommerce',
        'SKUs Efatabook',
        '🏷️ SKUs Efatabook',
        'manage_woocommerce',
        'efatabook-skus',
        'efatabook_pagina_skus'
    );
} );

/**
 * Parseia o CSV da SBTB/ACF e retorna array de produtos limpos.
 * Ignora linhas de cabeçalho, rodapé, totais e linhas sem EAN numérico.
 *
 * @param string $filepath Caminho absoluto para o arquivo CSV.
 * @return array Lista de produtos com keys: ean, nome, categoria, estoque, preco, cod_interno.
 */
function efatabook_parsear_csv( string $filepath ): array {
    $handle = fopen( $filepath, 'r' );
    if ( ! $handle ) {
        return [];
    }

    // Detectar e pular BOM UTF-8
    $bom = fread( $handle, 3 );
    if ( $bom !== "\xEF\xBB\xBF" ) {
        rewind( $handle );
    }

    $produtos          = [];
    $categoria_atual   = '';

    while ( ( $cols = fgetcsv( $handle ) ) !== false ) {
        // Limpar espaços e tabs de cada coluna
        $cols = array_map( fn( $c ) => trim( $c, " \t\r\n" ), $cols );

        if ( count( $cols ) < 2 ) {
            continue;
        }

        $col0 = $cols[0];
        $col1 = $cols[1] ?? '';

        // Linha de cabeçalho de categoria: primeira coluna = "Cód. de Barras (EAN)"
        if ( stripos( $col0, 'Cód. de Barras' ) !== false ) {
            // O nome da categoria está na segunda coluna
            $categoria_atual = trim( preg_replace( '/\s*-\s*[\d,\.\s]+cm.*$/i', '', $col1 ) );
            continue;
        }

        // Ignorar linhas cujo EAN não seja numérico (rodapés, totais, títulos)
        $ean_limpo = preg_replace( '/\D/', '', $col0 );
        if ( empty( $ean_limpo ) ) {
            continue;
        }

        $nome      = trim( $col1 );
        $disponivel = trim( $cols[3] ?? '0' );
        $preco_raw  = trim( $cols[4] ?? '0' );

        // Limpar estoque: "1,859" → 1859 | "60\nMilheiros" → 60
        $estoque_str = preg_replace( '/[^\d]/', '', explode( "\n", $disponivel )[0] );
        $estoque     = intval( $estoque_str );

        // Limpar preço: "R$ 2,490.00" → 2490.00 | "R$ 11.90" → 11.90
        $preco_str = str_replace( 'R$', '', $preco_raw );
        $preco_str = trim( $preco_str );
        if ( strpos( $preco_str, ',' ) !== false && strpos( $preco_str, '.' ) !== false ) {
            // Formato "2,490.00": vírgula=milhar, ponto=decimal
            $preco_str = str_replace( ',', '', $preco_str );
        } elseif ( strpos( $preco_str, ',' ) !== false ) {
            // Formato "11,90": vírgula=decimal
            $preco_str = str_replace( ',', '.', $preco_str );
        }
        $preco = floatval( $preco_str );

        // Extrair código interno do fornecedor (ex: "cód. 6801")
        preg_match( '/cód\.\s*(\w+)/i', $nome, $cod_match );
        $cod_interno = $cod_match[1] ?? '';

        // Nome limpo para exibição inicial: remove "- cód. XXXX" e "- Desc. Max. XX%"
        $nome_limpo = preg_replace( '/\s*-\s*cód\.\s*\w+.*/i', '', $nome );
        $nome_limpo = preg_replace( '/\s*-\s*Desc\. Max\. \d+%/i', '', $nome_limpo );
        $nome_limpo = trim( $nome_limpo );

        $produtos[] = [
            'ean'          => $ean_limpo,
            'nome'         => $nome_limpo,
            'nome_original' => $nome,
            'categoria'    => $categoria_atual,
            'estoque'      => $estoque,
            'preco'        => $preco,
            'cod_interno'  => $cod_interno,
        ];
    }

    fclose( $handle );
    return $produtos;
}

/**
 * Busca um produto WooCommerce pelo EAN (meta _efatabook_ean).
 *
 * @param string $ean
 * @return int|null ID do produto ou null se não encontrado.
 */
function efatabook_buscar_por_ean( string $ean ): ?int {
    global $wpdb;
    $product_id = $wpdb->get_var( $wpdb->prepare(
        "SELECT post_id FROM {$wpdb->postmeta}
         WHERE meta_key = '_efatabook_ean' AND meta_value = %s
         LIMIT 1",
        $ean
    ) );
    return $product_id ? intval( $product_id ) : null;
}

/**
 * Cria ou atualiza um produto com base nos dados da planilha.
 * Aplica a Máscara Comercial: nunca sobrescreve o nome após a primeira importação.
 *
 * @param array $dados Dados do produto parseados do CSV.
 * @return array{action: string, product_id: int} Resultado da operação.
 */
function efatabook_processar_produto( array $dados ): array {
    $product_id = efatabook_buscar_por_ean( $dados['ean'] );

    if ( $product_id ) {
        // ── PRODUTO EXISTENTE: atualiza apenas preço e estoque ──
        $product = wc_get_product( $product_id );
        if ( ! $product ) {
            return [ 'action' => 'erro', 'product_id' => $product_id ];
        }
        $product->set_regular_price( $dados['preco'] );
        $product->set_stock_quantity( $dados['estoque'] );
        $product->set_manage_stock( true );
        $product->set_stock_status( $dados['estoque'] > 0 ? 'instock' : 'outofstock' );
        $product->save();

        return [ 'action' => 'atualizado', 'product_id' => $product_id ];

    } else {
        // ── PRODUTO NOVO: cria com todos os dados ──
        $product = new WC_Product_Simple();
        $product->set_name( $dados['nome'] );
        $product->set_status( 'publish' );
        $product->set_regular_price( $dados['preco'] );
        $product->set_manage_stock( true );
        $product->set_stock_quantity( $dados['estoque'] );
        $product->set_stock_status( $dados['estoque'] > 0 ? 'instock' : 'outofstock' );

        // Categoria WooCommerce: cria se não existir
        if ( ! empty( $dados['categoria'] ) ) {
            $term = term_exists( $dados['categoria'], 'product_cat' );
            if ( ! $term ) {
                $term = wp_insert_term( $dados['categoria'], 'product_cat' );
            }
            if ( ! is_wp_error( $term ) ) {
                $product->set_category_ids( [ $term['term_id'] ] );
            }
        }

        $product_id = $product->save();

        // Salvar metadados do fornecedor (Pilar C)
        update_post_meta( $product_id, '_efatabook_ean',             $dados['ean'] );
        update_post_meta( $product_id, '_efatabook_nome_fornecedor', $dados['nome_original'] );
        update_post_meta( $product_id, '_efatabook_cod_interno',     $dados['cod_interno'] );
        update_post_meta( $product_id, '_efatabook_fornecedor',      'ACF' );

        // Gerar SKU imediatamente após salvar (Pilar B)
        efatabook_aplicar_sku( $product_id );

        return [ 'action' => 'criado', 'product_id' => $product_id ];
    }
}

// ══════════════════════════════════════════════════════════════════
// PÁGINA DE IMPORTAÇÃO — INTERFACE ADMIN
// ══════════════════════════════════════════════════════════════════

function efatabook_pagina_importar(): void {
    $criados     = 0;
    $atualizados = 0;
    $erros       = 0;
    $log         = [];
    $processado  = false;

    // ── Processar upload ──
    if (
        isset( $_POST['efatabook_import_nonce'] ) &&
        wp_verify_nonce( $_POST['efatabook_import_nonce'], 'efatabook_importar' ) &&
        ! empty( $_FILES['efatabook_csv']['tmp_name'] )
    ) {
        $processado = true;
        $filepath   = $_FILES['efatabook_csv']['tmp_name'];
        $produtos   = efatabook_parsear_csv( $filepath );

        foreach ( $produtos as $dados ) {
            $resultado = efatabook_processar_produto( $dados );
            if ( $resultado['action'] === 'criado' ) {
                $criados++;
            } elseif ( $resultado['action'] === 'atualizado' ) {
                $atualizados++;
            } else {
                $erros++;
            }
            $log[] = $resultado + [ 'nome' => $dados['nome'], 'ean' => $dados['ean'] ];
        }
    }

    // ── Render da página ──
    ?>
    <div class="wrap">
        <h1>📥 Importar Planilha ACF — Efatabook</h1>

        <?php if ( $processado ) : ?>
        <div class="notice notice-success" style="padding:12px 16px">
            <strong>✅ Importação concluída!</strong>
            &nbsp;&nbsp;
            🟢 <strong><?= $criados ?></strong> criados
            &nbsp;|&nbsp;
            🔵 <strong><?= $atualizados ?></strong> atualizados
            &nbsp;|&nbsp;
            🔴 <strong><?= $erros ?></strong> erros
        </div>

        <h2>Relatório detalhado</h2>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th>Ação</th>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>EAN</th>
                    <th>SKU Gerado</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ( $log as $item ) :
                $icon = match( $item['action'] ) {
                    'criado'     => '🟢',
                    'atualizado' => '🔵',
                    default      => '🔴',
                };
                $sku = efatabook_gerar_sku( $item['product_id'] );
                $link = get_edit_post_link( $item['product_id'] );
            ?>
                <tr>
                    <td><?= $icon ?> <?= ucfirst( $item['action'] ) ?></td>
                    <td><a href="<?= esc_url( $link ) ?>" target="_blank"><?= $item['product_id'] ?></a></td>
                    <td><?= esc_html( $item['nome'] ) ?></td>
                    <td><code><?= esc_html( $item['ean'] ) ?></code></td>
                    <td><code><?= esc_html( $sku ?: '—' ) ?></code></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <br>
        <?php endif; ?>

        <div style="background:#fff;border:1px solid #c3c4c7;border-radius:4px;padding:20px;max-width:600px">
            <h2 style="margin-top:0">📂 Enviar planilha CSV</h2>
            <p>Envie o arquivo CSV exportado diretamente do sistema da SBTB/ACF, sem precisar limpar ou editar nada.</p>
            <ul style="color:#555">
                <li>✅ Linhas sem EAN numérico são ignoradas automaticamente</li>
                <li>✅ Produtos existentes têm <strong>preço e estoque atualizados</strong></li>
                <li>✅ O <strong>nome comercial</strong> (máscara) nunca é sobrescrito</li>
                <li>✅ Produtos novos recebem <strong>SKU gerado automaticamente</strong></li>
            </ul>
            <form method="post" enctype="multipart/form-data">
                <?php wp_nonce_field( 'efatabook_importar', 'efatabook_import_nonce' ); ?>
                <input
                    type="file"
                    name="efatabook_csv"
                    accept=".csv"
                    required
                    style="display:block;margin-bottom:12px"
                >
                <button type="submit" class="button button-primary button-large">
                    ⚙️ Importar Agora
                </button>
            </form>
        </div>

        <div style="margin-top:24px;padding:12px 16px;background:#fff8e1;border-left:4px solid #f0a500;max-width:600px">
            <strong>💡 Fluxo recomendado:</strong><br>
            1. Importe a planilha → produtos são criados com o nome do fornecedor<br>
            2. Edite cada produto no WooCommerce e coloque o <strong>Nome Comercial</strong> (título do produto)<br>
            3. Nas próximas importações, apenas preço e estoque serão atualizados — o nome comercial fica intacto
        </div>
    </div>
    <?php
}

// ══════════════════════════════════════════════════════════════════
// PÁGINA DE SKUs — VISÃO GERAL E REGENERAÇÃO
// ══════════════════════════════════════════════════════════════════

function efatabook_pagina_skus(): void {
    $mensagem = '';

    // Regenerar todos os SKUs
    if (
        isset( $_POST['efatabook_sku_nonce'] ) &&
        wp_verify_nonce( $_POST['efatabook_sku_nonce'], 'efatabook_regenerar_skus' )
    ) {
        $ids = get_posts( [
            'post_type'      => 'product',
            'posts_per_page' => -1,
            'post_status'    => 'any',
            'fields'         => 'ids',
        ] );
        $count = 0;
        foreach ( $ids as $id ) {
            $sku = efatabook_gerar_sku( $id );
            if ( $sku !== '' ) {
                efatabook_aplicar_sku( $id );
                $count++;
            }
        }
        $mensagem = "<div class='notice notice-success'><p>✅ <strong>$count</strong> SKUs regenerados com sucesso.</p></div>";
    }

    $produtos = get_posts( [
        'post_type'      => 'product',
        'posts_per_page' => -1,
        'post_status'    => 'any',
    ] );
    ?>
    <div class="wrap">
        <h1>🏷️ SKUs Efatabook</h1>
        <?= $mensagem ?>

        <p>
            Formato do SKU: <code>[2 primeiros dígitos do EAN][ID do WordPress]</code>
            &nbsp;|&nbsp; Exemplo: EAN <code>9788573804157</code>, ID <code>1877</code> → SKU <code>971877</code>
        </p>

        <form method="post" style="margin-bottom:20px">
            <?php wp_nonce_field( 'efatabook_regenerar_skus', 'efatabook_sku_nonce' ); ?>
            <button type="submit" class="button button-secondary">
                🔄 Regenerar todos os SKUs
            </button>
        </form>

        <table class="widefat striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome Comercial</th>
                    <th>Nome Fornecedor</th>
                    <th>EAN</th>
                    <th>Fornecedor</th>
                    <th>Cód. Interno</th>
                    <th>SKU Atual</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ( $produtos as $post ) :
                $id        = $post->ID;
                $product   = wc_get_product( $id );
                $sku       = $product ? $product->get_sku() : '—';
                $ean       = get_post_meta( $id, '_efatabook_ean', true );
                $fornecedor = get_post_meta( $id, '_efatabook_fornecedor', true );
                $cod       = get_post_meta( $id, '_efatabook_cod_interno', true );
                $nome_forn = get_post_meta( $id, '_efatabook_nome_fornecedor', true );
                $link      = get_edit_post_link( $id );
                $sem_ean   = empty( $ean ) ? ' style="color:#aaa"' : '';
            ?>
                <tr<?= $sem_ean ?>>
                    <td><a href="<?= esc_url( $link ) ?>" target="_blank"><?= $id ?></a></td>
                    <td><?= esc_html( $post->post_title ) ?></td>
                    <td style="color:#888;font-size:12px"><?= esc_html( $nome_forn ?: '—' ) ?></td>
                    <td><code><?= esc_html( $ean ?: '—' ) ?></code></td>
                    <td><?= esc_html( $fornecedor ?: '—' ) ?></td>
                    <td><?= esc_html( $cod ?: '—' ) ?></td>
                    <td><strong><code><?= esc_html( $sku ) ?></code></strong></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}

