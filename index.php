<?php
/**
 * Plugin Name: Parcelas Flex For WooCommerce
 * Description: Um plugin para adicionar opções de parcelamento no WooCommerce com descontos para Pix e Boleto. Compatível com produtos variáveis, ele proporciona flexibilidade de pagamento e otimiza a conversão de vendas.
 * Version: 2
 * Author: José Jefferson
 * Author URI: https://www.linkedin.com/in/ojosejefferson/
 * License: GPL v2 ou posterior
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Domain Path: /languages
 * WC requires at least: 5x
 * WC tested up to: 8.3
 * Tags: Dividi, mostrar parcelas, parcelamento, pagamento à vista, WooCommerce, Pix, boleto bancário, cartão de crédito, descontos, produtos variáveis, e-commerce, Brasil
 */

// Definição da constante ABSPATH
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/');
}

// Importações necessárias
require_once ABSPATH . 'wp-admin/includes/plugin.php';
require_once ABSPATH . 'wp-includes/pluggable.php';
require_once ABSPATH . 'wp-includes/plugin.php';
require_once ABSPATH . 'wp-includes/functions.php';
require_once ABSPATH . 'wp-includes/option.php';
require_once ABSPATH . 'wp-includes/pluggable.php';
require_once ABSPATH . 'wp-includes/pluggable.php';
require_once ABSPATH . 'wp-includes/pluggable.php';

add_action('woocommerce_loaded', function () {
    if (class_exists('Automattic\WooCommerce\Utilities\FeaturesUtil') && method_exists('Automattic\WooCommerce\Utilities\FeaturesUtil', 'declare_compatibility')) {
        Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', plugin_basename(__FILE__), true);
    }
});

add_action('plugins_loaded', function () {
    if (class_exists('Automattic\WooCommerce\Utilities\FeaturesUtil') && method_exists('Automattic\WooCommerce\Utilities\FeaturesUtil', 'declare_compatibility')) {
        Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', plugin_basename(__FILE__), true);
    }
}, 0); // Prioridade 0 para que seja executado o mais cedo possível.

add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );

// Registra as configurações
function parcelas_flex_parcelamento_register_settings()
{
    register_setting('parcelas-flex-parcelamento-settings-group', 'desconto_pix');
    register_setting('parcelas-flex-parcelamento-settings-group', 'desconto_boleto');
    register_setting('parcelas-flex-parcelamento-settings-group', 'exibir_juros_porcentagem');
    register_setting('parcelas-flex-parcelamento-settings-group', 'valor_minimo_parcela');
    register_setting('parcelas-flex-parcelamento-settings-group', 'desconto_gateway');
    register_setting('parcelas-flex-parcelamento-settings-group', 'taxa_juros_personalizada');
    
    for ($i = 1; $i <= 12; $i++) {
        register_setting('parcelas-flex-parcelamento-settings-group', "parcelamento_juros_$i");
    }
}
add_action('admin_init', 'parcelas_flex_parcelamento_register_settings');

function parcelas_flex_registrar_opcoes() {
    // Registra uma nova configuração para armazenar a preferência de texto 'à vista'
    register_setting('parcelas_flex-opcoes-pagamento', 'parcelas_flex_texto_a_vista');

    // Registra uma nova configuração para armazenar a preferência de texto 'no Pix'
    register_setting('parcelas_flex-opcoes-pagamento', 'parcelas_flex_texto_no_pix');
}
add_action('admin_init', 'parcelas_flex_registrar_opcoes');

// Adiciona a página de configurações ao menu do WooCommerce
function parcelas_flex_parcelamento_add_admin_menu()
{
    add_submenu_page(
        'woocommerce',
        'Configurações de Parcelamento',
        'Parcelas Flex',
        'manage_options',
        'parcelas-flex-parcelamento',
        'parcelas_flex_parcelamento_settings_page'
    );
}
add_action('admin_menu', 'parcelas_flex_parcelamento_add_admin_menu');

// Inclui a lógica do plugin
require_once plugin_dir_path(__FILE__) . 'includes/config.php';
require_once plugin_dir_path(__FILE__) . 'includes/shortcodes/desconto-pix.php';
require_once plugin_dir_path(__FILE__) . 'includes/shortcodes/desconto-boleto.php';
require_once plugin_dir_path(__FILE__) . 'includes/shortcodes/tabela-parcelas.php';
require_once plugin_dir_path(__FILE__) . 'includes/shortcodes/economize.php';
require_once plugin_dir_path(__FILE__) . 'includes/shortcodes/melhor-parcela.php';

