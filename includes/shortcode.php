<?php
function meu_plugin_desconto_pix_shortcode()
{
    // Verifica se temos um produto global ou se estamos dentro do loop de produtos
    $texto_a_vista = get_option('meu_plugin_texto_a_vista', 'à vista');
    $texto_no_pix = get_option('meu_plugin_texto_no_pix', 'no Pix');
    $product = wc_get_product(get_the_ID());
    

    // Se temos um produto válido
    if ($product && ($product instanceof WC_Product_Variable || $product instanceof WC_Product)) {
        // Prepara o HTML para resposta
        $output = "<div id='desconto-pix-container'>";

        // Se o produto for variável, tenta encontrar o preço padrão
        if ($product instanceof WC_Product_Variable) {
            $variacoes = $product->get_available_variations();
            $preco_minimo = null;

            foreach ($variacoes as $variacao) {
                $variacao_obj = wc_get_product($variacao['variation_id']);
                $preco_variacao = floatval($variacao_obj->get_price());

                if (is_null($preco_minimo) || $preco_variacao < $preco_minimo) {
                    $preco_minimo = $preco_variacao;
                }
            }

            if (!is_null($preco_minimo)) {
                $desconto_pix = floatval(get_option('desconto_pix', 0));
                $preco_com_desconto_pix = $preco_minimo * (1 - ($desconto_pix / 100));
                $preco_formatado = wc_price($preco_com_desconto_pix);
                $output .= '
                <div class="opcao-pagamento pix" itemscope itemtype="http://schema.org/PaymentMethod">
                    <i class="fa-brands fa-pix"></i>
                    <span class="parcelas">'. esc_html($texto_a_vista) .'</span>
                    <span class="preco" itemprop="price">' . $preco_formatado . '</span>
                    <div class="desconto-container" itemprop="offers" itemscope itemtype="http://schema.org/Offer">
                        <meta itemprop="price">
                        <meta itemprop="priceCurrency" content="BRL">
                        <span class="textodesconto" itemprop="description">'. esc_html($texto_no_pix) .'</span>
                        <div class="badge-container">
                            <div class="best-price__Badge-sc-1v0eo34-3 hWoKbG badge">
                                <!-- SVG code -->
                                <span itemprop="discount">-' . $desconto_pix . '%</span>
                            </div>
                        </div>
                    </div>
                </div>';
            } else {
                $output .= "<p>Selecione uma opção de produto para ver o desconto do Pix.</p>";
            }
        } else {
            // Se o produto for simples, calcula o desconto
            $preco = floatval($product->get_price());
            $desconto_pix = floatval(get_option('desconto_pix', 0));
            $preco_com_desconto_pix = $preco * (1 - ($desconto_pix / 100));
            $preco_formatado = wc_price($preco_com_desconto_pix);
            $output .= '
            <div class="opcao-pagamento pix" itemscope itemtype="http://schema.org/PaymentMethod">
                <i class="fa-brands fa-pix"></i>
                <span class="parcelas">' . esc_html($texto_a_vista) . '</span>
                <span class="preco" itemprop="price">' . $preco_formatado . '</span>
                <div class="desconto-container" itemprop="offers" itemscope itemtype="http://schema.org/Offer">
                    <meta itemprop="price">
                    <meta itemprop="priceCurrency" content="BRL">
                    <span class="textodesconto" itemprop="description">' . esc_html($texto_no_pix) . '</span>
                    <div class="badge-container">
                        <div class="best-price__Badge-sc-1v0eo34-3 hWoKbG badge">
                            <!-- SVG code -->
                            <span itemprop="discount">-' . $desconto_pix . '%</span>
                        </div>
                    </div>
                </div>
            </div>';
        }

        $output .= "</div>";

        // Retorna o HTML que será substituído pelo shortcode
        return $output;
    } else {
        // Se não estiver na página do produto ou o produto não for válido, retorna uma mensagem de erro
        return '<p>Desconto do Pix disponível apenas na página de produtos.</p>';
    }
}

add_shortcode('desconto_pix', 'meu_plugin_desconto_pix_shortcode');




