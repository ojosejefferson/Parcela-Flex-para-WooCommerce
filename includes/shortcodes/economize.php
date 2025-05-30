<?php
class EconomizeShortcode {
    public function parcelas_flex_economize_shortcode() {
        global $product;
        $texto_economize = get_option('parcelas_flex_texto_economize', 'Economize no Pix');

        if (!is_a($product, 'WC_Product')) {
            $product = wc_get_product(get_the_ID());
        }

        if (!$product) {
            return '<p>Desconto disponível apenas na página de produtos ou em loops de produtos.</p>';
        }

        $output = "<div id='economize-container'>";
        $desconto_pix = floatval(get_option('desconto_pix', 0));

        // Obter o preço de venda atual para produtos simples ou variáveis
        $preco_venda = $product->is_type('variable') ? 
            floatval($product->get_variation_price('min', true)) : 
            floatval($product->get_price());

        // Calcular a economia no Pix com base no preço de venda atual
        $economia_pix = $preco_venda * ($desconto_pix / 100);

        if ($economia_pix > 0) {
            $output .= '<div class="economize-container">
            <img src="' . plugin_dir_url(__FILE__) . '../src/imagem/icon-descont2.svg" alt="Ícone de boleto" width="20" height="20">
            <span class="economize-text">'. esc_html($texto_economize) . ' ' . wc_price($economia_pix) . '
            </span>
            <span class="economize-amount"><?php echo $amount_saved; ?></span>
        </div>
        ';
        }

        $output .= "</div>";
        return $output;
    }

    public function buscar_economize() {
        $texto_economize = get_option('parcelas_flex_texto_economize', 'Economize no Pix');

        if (!isset($_POST['preco'])) {
            wp_send_json_error('Dados insuficientes para calcular a economia.');
            wp_die();
        }

        $preco_venda = floatval($_POST['preco']);
        $desconto_pix = floatval(get_option('desconto_pix', 0));

        $economia_pix = $preco_venda * ($desconto_pix / 100);

        if ($economia_pix > 0) {
            wp_send_json_success(
                '<div class="economize-container">
            <img src="' . plugin_dir_url(__FILE__) . '../src/imagem/icon-descont2.svg" alt="Ícone de boleto" width="20" height="20">
            <span class="economize-text">'. esc_html($texto_economize) . ' ' . wc_price($economia_pix) . '
            </span>
            <span class="economize-amount"><?php echo $amount_saved; ?></span>
        </div>
        '
            );
        } else {
            wp_send_json_success('<p>Não há descontos disponíveis para este produto.</p>');
        }

        wp_die();
    }

    public function parcelas_flex_economize_loop_shortcode() {
        $texto_economize = get_option('parcelas_flex_texto_economize', 'Economize no Pix');

        // Verifica se estamos dentro do loop de produtos do WooCommerce
        if (wc_get_loop_prop('is_shortcode') && wc_get_loop_prop('name') === 'products') {
            $product = wc_get_product(get_the_ID());
        } else {
            global $product;
        }

        // Se não temos um produto, retornamos uma mensagem de erro
        if (!$product) {
            return '<p>Desconto disponível apenas na página de produtos ou em loops de produtos.</p>';
        }

        $output = "<div id='economize-container'>";
        $desconto_pix = floatval(get_option('desconto_pix', 0));

        // Obter o preço regular para produtos simples ou variáveis
        $preco_regular = $product->is_type('variable') ? 
            floatval($product->get_variation_regular_price('min', true)) : 
            floatval($product->get_regular_price());

        // Calcular a economia no Pix com base no preço regular
        $economia_pix = $preco_regular * ($desconto_pix / 100);

        if ($economia_pix > 0) {
            $output .= '<div class="economize-container">
            <img src="' . plugin_dir_url(__FILE__) . '../src/imagem/icon-descont2.svg" alt="Ícone de boleto" width="20" height="20">
            <span class="economize-text">'. esc_html($texto_economize) . ' ' . wc_price($economia_pix) . '
            </span>
            <span class="economize-amount"><?php echo $amount_saved; ?></span>
        </div>
        ';
        }

        $output .= "</div>";
        return $output;
    }
}

$economize_shortcode = new EconomizeShortcode();

add_shortcode('economize', array($economize_shortcode, 'parcelas_flex_economize_shortcode'));
add_shortcode('economize_loop', array($economize_shortcode, 'parcelas_flex_economize_loop_shortcode'));

add_action('wp_ajax_buscar_economize', array($economize_shortcode, 'buscar_economize'));
add_action('wp_ajax_nopriv_buscar_economize', array($economize_shortcode, 'buscar_economize'));
