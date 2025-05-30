<?php
class DescontoPixShortcode {
    public function parcelas_flexsconto_pix_shortcode() {
        // Verifica se temos um produto global ou se estamos dentro do loop de produtos
        $texto_a_vista = get_option('parcelas_flex_texto_a_vista', 'à vista');
        $texto_no_pix = get_option('parcelas_flex_texto_no_pix', 'no Pix');
        $product = wc_get_product(get_the_ID());

        // Se temos um produto válido
        if ($product && ($product instanceof WC_Product)) {
            // Prepara o HTML para resposta
            $output = "<div id='desconto-pix-container'>";
            $preco = 0;
            $produto_disponivel = false;

            // Verifica o tipo do produto
            switch ($product->get_type()) {
                case 'variable':
                    // Produto variável
                    $variacoes = $product->get_available_variations();
                    $preco_minimo = PHP_FLOAT_MAX;

                    foreach ($variacoes as $variacao) {
                        if ($variacao['is_purchasable'] && $variacao['is_in_stock']) {
                            $produto_disponivel = true;
                            $preco_variacao = floatval($variacao['display_price']);
                            if ($preco_variacao < $preco_minimo) {
                                $preco_minimo = $preco_variacao;
                            }
                        }
                    }

                    if ($produto_disponivel) {
                        $preco = $preco_minimo;
                    }
                    break;

                case 'grouped':
                    // Produto agrupado
                    $children = $product->get_children();
                    $preco_minimo = PHP_FLOAT_MAX;

                    foreach ($children as $child_id) {
                        $child = wc_get_product($child_id);
                        if ($child && $child->is_purchasable() && $child->is_in_stock()) {
                            $produto_disponivel = true;
                            $preco_filho = floatval($child->get_price());
                            if ($preco_filho < $preco_minimo) {
                                $preco_minimo = $preco_filho;
                            }
                        }
                    }

                    if ($produto_disponivel) {
                        $preco = $preco_minimo;
                    }
                    break;

                case 'external':
                    // Produto externo
                    if ($product->is_purchasable()) {
                        $produto_disponivel = true;
                        $preco = floatval($product->get_price());
                    }
                    break;

                default:
                    // Produtos simples, virtuais, baixáveis
                    if ($product->is_purchasable() && $product->is_in_stock()) {
                        $produto_disponivel = true;
                        $preco = floatval($product->get_price());
                    }
                    break;
            }

            // Verifica se o produto está disponível e tem preço válido
            if ($produto_disponivel && $preco > 0) {
                $desconto_pix = floatval(get_option('desconto_pix', 0));
                $preco_com_desconto_pix = $preco * (1 - ($desconto_pix / 100));
                $preco_formatado = wc_price($preco_com_desconto_pix);
                $output .= '
                <div class="opcao-pagamento pix">
                    <img src="' . plugin_dir_url(__FILE__) . '../src/imagem/icon-pix.svg" alt="Ícone de Pix" width="20" height="20">
                    <span class="parcelas">' . esc_html($texto_a_vista) . '</span>
                    <div class="desconto-container">
                        <span class="preco">' . $preco_formatado . ' </span>
                        <span class="textodesconto"> ' . esc_html($texto_no_pix) . ' </span>
                        <div class="badge-container">
                            <div class="best-price__Badge-sc-1v0eo34-3 hWoKbG badge">
                                -' . $desconto_pix . '%
                            </div>
                        </div>
                    </div>
                </div>';
            } else {
                $output .= "<p>Selecione uma opção de produto para ver o desconto do Pix.</p>";
            }

            $output .= "</div>";
            return $output;
        } else {
            return '<p>Desconto do Pix disponível apenas na página de produtos.</p>';
        }
    }