function buscar_desconto_pix()
{
    $texto_a_vista = get_option('meu_plugin_texto_a_vista', 'à vista');
    $texto_no_pix = get_option('meu_plugin_texto_no_pix', 'no Pix');

    if (!isset($_POST['preco'])) {
        wp_send_json_error('Preço não foi enviado.');
        wp_die();
    }

    $preco = floatval($_POST['preco']);
    $desconto_pix = floatval(get_option('desconto_pix', 0));
    $preco_com_desconto_pix = $preco * (1 - ($desconto_pix / 100));
    $preco_formatado = wc_price($preco_com_desconto_pix);

    wp_send_json_success('<div class="opcao-pagamento pix" itemscope itemtype="http://schema.org/PaymentMethod">
    <i class="fa-brands fa-pix"></i>
    <span class="parcelas">' . esc_html($texto_a_vista) . '</span>
    <span class="preco" itemprop="price">' . $preco_formatado . '</span>
    <div class="desconto-container" itemprop="offers" itemscope itemtype="http://schema.org/Offer">
        <meta itemprop="price">
        <meta itemprop="priceCurrency" content="BRL">
        <span class="textodesconto" itemprop="description">'. esc_html($texto_no_pix) .'</span>
        <div class="badge-container">
            <div class="best-price__Badge-sc-1v0eo34-3 hWoKbG badge">
                <!-- SVG code -->
                <span itemprop="discount">-' . $desconto_pix . '%</span>
            </div>
        </div>
    </div>
</div>');
    wp_die();
}
add_action('wp_ajax_buscar_desconto_pix', 'buscar_desconto_pix');
add_action('wp_ajax_nopriv_buscar_desconto_pix', 'buscar_desconto_pix');



function meu_plugin_desconto_pix_loop_shortcode()
{

    $texto_a_vista = get_option('meu_plugin_texto_a_vista', 'à vista');
    $texto_no_pix = get_option('meu_plugin_texto_no_pix', 'no Pix');
    global $product;

    // Se não estamos dentro de um loop de produtos, tentamos obter o produto global
    if (!is_a($product, 'WC_Product')) {
        $product = wc_get_product(get_the_ID());
    }

    // Se ainda não temos um produto, retornamos uma mensagem de erro
    if (!is_a($product, 'WC_Product')) {
        return '<p>Desconto do Pix disponível apenas em loops de produtos.</p>';
    }

    $output = "<div class='desconto-pix-loop-container'>";

    // Verifica se o produto é variável ou simples
    if ($product->is_type('variable')) {
        $preco = floatval($product->get_variation_price('min', true)); // Preço mínimo da variação
    } else {
        $preco = floatval($product->get_price()); // Preço atual do produto
    }

    $desconto_pix = floatval(get_option('desconto_pix', 0));
    $preco_com_desconto_pix = $preco * (1 - ($desconto_pix / 100));
    $preco_formatado = wc_price($preco_com_desconto_pix);

    // Aqui você pode adicionar o HTML personalizado para o loop
    $output .= '
    <div class="opcao-pagamento2 pix" itemscope itemtype="http://schema.org/PaymentMethod">
        <i class="fa-brands fa-pix"></i>
        <span class="parcelas">' . esc_html($texto_a_vista) . '</span>
        <span class="preco" itemprop="price">' . $preco_formatado . '</span>
        <div class="desconto-container" itemprop="offers" itemscope itemtype="http://schema.org/Offer">
            <meta itemprop="price">
            <meta itemprop="priceCurrency" content="BRL">
            <span class="textodesconto" itemprop="description">'. esc_html($texto_no_pix) .'</span>
            <div class="badge-container">
                <div class="best-price__Badge-sc-1v0eo34-3 hWoKbG badge">
                    <!-- SVG code -->
                    <span itemprop="discount">-' . $desconto_pix . '%</span>
                </div>
            </div>
        </div>
    </div>';

    $output .= "</div>";

    return $output;
}

add_shortcode('desconto_pix_loop', 'meu_plugin_desconto_pix_loop_shortcode');








function meu_plugin_desconto_boleto_shortcode()
{
    global $product;
    $texto_a_boleto = get_option('meu_plugin_texto_a_boleto', 'à vista Boleto');
    $texto_no_boleto = get_option('meu_plugin_texto_no_boleto', 'no Boleto');

    // Tenta obter o produto global se não estiver definido
    if (!is_a($product, 'WC_Product')) {
        $product = wc_get_product(get_the_ID());
    }

    // Se ainda não temos um produto, retornamos uma mensagem de erro
    if (!is_a($product, 'WC_Product')) {
        return '<p>Desconto no boleto disponível apenas na página de produtos ou em loops de produtos.</p>';
    }

    $output = "<div id='desconto-boleto-container'>";

    // Verifica se o produto é variável ou simples
    if ($product->is_type('variable')) {
        $preco = floatval($product->get_variation_price('min', true)); // Preço mínimo da variação
    } else {
        $preco = floatval($product->get_price()); // Preço atual do produto
    }

    $desconto_boleto = floatval(get_option('desconto_boleto', 0));
    $preco_com_desconto_boleto = $preco * (1 - ($desconto_boleto / 100));
    $preco_formatado = wc_price($preco_com_desconto_boleto);

    $output .= meu_plugin_gerar_html_desconto_boleto($preco_formatado, $desconto_boleto);
    $output .= "</div>";

    return $output;
}

// Certifique-se de registrar o shortcode
add_shortcode('desconto_boleto', 'meu_plugin_desconto_boleto_shortcode');


function meu_plugin_desconto_boleto_loop_shortcode()
{
    global $product;
    
    $texto_a_boleto = get_option('meu_plugin_texto_a_boleto', 'à vista Boleto');
    $texto_no_boleto = get_option('meu_plugin_texto_no_boleto', 'no Boleto');


    // Tenta obter o produto global se não estiver definido
    if (!is_a($product, 'WC_Product')) {
        $product = wc_get_product(get_the_ID());
    }

    // Se ainda não temos um produto, retornamos uma mensagem de erro
    if (!is_a($product, 'WC_Product')) {
        return '<p>Desconto no boleto disponível apenas na página de produtos ou em loops de produtos.</p>';
    }

    $output = "<div class='desconto-boleto-loop-container'>"; // Classe modificada para estilização específica do loop

    // Verifica se o produto é variável ou simples
    if ($product->is_type('variable')) {
        $preco = floatval($product->get_variation_price('min', true)); // Preço mínimo da variação
    } else {
        $preco = floatval($product->get_price()); // Preço atual do produto
    }

    $desconto_boleto = floatval(get_option('desconto_boleto', 0));
    $preco_com_desconto_boleto = $preco * (1 - ($desconto_boleto / 100));
    $preco_formatado = wc_price($preco_com_desconto_boleto);

    // Supondo que você tenha uma função chamada meu_plugin_gerar_html_desconto_boleto
    $output .= meu_plugin_gerar_html_desconto_boleto($preco_formatado, $desconto_boleto);
    $output .= "</div>";

    return $output;
}

// Certifique-se de registrar o novo shortcode
add_shortcode('desconto_boleto_loop', 'meu_plugin_desconto_boleto_loop_shortcode');



function buscar_desconto_boleto()
{


    if (!isset($_POST['preco'])) {
        wp_send_json_error('Preço não foi enviado.');
        wp_die();
    }

    $preco = floatval($_POST['preco']);
    $desconto_boleto = floatval(get_option('desconto_boleto', 0));
    $preco_com_desconto_boleto = $preco * (1 - ($desconto_boleto / 100));
    $preco_formatado = wc_price($preco_com_desconto_boleto);

    wp_send_json_success(meu_plugin_gerar_html_desconto_boleto($preco_formatado, $desconto_boleto));
    wp_die();
}

add_action('wp_ajax_buscar_desconto_boleto', 'buscar_desconto_boleto');
add_action('wp_ajax_nopriv_buscar_desconto_boleto', 'buscar_desconto_boleto');

// Função auxiliar para gerar o HTML do desconto no boleto
function meu_plugin_gerar_html_desconto_boleto($preco_formatado, $desconto_boleto)
{
    $texto_a_boleto = get_option('meu_plugin_texto_a_boleto', 'à vista Boleto');
    $texto_no_boleto = get_option('meu_plugin_texto_no_boleto', 'no Boleto');
    
    return '<div class="opcao-pagamento" itemscope itemtype="http://schema.org/PaymentMethod">
        <i class="fa-solid fa-barcode"></i>
        <span class="preco" itemprop="price"> ' . $preco_formatado . ' </span>
        <span class="parcelas">' . esc_html($texto_a_boleto) . ' <span itemprop="name"> ' . esc_html($texto_no_boleto) .' </span></span>
        <div class="best-price__Badge-sc-1v0eo34-3 hWoKbG badge">' . (-$desconto_boleto) . '%</div>
    </div>';
    wp_die();
}



add_action('wp_ajax_buscar_desconto_boleto', 'buscar_desconto_boleto');
add_action('wp_ajax_nopriv_buscar_desconto_boleto', 'buscar_desconto_boleto');








function meu_plugin_melhor_parcela_shortcode() {
    global $product;

    $texto_melhor_parcela = get_option('meu_plugin_texto_melhor_parcela', 'sem juros');
    $texto_melhor_parcela_cjuros = get_option('meu_plugin_texto_melhor_parcela_cjuros', 'com juros');

    if (!$product || !($product instanceof WC_Product)) {
        return '<div id="melhor-parcela-container">Produto não encontrado.</div>';
    }

    $preco = floatval($product->get_price());
    $valor_minimo_parcela = floatval(get_option('valor_minimo_parcela', 0)); // Valor definido pelo usuário

    // Se o preço do produto for menor ou igual ao valor mínimo para parcelamento, exiba apenas 1x sem juros
    if ($preco <= $valor_minimo_parcela) {
        return '<div id="melhor-parcela-container">
            <div class="opcao-pagamento" itemscope itemtype="http://schema.org/PaymentMethod">
                <i class="wci-icon-best-installments fa-regular fa-credit-card"></i>
                <span class="parcelas">1x de</span>
                <span class="preco" itemprop="price">' . wc_price($preco) . '</span>
                <span class="parcelas">' . esc_html($texto_melhor_parcela) . ' </span>
            </div>
        </div>';
    }

    // Se o preço do produto for maior que o valor mínimo, calcule a melhor parcela sem juros
    $melhor_parcela_sem_juros = 0;
    $valor_melhor_parcela = PHP_FLOAT_MAX;

    for ($i = 1; $i <= 12; $i++) {
        $juros = floatval(get_option("parcelamento_juros_$i", 0));
        if ($juros == 0 && ($preco / $i < $valor_melhor_parcela)) {
            $melhor_parcela_sem_juros = $i;
            $valor_melhor_parcela = $preco / $i;
        }
    }

    if ($melhor_parcela_sem_juros > 0) {
        return '<div id="melhor-parcela-container">
            <div class="opcao-pagamento" itemscope itemtype="http://schema.org/PaymentMethod">
                <i class="wci-icon-best-installments fa-regular fa-credit-card"></i>
                <span class="parcelas">' . $melhor_parcela_sem_juros . 'x de</span>
                <span class="preco" itemprop="price">' . wc_price($valor_melhor_parcela) . '</span>
                <span class="parcelas">' . esc_html($texto_melhor_parcela) . '</span></span>
            </div>
        </div>';
    }

    return '<div id="melhor-parcela-container"></div>'; // Retorna um contêiner vazio se não houver parcelas sem juros
}
add_shortcode('melhor_parcelamento', 'meu_plugin_melhor_parcela_shortcode');


function meu_plugin_melhor_parcela_loop_shortcode() {
    global $product;
    $texto_melhor_parcela = get_option('meu_plugin_texto_melhor_parcela', 'sem juros');
    $texto_melhor_parcela_cjuros = get_option('meu_plugin_texto_melhor_parcela_cjuros', 'com juros');
    
    if (!$product || !($product instanceof WC_Product)) {
        return '<div class="melhor-parcela-loop-container">Produto não encontrado.</div>';
    }

    $preco = floatval($product->get_price());
    $valor_minimo_parcela = floatval(get_option('valor_minimo_parcela', 0)); // Valor definido pelo usuário

    // Se o preço do produto for menor ou igual ao valor mínimo para parcelamento, exiba apenas 1x sem juros
    if ($preco <= $valor_minimo_parcela) {
        return '<div class="melhor-parcela-loop-container">
            <div class="opcao-pagamento-loop" itemscope itemtype="http://schema.org/PaymentMethod">
                <i class="wci-icon-best-installments fa-regular fa-credit-card"></i>
                <span class="parcelas-loop">1x de</span>
                <span class="preco-loop" itemprop="price">' . wc_price($preco) . '</span>
                <span class="parcelas-loop">' . esc_html($texto_melhor_parcela) . '</span>
            </div>
        </div>';
    }

    // Continua a lógica para calcular a melhor parcela sem juros se o preço for maior que o valor mínimo
    $melhor_parcela_sem_juros = 0;
    $valor_melhor_parcela = PHP_FLOAT_MAX;

    for ($i = 1; $i <= 12; $i++) {
        $juros = floatval(get_option("parcelamento_juros_$i", 0));
        if ($juros == 0 && ($preco / $i) < $valor_melhor_parcela) {
            $melhor_parcela_sem_juros = $i;
            $valor_melhor_parcela = $preco / $i;
        }
    }

    if ($melhor_parcela_sem_juros > 0 && $preco > $valor_minimo_parcela) {
        return '<div class="melhor-parcela-loop-container">
            <div class="opcao-pagamento-loop" itemscope itemtype="http://schema.org/PaymentMethod">
                <i class="wci-icon-best-installments fa-regular fa-credit-card"></i>
                <span class="parcelas-loop">' . $melhor_parcela_sem_juros . 'x de</span>
                <span class="preco-loop" itemprop="price">' . wc_price($valor_melhor_parcela) . '</span>
                <span class="parcelas-loop">' . esc_html($texto_melhor_parcela) . '</span>
            </div>
        </div>';
    }

    // Se não houver parcelas sem juros ou o preço for menor ou igual ao valor mínimo, retorna 1x sem juros
    return '<div class="melhor-parcela-loop-container">
        <div class="opcao-pagamento-loop" itemscope itemtype="http://schema.org/PaymentMethod">
            <i class="wci-icon-best-installments fa-regular fa-credit-card"></i>
            <span class="parcelas-loop">1x de</span>
            <span class="preco-loop" itemprop="price">' . wc_price($preco) . '</span>
            <span class="parcelas-loop">' . esc_html($texto_melhor_parcela) . '/span>
        </div>
    </div>';
}
add_shortcode('melhor_parcelamento_loop', 'meu_plugin_melhor_parcela_loop_shortcode');




function buscar_melhor_parcela_sem_juros() {

    $texto_melhor_parcela = get_option('meu_plugin_texto_melhor_parcela', 'sem juros');
    $texto_melhor_parcela_cjuros = get_option('meu_plugin_texto_melhor_parcela_cjuros', 'com juros');

    if (!isset($_POST['preco'])) {
        wp_send_json_error('Preço não foi enviado.');
        wp_die();
    }

    $preco = floatval($_POST['preco']);
    $valor_minimo_parcela = floatval(get_option('valor_minimo_parcela', 0)); // Valor definido pelo usuário
    $melhor_parcela_sem_juros = 0;
    $valor_melhor_parcela = PHP_FLOAT_MAX;

    // Se o preço do produto for menor ou igual ao valor mínimo para parcelamento, exiba apenas 1x sem juros
    if ($preco <= $valor_minimo_parcela) {
        wp_send_json_success('
        <div class="opcao-pagamento" itemscope itemtype="http://schema.org/PaymentMethod">
            <i class="wci-icon-best-installments fa-regular fa-credit-card"></i>
            <span class="parcelas">1x de</span>
            <span class="preco" itemprop="price">' . wc_price($preco) . '</span>
            <span class="parcelas">' . esc_html($texto_melhor_parcela) . '</span>
        </div>
        ');
        wp_die();
    }

    // Continua a lógica para calcular a melhor parcela sem juros se o preço for maior que o valor mínimo
    for ($i = 1; $i <= 12; $i++) {
        $juros = floatval(get_option("parcelamento_juros_$i", 0));
        if ($juros == 0 && ($preco / $i) < $valor_melhor_parcela) {
            $melhor_parcela_sem_juros = $i;
            $valor_melhor_parcela = $preco / $i;
        }
    }

    if ($melhor_parcela_sem_juros > 0) {
        wp_send_json_success('
        <div class="opcao-pagamento" itemscope itemtype="http://schema.org/PaymentMethod">
            <i class="wci-icon-best-installments fa-regular fa-credit-card"></i>
            <span class="parcelas">' . $melhor_parcela_sem_juros . 'x de</span>
            <span class="preco" itemprop="price">' . wc_price($valor_melhor_parcela) . '</span>
            <span class="parcelas">' . esc_html($texto_melhor_parcela) . '</span>
        </div>
        ');
    } else {
        wp_send_json_error('Não foi possível calcular o parcelamento.');
    }

    wp_die();
}
add_action('wp_ajax_buscar_melhor_parcela', 'buscar_melhor_parcela_sem_juros');
add_action('wp_ajax_nopriv_buscar_melhor_parcela', 'buscar_melhor_parcela_sem_juros');








function meu_plugin_tabela_parcelamento_shortcode() {
    global $product;
        $texto_melhor_parcela = get_option('meu_plugin_texto_melhor_parcela', 'sem juros');
        $texto_melhor_parcela_cjuros = get_option('meu_plugin_texto_melhor_parcela_cjuros', 'com juros');
    
    if (!$product || !($product instanceof WC_Product)) {
        return '<div id="tabela-parcelamento-container">Produto não encontrado.</div>';
    }

    $preco = $product->is_type('variable') ? $product->get_variation_price('min', true) : $product->get_price();
    $preco = floatval($preco);
    $valor_minimo_parcela = floatval(get_option('valor_minimo_parcela', 0)); // Valor definido pelo usuário
    $exibir_juros = get_option('exibir_juros_porcentagem', 0); // Obtenha a opção de exibir juros

    $output = "<div id='tabela-parcelamento-container'><table class='tabela-parcelamento'><tbody>";

    // Se o preço do produto for menor ou igual ao valor mínimo para parcelamento, exiba apenas 1x sem juros
    if ($preco <= $valor_minimo_parcela) {
        $output .= "<tr><td>1x de " . wc_price($preco) . " " . esc_html($texto_melhor_parcela) . "</td></tr>";
    } else {
        // Se o preço for maior, calcule as parcelas normalmente
        for ($i = 1; $i <= 12; $i++) {
            $juros = floatval(get_option("parcelamento_juros_$i", 0));
            if ($juros == 0) {
                $valor_parcela = $preco / $i;
                $output .= "<tr><td>{$i}x de " . wc_price($valor_parcela) . " " . esc_html($texto_melhor_parcela) . "</td></tr>";
            } else {
                $valor_total_com_juros = $preco * (1 + ($juros / 100));
                $valor_parcela = $valor_total_com_juros / $i;
                // Verifique se a opção de exibir juros está ativada antes de adicionar ao output
                if ($exibir_juros) {
                    $output .= "<tr><td>{$i}x de " . wc_price($valor_parcela) . " " . esc_html($texto_melhor_parcela_cjuros) . " de {$juros}%</td></tr>";
                } else {
                    $output .= "<tr><td>{$i}x de " . wc_price($valor_parcela) . " " . esc_html($texto_melhor_parcela_cjuros) . "</td></tr>";
                }
            }
        }
    }

    $output .= "</tbody></table></div>"; // Fechamos o div do contêiner aqui
    return $output;
}
add_shortcode('tabela_parcelamento', 'meu_plugin_tabela_parcelamento_shortcode');









function buscar_tabela_parcelamento() {

    $texto_melhor_parcela = get_option('meu_plugin_texto_melhor_parcela', 'sem juros');
    $texto_melhor_parcela_cjuros = get_option('meu_plugin_texto_melhor_parcela_cjuros', 'com juros');

    if (!isset($_POST['preco'])) {
        wp_send_json_error('Preço não foi enviado.');
        wp_die();
    }

    $preco = floatval($_POST['preco']);
    $valor_minimo_parcela = floatval(get_option('valor_minimo_parcela', 0)); // Valor definido pelo usuário
    $exibir_juros = get_option('exibir_juros_porcentagem', 0); // Obtenha a opção de exibir juros

    $output = "<table class='tabela-parcelamento'><tbody>";

    // Se o preço do produto for menor ou igual ao valor mínimo para parcelamento, exiba apenas 1x sem juros
    if ($preco <= $valor_minimo_parcela) {
        $output .= "<tr><td>1x de " . wc_price($preco) . " sem juros</td></tr>";
    } else {
        // Se o preço for maior, calcule as parcelas normalmente
        for ($i = 1; $i <= 12; $i++) {
            $juros = floatval(get_option("parcelamento_juros_$i", 0));
            if ($juros == 0) {
                $valor_parcela = $preco / $i;
                $output .= "<tr><td>{$i}x de " . wc_price($valor_parcela) . " sem juros</td></tr>";
            } else {
                $valor_total_com_juros = $preco * (1 + ($juros / 100));
                $valor_parcela = $valor_total_com_juros / $i;
                // Verifique se a opção de exibir juros está ativada antes de adicionar ao output
                if ($exibir_juros) {
                    $output .= "<tr><td>{$i}x de " . wc_price($valor_parcela) . " " . esc_html($texto_melhor_parcela_cjuros) . " de {$juros}%</td></tr>";
                } else {
                    $output .= "<tr><td>{$i}x de " . wc_price($valor_parcela) . " " . esc_html($texto_melhor_parcela_cjuros) . "</td></tr>";
                }
            }
        }
    }

    $output .= "</tbody></table>";

    wp_send_json_success($output);
    wp_die();
}
add_action('wp_ajax_buscar_tabela_parcelamento', 'buscar_tabela_parcelamento');
add_action('wp_ajax_nopriv_buscar_tabela_parcelamento', 'buscar_tabela_parcelamento');








function meu_plugin_economize_shortcode()
{
    global $product;
    $texto_economize = get_option('meu_plugin_texto_economize', 'Economize no Pix');


    if (!is_a($product, 'WC_Product')) {
        $product = wc_get_product(get_the_ID());
    }

    if (!$product) {
        return '<p>Desconto disponível apenas na página de produtos ou em loops de produtos.</p>';
    }

    $output = "<div id='economize-container'>";
    $desconto_pix = floatval(get_option('desconto_pix', 0));

    // Obter o preço de venda atual para produtos simples ou variáveis
    $preco_venda = $product->is_type('variable') ? $product->get_variation_price('min', true) : $product->get_price();

    // Calcular a economia no Pix com base no preço de venda atual
    $economia_pix = $preco_venda * ($desconto_pix / 100);

    if ($economia_pix > 0) {
        $output .= '<div style="margin-top: 8px;" class="product-meta__label-list"><span class="product-label product-label--on-sale">' . esc_html($texto_economize) . ' ' . wc_price($economia_pix) . '</span></div>';
    }

    $output .= "</div>";
    return $output;
}

add_shortcode('economize', 'meu_plugin_economize_shortcode');

function buscar_economize()
{
    $texto_economize = get_option('meu_plugin_texto_economize', 'Economize no Pix');

    if (!isset($_POST['preco_venda'])) {
        wp_send_json_error('Dados insuficientes para calcular a economia.');
        wp_die();
    }

    $preco_venda = floatval($_POST['preco_venda']);
    $desconto_pix = floatval(get_option('desconto_pix', 0));

    $economia_pix = $preco_venda * ($desconto_pix / 100);

    if ($economia_pix > 0) {
        wp_send_json_success(
            '<div style="margin-top: 8px;" class="product-meta__label-list"><span class="product-label product-label--on-sale">' . esc_html($texto_economize) . ' ' . wc_price($economia_pix) . '</span></div>'
        
        );
    } else {
        wp_send_json_success('<p>Não há descontos disponíveis para este produto.</p>');
    }

    wp_die();
}

add_action('wp_ajax_buscar_economize', 'buscar_economize');
add_action('wp_ajax_nopriv_buscar_economize', 'buscar_economize');

function meu_plugin_economize_loop_shortcode()
{
    $texto_economize = get_option('meu_plugin_texto_economize', 'Economize no Pix');

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
    $preco_regular = $product->is_type('variable') ? $product->get_variation_regular_price('min', true) : $product->get_regular_price();

    // Calcular a economia no Pix com base no preço regular
    $economia_pix = $preco_regular * ($desconto_pix / 100);

    if ($economia_pix > 0) {
        $output .= '<div style="margin-top: 8px;" class="product-meta__label-list"><span class="product-label product-label--on-sale">' . esc_html($texto_economize) . ' ' . wc_price($economia_pix) . '</span></div>';
    }

    $output .= "</div>";
    return $output;
}

add_shortcode('economize_loop', 'meu_plugin_economize_loop_shortcode');