function parcelas_flex_parcelamento_enqueue_scripts()
{
    if (is_product()) {
        wp_enqueue_script('parcelas-flex-parcelamento-js', plugin_dir_url(__FILE__) . 'assets/js/parcelamento.js', array('jquery'), null, true);

        wp_localize_script('parcelas-flex-parcelamento-js', 'parcelaFlexDeParcelamento', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('buscar_desconto_pix_nonce') // Cria um nonce para segurança
        ));
    }
}
add_action('wp_enqueue_scripts', 'parcelas_flex_parcelamento_enqueue_scripts');

function add_custom_style()
{
    // Usa get_template_directory_uri() se estiver usando um tema pai,
    // ou get_stylesheet_directory_uri() se estiver usando um tema filho.
    wp_enqueue_style(
        'forma-de-pagamentos', // Este é um 'handle' para o seu estilo
        plugin_dir_url(__FILE__) . 'assets/css/formadepagamentos.css', // Caminho para o seu arquivo css
        array(), // Dependências, deixe vazio se não houver nenhuma
        '1.0.0' // Versão do seu arquivo css para controle de cache
    );
}
add_action('wp_enqueue_scripts', 'add_custom_style');

// Adiciona campo de desconto personalizado no painel de produto
function parcelas_flex_add_custom_discount_field() {
    global $post;
    $desconto_personalizado = get_post_meta($post->ID, '_desconto_personalizado', true);
    echo '<div class="options_group">';
    woocommerce_wp_text_input(
        array(
            'id' => '_desconto_personalizado',
            'label' => 'Desconto Personalizado (%)',
            'description' => 'Desconto específico para este produto no Pix',
            'value' => $desconto_personalizado,
            'type' => 'number',
            'custom_attributes' => array(
                'step' => '0.01',
                'min' => '0',
                'max' => '100'
            )
        )
    );
    echo '</div>';
}
add_action('woocommerce_product_options_general_product_data', 'parcelas_flex_add_custom_discount_field');

// Salva o valor do desconto personalizado
function parcelas_flex_save_custom_discount_field($post_id) {
    $desconto_personalizado = isset($_POST['_desconto_personalizado']) ? sanitize_text_field($_POST['_desconto_personalizado']) : '';
    update_post_meta($post_id, '_desconto_personalizado', $desconto_personalizado);
}
add_action('woocommerce_process_product_meta', 'parcelas_flex_save_custom_discount_field');

// Adiciona botão de compartilhamento para o WhatsApp
function parcelas_flex_add_whatsapp_share_button() {
    echo '<button id="share-whatsapp" class="button">Compartilhar no WhatsApp</button>';
}
add_action('woocommerce_single_product_summary', 'parcelas_flex_add_whatsapp_share_button', 20);

// Função para exibir o valor do Pix
function parcelas_flex_show_cart_discount_info() {
    $cart = WC()->cart;
    if (!$cart) return;

    $subtotal = (float) $cart->get_subtotal(); // total dos produtos
    $desconto_cupom = (float) $cart->get_discount_total();
    $frete = (float) $cart->get_shipping_total();
    $desconto_pix = floatval(get_option('desconto_pix', 0));

    $total_produtos = $subtotal - $desconto_cupom;

    $valor_desconto_pix = $total_produtos * ($desconto_pix / 100);
    $total_pix = $total_produtos - $valor_desconto_pix + $frete;

    echo '<tr class="parcelas-flex-cart-info">';
    echo '<th style="color: #00a650; font-weight: 600;">Total à vista no Pix:</th>';
    echo '<td style="color: #00a650; font-weight: 600;">' . wc_price($total_pix) . '</td>';
    echo '</tr>';
}


// Remove os hooks antigos
remove_action('woocommerce_proceed_to_checkout', 'parcelas_flex_show_cart_discount_info');
remove_action('woocommerce_review_order_before_payment', 'parcelas_flex_show_cart_discount_info');

// Adiciona os novos hooks
add_action('woocommerce_cart_totals_after_order_total', 'parcelas_flex_show_cart_discount_info');
add_action('woocommerce_review_order_after_order_total', 'parcelas_flex_show_cart_discount_info');

