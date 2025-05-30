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

// Adiciona informações de desconto e parcelamento na página do carrinho
function parcelas_flex_show_cart_discount_info() {
    $cart = WC()->cart;
    if (!$cart) {
        return;
    }

    $total = $cart->get_total('edit');
    $desconto_pix = get_option('desconto_pix', 0);
    $valor_pix = $total - ($total * ($desconto_pix / 100));
    $valor_pix_formatado = wc_price($valor_pix);

    echo '<div class="parcelas-flex-cart-info" style="color: #00a650; font-weight: 600;">';
    echo '<p>Total à vista no Pix: ' . $valor_pix_formatado . '</p>';
    echo '<p>Parcelamento disponível em até 12x.</p>';
    echo '</div>';
}

// Adiciona o valor do Pix na resposta do update_cart
add_filter('woocommerce_add_to_cart_fragments', 'parcelas_flex_add_pix_to_cart_fragments');
function parcelas_flex_add_pix_to_cart_fragments($fragments) {
    $cart = WC()->cart;
    if (!$cart) {
        return $fragments;
    }

    $total = $cart->get_total('edit');
    $desconto_pix = get_option('desconto_pix', 0);
    $valor_pix = $total - ($total * ($desconto_pix / 100));
    $valor_pix_formatado = wc_price($valor_pix);

    $fragments['.parcelas-flex-cart-info p:first'] = '<p style="color: #00a650; font-weight: 600;">Total à vista no Pix: ' . $valor_pix_formatado . '</p>';
    
    return $fragments;
}

// Adiciona o script para atualização dinâmica no checkout
function parcelas_flex_enqueue_checkout_scripts() {
    if (is_checkout()) {
        wp_enqueue_script('parcelas-flex-checkout', plugin_dir_url(__FILE__) . 'assets/js/checkout.js', array('jquery'), null, true);
        wp_localize_script('parcelas-flex-checkout', 'parcelasFlexCheckout', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('parcelas_flex_checkout_nonce')
        ));
    }
}
add_action('wp_enqueue_scripts', 'parcelas_flex_enqueue_checkout_scripts');

// Função para atualizar o valor do Pix via AJAX
function parcelas_flex_update_pix_value() {
    check_ajax_referer('parcelas_flex_checkout_nonce', 'nonce');
    
    $cart = WC()->cart;
    if (!$cart) {
        wp_send_json_error('Carrinho não encontrado');
        wp_die();
    }

    $total_produtos = (float) $cart->get_cart_contents_total();
    $frete = (float) $cart->get_shipping_total();
    $desconto_pix = floatval(get_option('desconto_pix', 0));
    
    // Calcula o desconto apenas sobre o valor dos produtos
    $preco_com_desconto_pix = $total_produtos * (1 - ($desconto_pix / 100));
    
    // Adiciona o frete ao valor com desconto
    $preco_final = $preco_com_desconto_pix + $frete;
    
    wp_send_json_success(array(
        'cart_total' => $total_produtos,
        'shipping_total' => $frete,
        'desconto_pix' => $desconto_pix,
        'pix_value' => wc_price($preco_final)
    ));
}
add_action('wp_ajax_parcelas_flex_update_pix_value', 'parcelas_flex_update_pix_value');
add_action('wp_ajax_nopriv_parcelas_flex_update_pix_value', 'parcelas_flex_update_pix_value');

// Hooks para exibir o valor do Pix
add_action('woocommerce_proceed_to_checkout', 'parcelas_flex_show_cart_discount_info');
add_action('woocommerce_review_order_before_payment', 'parcelas_flex_show_cart_discount_info');

// Adiciona o valor do Pix na resposta do update_order_review
add_filter('woocommerce_update_order_review_fragments', 'parcelas_flex_add_pix_to_fragments');
function parcelas_flex_add_pix_to_fragments($fragments) {
    $cart = WC()->cart;
    if (!$cart) {
        return $fragments;
    }

    $total = $cart->get_total('edit');
    $desconto_pix = get_option('desconto_pix', 0);
    $valor_pix = $total - ($total * ($desconto_pix / 100));
    $valor_pix_formatado = wc_price($valor_pix);

    $fragments['.parcelas-flex-cart-info p:first'] = '<p style="color: #00a650; font-weight: 600;">Total à vista no Pix: ' . $valor_pix_formatado . '</p>';
    
    return $fragments;
}

// Adiciona o script para atualização dinâmica no carrinho
function parcelas_flex_enqueue_cart_scripts() {
    if (is_cart()) {
        wp_enqueue_script('parcelas-flex-cart', plugin_dir_url(__FILE__) . 'assets/js/cart.js', array('jquery'), null, true);
    }
}
add_action('wp_enqueue_scripts', 'parcelas_flex_enqueue_cart_scripts');



