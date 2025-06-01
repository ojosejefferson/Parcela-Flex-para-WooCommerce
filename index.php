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

// Mostra o valor total com desconto Pix
function parcelas_flex_show_pix_total() {
    $cart = WC()->cart;
    if ( ! $cart ) return;

    // Subtotal REAL dos produtos (sem cupom)
    $subtotal = (float) $cart->get_subtotal();

    // Cupom aplicado (se existir)
    $desconto_cupom = (float) $cart->get_discount_total();

    // Frete
    $frete = (float) $cart->get_shipping_total();

    // Desconto do Pix (10% sobre subtotal)
    $desconto_pix = $subtotal * 0.10;

    // Total final simulado: subtotal - cupom - desconto Pix + frete
    $total_pix = $subtotal - $desconto_cupom - $desconto_pix + $frete;

    echo '<tr class="parcelas-flex-pix-total">';
    echo '<th style="color: #00a650; font-weight: 600;">Total no Pix </th>';
    echo '<td style="color: #00a650; font-weight: 600;">' . wc_price($total_pix) . '</td>';
    echo '</tr>';



    
}


// Adiciona nos locais corretos
add_action('woocommerce_cart_totals_after_order_total', 'parcelas_flex_show_pix_total');
add_action('woocommerce_review_order_after_order_total', 'parcelas_flex_show_pix_total');

// Atualiza via AJAX
function parcelas_flex_update_pix_total($fragments) {
    ob_start();
    parcelas_flex_show_pix_total();
    $fragments['.parcelas-flex-pix-total'] = ob_get_clean();
    return $fragments;
}
add_filter('woocommerce_add_to_cart_fragments', 'parcelas_flex_update_pix_total');
add_filter('woocommerce_update_order_review_fragments', 'parcelas_flex_update_pix_total');