    public function buscar_desconto_pix() {
        $texto_a_vista = get_option('parcelas_flex_texto_a_vista', 'à vista');
        $texto_no_pix = get_option('parcelas_flex_texto_no_pix', 'no Pix');

        if (!isset($_POST['preco'])) {
            wp_send_json_error('Preço não foi enviado.');
            wp_die();
        }

        $preco = floatval($_POST['preco']);
        $desconto_pix = floatval(get_option('desconto_pix', 0));
        $preco_com_desconto_pix = $preco * (1 - ($desconto_pix / 100));
        $preco_formatado = wc_price($preco_com_desconto_pix);

        wp_send_json_success('
        <div class="opcao-pagamento pix">
            <img src="' . plugin_dir_url(__FILE__) . '../src/imagem/icon-pix.svg" alt="Ícone de Pix" width="20" height="20">
            <span class="parcelas">' . esc_html($texto_a_vista) . '</span>
            <span class="preco">' . $preco_formatado . '</span>
            <div class="desconto-container">
                <span class="textodesconto"> ' . esc_html($texto_no_pix) . ' </span>
                <div class="badge-container">
                    <div class="best-price__Badge-sc-1v0eo34-3 hWoKbG badge">
                        <!-- SVG code -->
                        -' . $desconto_pix . '%
                    </div>
                </div>
            </div>
        </div>
    ');
    
        
        wp_die();
    }

    public function parcelas_flexsconto_pix_loop_shortcode() {
        $texto_a_vista = get_option('parcelas_flex_texto_a_vista', 'à vista');
        $texto_no_pix = get_option('parcelas_flex_texto_no_pix', 'no Pix');
        global $product;

        // Se não estamos dentro de um loop de produtos, tentamos obter o produto global
        if (!is_a($product, 'WC_Product')) {
            $product = wc_get_product(get_the_ID());
        }

        // Se ainda não temos um produto, retornamos uma mensagem de erro
        if (!is_a($product, 'WC_Product')) {
            return '<p>Desconto do Pix disponível apenas em loops de produtos.</p>';
        }

        // Verifica se o produto é variável ou simples
        if ($product->is_type('variable')) {
            $preco = floatval($product->get_variation_price('min', true)); // Preço mínimo da variação
        } else {
            $preco = floatval($product->get_price()); // Preço atual do produto
        }

        $desconto_pix = floatval(get_option('desconto_pix', 0));
        $preco_com_desconto_pix = $preco * (1 - ($desconto_pix / 100));
        $preco_formatado = wc_price($preco_com_desconto_pix);

        // Layout simplificado apenas com o texto em verde
        $output = '<div class="desconto-pix-loop-simples">';
        $output .= '<span class="preco-pix">' . $preco_formatado . '</span>';
        $output .= '<span class="texto-pix"> ' . esc_html($texto_no_pix) . '</span>';
        $output .= '</div>';

        return $output;
    }
}

$desconto_pix_shortcode = new DescontoPixShortcode();

add_shortcode('desconto_pix', array($desconto_pix_shortcode, 'parcelas_flexsconto_pix_shortcode'));
add_action('wp_ajax_buscar_desconto_pix', array($desconto_pix_shortcode, 'buscar_desconto_pix'));
add_action('wp_ajax_nopriv_buscar_desconto_pix', array($desconto_pix_shortcode, 'buscar_desconto_pix'));
add_shortcode('desconto_pix_loop', array($desconto_pix_shortcode, 'parcelas_flexsconto_pix_loop_shortcode'));

// Registra e enfileira o CSS
function parcelas_flex_enqueue_styles() {
    // Garante que só executa no frontend
    if (is_admin()) {
        return;
    }

    // Registra e enfileira o CSS
    wp_enqueue_style(
        'parcelas-flex-styles',
        plugins_url('../../assets/css/formadepagamentos.css', __FILE__),
        array(),
        filemtime(plugin_dir_path(__FILE__) . '../../assets/css/formadepagamentos.css')
    );
}
add_action('wp_enqueue_scripts', 'parcelas_flex_enqueue_styles');

// Função para exibir o shortcode do Pix abaixo do preço
function mostrar_pix_shortcode_abaixo_do_preco($price_html, $product) {
    // Evita execução no painel do WordPress
    if (is_admin()) {
        return $price_html;
    }

    // Evita exibir no topo da página de produto individual
    if (is_product() && !did_action('woocommerce_after_shop_loop_item')) {
        return $price_html;
    }

    // Preço e cálculo do desconto Pix
    $preco = floatval($product->get_price());
    $desconto_pix = floatval(get_option('desconto_pix', 0));
    $texto_no_pix = get_option('parcelas_flex_texto_no_pix', 'no Pix');

    $preco_com_desconto_pix = $preco * (1 - ($desconto_pix / 100));
    $preco_formatado = wc_price($preco_com_desconto_pix);

    // HTML Pix abaixo do preço
    $price_html .= '<div class="desconto-pix-loop-simples">';
    $price_html .= '<span class="preco-pix">' . $preco_formatado . ' </span>';
    $price_html .= '<span class="texto-pix">' . esc_html($texto_no_pix) . ' </span>';
    $price_html .= '<span class="badge badge-pix">-' . $desconto_pix . '%</span>';
    $price_html .= '</div>';

    return $price_html;
}
add_filter('woocommerce_get_price_html', 'mostrar_pix_shortcode_abaixo_do_preco', 30, 2);


