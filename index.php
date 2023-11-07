<?php
/**
 * Plugin Name: Parcelas Flex For WooCommerce
 * Plugin URI: https://www.linkedin.com/in/ojosejefferson/
 * Description: Um plugin para adicionar opções de parcelamento no WooCommerce com descontos para Pix e Boleto. Compatível com produtos variáveis, ele proporciona flexibilidade de pagamento e otimiza a conversão de vendas.
 * Version: 1.0
 * Author: José Jefferson
 * Author URI: https://www.linkedin.com/in/ojosejefferson/
 * License: GPL
 * Text Domain: parcelas-flex-for-woocommerce
 * Domain Path: /languages
 * WC requires at least: 5x
 * WC tested up to: 8.3
 * 
 * Tags: parcelamento, pagamento à vista, WooCommerce, Pix, boleto bancário, cartão de crédito, descontos, produtos variáveis, e-commerce, Brasil
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Registra as configurações
function meu_plugin_de_parcelamento_register_settings() {
    register_setting('meu-plugin-de-parcelamento-settings-group', 'desconto_pix');
    register_setting('meu-plugin-de-parcelamento-settings-group', 'desconto_boleto');
    register_setting('meu-plugin-de-parcelamento-settings-group', 'exibir_juros_porcentagem');

    for ($i = 1; $i <= 12; $i++) {
        register_setting('meu-plugin-de-parcelamento-settings-group', "parcelamento_juros_$i");
    }
}
add_action('admin_init', 'meu_plugin_de_parcelamento_register_settings');

// Adiciona a página de configurações ao menu do WooCommerce
function meu_plugin_de_parcelamento_add_admin_menu() {
    add_submenu_page(
        'woocommerce',
        'Configurações de Parcelamento',
        'Parcelamento',
        'manage_options',
        'meu-plugin-de-parcelamento',
        'meu_plugin_de_parcelamento_settings_page'
    );
}
add_action('admin_menu', 'meu_plugin_de_parcelamento_add_admin_menu');

// Cria a página de configurações
function meu_plugin_de_parcelamento_settings_page() {
    ?>
    
    <div class="wrap">
        <h1>Configurações de Parcelamento</h1>
        <form method="post" action="options.php">
            <?php settings_fields('meu-plugin-de-parcelamento-settings-group'); ?>
            <?php do_settings_sections('meu-plugin-de-parcelamento-settings-group'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Desconto Pix (%)</th>
                    <td>
                        <input type="number" name="desconto_pix" value="<?php echo esc_attr(get_option('desconto_pix')); ?>" step="0.01" min="0" max="100" class="small-text"/> %
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Desconto Boleto (%)</th>
                    <td>
                        <input type="number" name="desconto_boleto" value="<?php echo esc_attr(get_option('desconto_boleto')); ?>" step="0.01" min="0" max="100" class="small-text"/> %
                    </td>
                </tr>
                <?php for ($i = 1; $i <= 12; $i++): ?>
                <tr valign="top">
                    <th scope="row">Taxa de Juros para <?php echo $i; ?>x (%)</th>
                    <td>
                        <input type="number" name="parcelamento_juros_<?php echo $i; ?>" value="<?php echo esc_attr(get_option("parcelamento_juros_$i", 0)); ?>" step="0.01" min="0" max="100" class="small-text"/> %
                        <p class="description">Digite 0 para parcelamento sem juros.</p>
                    </td>
                </tr>
                
                <?php endfor; ?>
            </table>
            <tr valign="top">
    <th scope="row">Exibir porcentagem de juros</th>
    <td>
        <input type="checkbox" name="exibir_juros_porcentagem" value="1" <?php checked(1, get_option('exibir_juros_porcentagem', 0)); ?> />
        <label for="exibir_juros_porcentagem">Exibir a porcentagem de juros no frontend</label>
    </td>
</tr>
            <?php submit_button(); ?>
        </form>
    </div>



    <?php
}


function meu_plugin_desconto_pix_shortcode() {
    global $product;

    // Verifica se estamos na página de um produto e se temos um produto válido
    if (is_product() && is_object($product) && ($product instanceof WC_Product_Variable || $product instanceof WC_Product)) {
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
                    <span class="parcelas">à vista</span>
                    <span class="preco" itemprop="price">' . $preco_formatado . '</span>
                    <div class="desconto-container" itemprop="offers" itemscope itemtype="http://schema.org/Offer">
                        <meta itemprop="price">
                        <meta itemprop="priceCurrency" content="BRL">
                        <span class="textodesconto" itemprop="description">no Pix </span>
                        <div class="badge-container">
                            <div class="best-price__Badge-sc-1v0eo34-3 hWoKbG badge">
                                <!-- SVG code -->
                                <span itemprop="discount">(-' . $desconto_pix . '%)</span>
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
                <span class="parcelas">à vista</span>
                <span class="preco" itemprop="price">' . $preco_formatado . '</span>
                <div class="desconto-container" itemprop="offers" itemscope itemtype="http://schema.org/Offer">
                    <meta itemprop="price">
                    <meta itemprop="priceCurrency" content="BRL">
                    <span class="textodesconto" itemprop="description">no Pix </span>
                    <div class="badge-container">
                        <div class="best-price__Badge-sc-1v0eo34-3 hWoKbG badge">
                            <!-- SVG code -->
                            <span itemprop="discount">(-' . $desconto_pix . '%)</span>
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



function buscar_desconto_pix() {
    if (!isset($_POST['preco'])) {
        wp_send_json_error('Preço não foi enviado.');
        wp_die();
    }

    $preco = floatval($_POST['preco']);
    $desconto_pix = floatval(get_option('desconto_pix', 0));
    $preco_com_desconto_pix = $preco * (1 - ($desconto_pix / 100));
    $preco_formatado = wc_price($preco_com_desconto_pix);

    wp_send_json_success( '<div class="opcao-pagamento pix" itemscope itemtype="http://schema.org/PaymentMethod">
    <i class="fa-brands fa-pix"></i>
    <span class="parcelas">à vista</span>
    <span class="preco" itemprop="price">' . $preco_formatado . '</span>
    <div class="desconto-container" itemprop="offers" itemscope itemtype="http://schema.org/Offer">
        <meta itemprop="price">
        <meta itemprop="priceCurrency" content="BRL">
        <span class="textodesconto" itemprop="description">no Pix </span>
        <div class="badge-container">
            <div class="best-price__Badge-sc-1v0eo34-3 hWoKbG badge">
                <!-- SVG code -->
                <span itemprop="discount">(-' . $desconto_pix . '%)</span>
            </div>
        </div>
    </div>
</div>');
    wp_die();
}
add_action('wp_ajax_buscar_desconto_pix', 'buscar_desconto_pix');
add_action('wp_ajax_nopriv_buscar_desconto_pix', 'buscar_desconto_pix');











function meu_plugin_desconto_boleto_shortcode() {
    global $product;

    // Verifica se estamos na página de um produto e se temos um produto válido
    if (is_product() && is_object($product) && ($product instanceof WC_Product_Variable || $product instanceof WC_Product)) {
        // Prepara o HTML para resposta
        $output = "<div id='desconto-boleto-container'>";

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
                $desconto_boleto = floatval(get_option('desconto_boleto', 0));
                $preco_com_desconto_boleto = $preco_minimo * (1 - ($desconto_boleto / 100));
                $preco_formatado = wc_price($preco_com_desconto_boleto);
                $output .= '                
                <!-- Opção de Pagamento com Boleto -->
                <div class="opcao-pagamento" itemscope itemtype="http://schema.org/PaymentMethod">
                  <i class="fa-solid fa-barcode"></i>
                  <span class="preco" itemprop="price">' . $preco_formatado . '</span>
                  <span class="parcelas">à vista no <span itemprop="name">Boleto</span></span>
                  <div class="best-price__Badge-sc-1v0eo34-3 hWoKbG badge">
                  <svg viewBox="0 0 12 12" aria-labelledby="arrowDiscountIcon arrowDiscountDesc" width="10" height="10" fill="#fff" class="best-price__Arrow-sc-1v0eo34-5 chmPBI"><path fill="inherit" d="M.813 5.647a.5.5 0 01.707 0L5.5 9.628V1.166a.5.5 0 111 0v8.461l3.98-3.98a.5.5 0 01.637-.057l.07.058a.5.5 0 010 .707l-4.833 4.832a.508.508 0 01-.019.018l-.027.022a.379.379 0 01-.044.031l-.03.017a.363.363 0 01-.08.034.398.398 0 01-.08.018.45.45 0 01-.063.006H5.99a.503.503 0 01-.061-.005l.072.005a.502.502 0 01-.151-.023l-.023-.008-.015-.006a.496.496 0 01-.048-.022l-.015-.01-.01-.004a.498.498 0 01-.051-.037l-.017-.015a.232.232 0 01-.025-.022L.813 6.354a.5.5 0 010-.707z"></path></svg>' . (-$desconto_boleto) . '%</div>
                </div>';

            } else {
                $output .= "<p>Selecione uma opção de produto para ver o desconto no boleto.</p>";
            }
        } else {
            // Se o produto for simples, calcula o desconto
            $preco = floatval($product->get_regular_price());
            $desconto_boleto = floatval(get_option('desconto_boleto', 0));
            $preco_com_desconto_boleto = $preco * (1 - ($desconto_boleto / 100));
            $preco_formatado = wc_price($preco_com_desconto_boleto);
            $output .= '                
            <!-- Opção de Pagamento com Boleto -->
            <div class="opcao-pagamento" itemscope itemtype="http://schema.org/PaymentMethod">
              <i class="fa-solid fa-barcode"></i>
              <span class="preco" itemprop="price">' . $preco_formatado . '</span>
              <span class="parcelas">à vista no <span itemprop="name">Boleto</span></span>
              <div class="best-price__Badge-sc-1v0eo34-3 hWoKbG badge">
              <svg viewBox="0 0 12 12" aria-labelledby="arrowDiscountIcon arrowDiscountDesc" width="10" height="10" fill="#fff" class="best-price__Arrow-sc-1v0eo34-5 chmPBI"><path fill="inherit" d="M.813 5.647a.5.5 0 01.707 0L5.5 9.628V1.166a.5.5 0 111 0v8.461l3.98-3.98a.5.5 0 01.637-.057l.07.058a.5.5 0 010 .707l-4.833 4.832a.508.508 0 01-.019.018l-.027.022a.379.379 0 01-.044.031l-.03.017a.363.363 0 01-.08.034.398.398 0 01-.08.018.45.45 0 01-.063.006H5.99a.503.503 0 01-.061-.005l.072.005a.502.502 0 01-.151-.023l-.023-.008-.015-.006a.496.496 0 01-.048-.022l-.015-.01-.01-.004a.498.498 0 01-.051-.037l-.017-.015a.232.232 0 01-.025-.022L.813 6.354a.5.5 0 010-.707z"></path></svg>' . (-$desconto_boleto) . '%</div>
            </div>';
                }

        $output .= "</div>";

        // Retorna o HTML que será substituído pelo shortcode
        return $output;
    } else {
        // Se não estiver na página do produto ou o produto não for válido, retorna uma mensagem de erro
        return '<p>Desconto no boleto disponível apenas na página de produtos.</p>';
    }
}

add_shortcode('desconto_boleto', 'meu_plugin_desconto_boleto_shortcode');

function buscar_desconto_boleto() {
    if (!isset($_POST['preco'])) {
        wp_send_json_error('Preço não foi enviado.');
        wp_die();
    }

    $preco = floatval($_POST['preco']);
    $desconto_boleto = floatval(get_option('desconto_boleto', 0));
    $preco_com_desconto_boleto = $preco * (1 - ($desconto_boleto / 100));
    $preco_formatado = wc_price($preco_com_desconto_boleto);

    wp_send_json_success('<div class="opcao-pagamento" itemscope itemtype="http://schema.org/PaymentMethod">
    <i class="fa-solid fa-barcode"></i>
    <span class="preco" itemprop="price">' . $preco_formatado . '</span>
    <span class="parcelas">à vista no <span itemprop="name">Boleto</span></span>
    <div class="best-price__Badge-sc-1v0eo34-3 hWoKbG badge">
    <svg viewBox="0 0 12 12" aria-labelledby="arrowDiscountIcon arrowDiscountDesc" width="10" height="10" fill="#fff" class="best-price__Arrow-sc-1v0eo34-5 chmPBI"><path fill="inherit" d="M.813 5.647a.5.5 0 01.707 0L5.5 9.628V1.166a.5.5 0 111 0v8.461l3.98-3.98a.5.5 0 01.637-.057l.07.058a.5.5 0 010 .707l-4.833 4.832a.508.508 0 01-.019.018l-.027.022a.379.379 0 01-.044.031l-.03.017a.363.363 0 01-.08.034.398.398 0 01-.08.018.45.45 0 01-.063.006H5.99a.503.503 0 01-.061-.005l.072.005a.502.502 0 01-.151-.023l-.023-.008-.015-.006a.496.496 0 01-.048-.022l-.015-.01-.01-.004a.498.498 0 01-.051-.037l-.017-.015a.232.232 0 01-.025-.022L.813 6.354a.5.5 0 010-.707z"></path></svg>' . (-$desconto_boleto) . '%</div>
  </div>');
    wp_die();
}

add_action('wp_ajax_buscar_desconto_boleto', 'buscar_desconto_boleto');
add_action('wp_ajax_nopriv_buscar_desconto_boleto', 'buscar_desconto_boleto');








function meu_plugin_melhor_parcela_shortcode() {
    global $product;
    if (!$product || !($product instanceof WC_Product)) {
        return '<div id="melhor-parcela-container">Produto não encontrado.</div>';
    }

    $preco = floatval($product->get_price());
    $melhor_parcela_sem_juros = 0;
    $valor_melhor_parcela = PHP_FLOAT_MAX; // Inicializa com o maior valor possível para comparação

    for ($i = 1; $i <= 12; $i++) {
        $juros = floatval(get_option("parcelamento_juros_$i", 0));
        // Verifica se a parcela atual é sem juros e se o valor da parcela é menor que o valor da melhor parcela encontrada até agora
        if ($juros == 0 && ($preco / $i < $valor_melhor_parcela || $melhor_parcela_sem_juros == 0)) {
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
        <span class="parcelas">sem juros no <span itemprop="name">Cartão</span></span>
    </div>
    

    
        </div>
        
        
 
        
        ';


    }

    return '<div id="melhor-parcela-container"></div>'; // Retorna um contêiner vazio se não houver parcelas sem juros
}
add_shortcode('melhor_parcelamento', 'meu_plugin_melhor_parcela_shortcode');




function buscar_melhor_parcela_sem_juros() {
    if (!isset($_POST['preco'])) {
        wp_send_json_error('Preço não foi enviado.');
        wp_die();
    }

    $preco = floatval($_POST['preco']);
    $melhor_parcela_sem_juros = 0;
    $valor_melhor_parcela = 0;

    for ($i = 1; $i <= 12; $i++) {
        $juros = floatval(get_option("parcelamento_juros_$i", 0));
        if ($juros == 0 && ($preco / $i < $valor_melhor_parcela || $melhor_parcela_sem_juros == 0)) {
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
            <span class="parcelas">sem juros no <span itemprop="name">Cartão</span></span>
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
    if (!$product || !($product instanceof WC_Product)) {
        return '<div id="tabela-parcelamento-container">Produto não encontrado.</div>';
    }

    // Verifica se o produto é variável e obtém o preço padrão da variação
    $preco = $product->is_type('variable') ? $product->get_variation_price('min', true) : $product->get_price();
    $preco = floatval($preco);
    $output = "<div id='tabela-parcelamento-container'><table class='tabela-parcelamento'><tbody>";
    // ... seu código para gerar a tabela ...
    for ($i = 1; $i <= 12; $i++) {
        $juros = floatval(get_option("parcelamento_juros_$i", 0));
        if ($juros == 0) {
            $valor_parcela = $preco / $i;
        } else {
            $valor_total_com_juros = $preco * (1 + ($juros / 100));
            $valor_parcela = $valor_total_com_juros / $i;
        }
        $output .= "<tr><td>{$i}x de " . wc_price($valor_parcela) . ($juros == 0 ? " sem juros" : " com juros de {$juros}%") . "</td></tr>";
    }
    $output .= "</tbody></table></div>"; // Fechamos o div do contêiner aqui
    return $output;
}
add_shortcode('tabela_parcelamento', 'meu_plugin_tabela_parcelamento_shortcode');







function buscar_tabela_parcelamento() {
    if (!isset($_POST['preco'])) {
        wp_send_json_error('Preço não foi enviado.');
        wp_die();
    }

    $preco = floatval($_POST['preco']);
    $output = "<table class='tabela-parcelamento'><tbody>";

    for ($i = 1; $i <= 12; $i++) {
        $juros = floatval(get_option("parcelamento_juros_$i", 0));
        if ($juros == 0) {
            $valor_parcela = $preco / $i;
        } else {
            $valor_total_com_juros = $preco * (1 + ($juros / 100));
            $valor_parcela = $valor_total_com_juros / $i;
        }
        $output .= "<tr><td>{$i}x de " . wc_price($valor_parcela) . ($juros == 0 ? " sem juros" : " com juros de {$juros}%") . "</td></tr>";
    }

    $output .= "</tbody></table>";

    wp_send_json_success($output);
    wp_die();
}
add_action('wp_ajax_buscar_tabela_parcelamento', 'buscar_tabela_parcelamento');
add_action('wp_ajax_nopriv_buscar_tabela_parcelamento', 'buscar_tabela_parcelamento');










function meu_plugin_de_parcelamento_enqueue_scripts() {
    if (is_product()) {
        wp_enqueue_script('meu-plugin-de-parcelamento-js', plugin_dir_url(__FILE__) . 'js/parcelamento.js', array('jquery'), null, true);

        wp_localize_script('meu-plugin-de-parcelamento-js', 'meuPluginDeParcelamento', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('buscar_desconto_pix_nonce') // Cria um nonce para segurança
        ));
    }
}
add_action('wp_enqueue_scripts', 'meu_plugin_de_parcelamento_enqueue_scripts');



function add_custom_style() {
    // Usa get_template_directory_uri() se estiver usando um tema pai,
    // ou get_stylesheet_directory_uri() se estiver usando um tema filho.
    wp_enqueue_style(
        'forma-de-pagamentos', // Este é um 'handle' para o seu estilo
        plugin_dir_url(__FILE__) . 'css/formadepagamentos.css', // Caminho para o seu arquivo css
        array(), // Dependências, deixe vazio se não houver nenhuma
        '1.0.0' // Versão do seu arquivo css para controle de cache
    );
}

add_action( 'wp_enqueue_scripts', 'add_custom_style' );


function meu_tema_enqueue_scripts() {
    // Verifica se o FontAwesome ainda não foi carregado
    if (!wp_script_is('fontawesome', 'enqueued')) {
        // Registra o FontAwesome
        wp_register_script('fontawesome', 'https://kit.fontawesome.com/b6e807a75d.js', array(), null, true);
        
        // Enfileira o script FontAwesome
        wp_enqueue_script('fontawesome');
    }
}

add_action('wp_enqueue_scripts', 'meu_tema_enqueue_scripts');