// Atualiza a função de fragments para usar o novo formato
function parcelas_flex_add_pix_to_cart_fragments($fragments) {
    $cart = WC()->cart;
    if (!$cart) {
        return $fragments;
    }

    // Obtém o subtotal dos produtos
    $subtotal = (float) $cart->get_subtotal();
    
    // Obtém o valor do frete
    $frete = (float) $cart->get_shipping_total();
    
    // Obtém o valor do desconto do cupom
    $desconto_cupom = (float) $cart->get_discount_total();
    
    // Obtém a porcentagem de desconto do Pix do plugin
    $desconto_pix = floatval(get_option('desconto_pix', 0));
    
    // Calcula o valor total base (subtotal + frete - cupom)
    $valor_base = $subtotal + $frete - $desconto_cupom;
    
    // Aplica o desconto do Pix
    $desconto_valor = $valor_base * ($desconto_pix / 100);
    $preco_final = $valor_base - $desconto_valor;

    $fragments['.parcelas-flex-cart-info'] = '<tr class="parcelas-flex-cart-info"><th style="color: #00a650; font-weight: 600;">Total à vista no Pix:</th><td style="color: #00a650; font-weight: 600;">' . wc_price($preco_final) . '</td></tr>';
    
    return $fragments;
}

// Atualiza a função de fragments do checkout
function parcelas_flex_add_pix_to_fragments($fragments) {
    $cart = WC()->cart;
    if (!$cart) {
        return $fragments;
    }

    // Obtém o valor total dos produtos (sem frete)
    $total_produtos = (float) $cart->get_cart_contents_total();
    
    // Obtém o valor do frete
    $frete = (float) $cart->get_shipping_total();
    
    // Obtém a porcentagem de desconto do Pix
    $desconto_pix = floatval(get_option('desconto_pix', 0));
    
    // Obtém o valor total do carrinho (incluindo descontos do gateway e cupom)
    $total_carrinho = (float) $cart->get_total('edit');
    
    // Verifica se o método de pagamento é Pix
    $is_pix = isset($_POST['payment_method']) && $_POST['payment_method'] === 'pix';
    
    // Se o método de pagamento for Pix, usa o valor total do carrinho
    if ($is_pix) {
        $preco_final = $total_carrinho;
    } else {
        // Se não for Pix, calcula o valor com desconto do Pix
        if ($desconto_pix > 0) {
            // Calcula o desconto apenas sobre o valor dos produtos (já considerando o cupom)
            $desconto_valor = $total_produtos * ($desconto_pix / 100);
            $preco_com_desconto_pix = $total_produtos - $desconto_valor;
            
            // Adiciona o frete ao valor com desconto
            $preco_final = $preco_com_desconto_pix + $frete;
        } else {
            // Se não houver desconto do Pix, usa o valor total do carrinho
            $preco_final = $total_carrinho;
        }
    }

    $fragments['.parcelas-flex-cart-info'] = '<tr class="parcelas-flex-cart-info"><th style="color: #00a650; font-weight: 600;">Total à vista no Pix:</th><td style="color: #00a650; font-weight: 600;">' . wc_price($preco_final) . '</td></tr>';
    
    return $fragments;
}

// Adiciona o script para atualização dinâmica no carrinho
function parcelas_flex_enqueue_cart_scripts() {
    if (is_cart()) {
        wp_enqueue_script('parcelas-flex-cart', plugin_dir_url(__FILE__) . 'assets/js/cart.js', array('jquery'), null, true);
    }
}
add_action('wp_enqueue_scripts', 'parcelas_flex_enqueue_cart_scripts');

// Função para garantir que o preço no Google Merchant Center seja o preço original
function parcelas_flex_ensure_original_price_for_google($price, $product) {
    // Retorna o preço original do produto, sem considerar descontos
    return $product->get_regular_price();
}
add_filter('woocommerce_gpf_feed_item_price', 'parcelas_flex_ensure_original_price_for_google', 10, 2);

// Função para garantir que o preço de venda no Google Merchant Center seja o preço original
function parcelas_flex_ensure_original_sale_price_for_google($price, $product) {
    // Retorna o preço original do produto, sem considerar descontos
    return $product->get_regular_price();
}
add_filter('woocommerce_gpf_feed_item_sale_price', 'parcelas_flex_ensure_original_sale_price_for_google', 10, 2);



