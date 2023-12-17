<?php

/**
 * Plugin Name: Parcelas Flex For WooCommerce
 * Description: Um plugin para adicionar opções de parcelamento no WooCommerce com descontos para Pix e Boleto. Compatível com produtos variáveis, ele proporciona flexibilidade de pagamento e otimiza a conversão de vendas.
 * Version: 1.0
 * Author: José Jefferson
 * Author URI: https://www.linkedin.com/in/ojosejefferson/
 * License: GPL v2 ou posterior
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Domain Path: /languages
 * WC requires at least: 5x
 * WC tested up to: 8.3
 * 
 * Tags: Dividi, mostrar parcelas, parcelamento, pagamento à vista, WooCommerce, Pix, boleto bancário, cartão de crédito, descontos, produtos variáveis, e-commerce, Brasil
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!function_exists('get_plugins')) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
}


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
function meu_plugin_de_parcelamento_register_settings()
{
    register_setting('meu-plugin-de-parcelamento-settings-group', 'desconto_pix');
    register_setting('meu-plugin-de-parcelamento-settings-group', 'desconto_boleto');
    register_setting('meu-plugin-de-parcelamento-settings-group', 'exibir_juros_porcentagem');
    register_setting('meu-plugin-de-parcelamento-settings-group', 'valor_minimo_parcela');
    


    for ($i = 1; $i <= 12; $i++) {
        register_setting('meu-plugin-de-parcelamento-settings-group', "parcelamento_juros_$i");
    }
}
add_action('admin_init', 'meu_plugin_de_parcelamento_register_settings');

function meu_plugin_registrar_opcoes() {
    // Registra uma nova configuração para armazenar a preferência de texto 'à vista'
    register_setting('meu-plugin-opcoes-pagamento', 'meu_plugin_texto_a_vista');

    // Registra uma nova configuração para armazenar a preferência de texto 'no Pix'
    register_setting('meu-plugin-opcoes-pagamento', 'meu_plugin_texto_no_pix');
}
add_action('admin_init', 'meu_plugin_registrar_opcoes');


// Adiciona a página de configurações ao menu do WooCommerce
function meu_plugin_de_parcelamento_add_admin_menu()
{
    add_submenu_page(
        'woocommerce',
        'Configurações de Parcelamento',
        'Parcelas Flex',
        'manage_options',
        'meu-plugin-de-parcelamento',
        'meu_plugin_de_parcelamento_settings_page'
    );
}
add_action('admin_menu', 'meu_plugin_de_parcelamento_add_admin_menu');



// Inclui a lógica do plugin
require_once plugin_dir_path(__FILE__) . 'includes/shortcode.php';
require_once plugin_dir_path(__FILE__) . 'includes/config.php';
require_once plugin_dir_path(__FILE__) . 'includes/styles.php';




function meu_plugin_de_parcelamento_enqueue_scripts()
{
    if (is_product()) {
        wp_enqueue_script('meu-plugin-de-parcelamento-js', plugin_dir_url(__FILE__) . 'js/parcelamento.js', array('jquery'), null, true);

        wp_localize_script('meu-plugin-de-parcelamento-js', 'meuPluginDeParcelamento', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('buscar_desconto_pix_nonce') // Cria um nonce para segurança
        ));
    }
}
add_action('wp_enqueue_scripts', 'meu_plugin_de_parcelamento_enqueue_scripts');



function add_custom_style()
{
    // Usa get_template_directory_uri() se estiver usando um tema pai,
    // ou get_stylesheet_directory_uri() se estiver usando um tema filho.
    wp_enqueue_style(
        'forma-de-pagamentos', // Este é um 'handle' para o seu estilo
        plugin_dir_url(__FILE__) . 'css/formadepagamentos.css', // Caminho para o seu arquivo css
        array(), // Dependências, deixe vazio se não houver nenhuma
        '1.0.0' // Versão do seu arquivo css para controle de cache
    );
}

add_action('wp_enqueue_scripts', 'add_custom_style');

function exibir_mensagem_doacao() {
    ?>
    <div class="notice notice-success settings-error is-dismissible">
        <h2>Faça uma Doação ❤️</h2>
        <p>Se este plugin foi útil para você de alguma forma, considere fazer uma doação para apoiar e incentivar ainda mais o desenvolvimento de novos recursos e melhorias. Sua contribuição é valiosa e ajuda a manter este plugin gratuito e em constante aprimoramento.</p>
        <p>Faça sua doação agora e faça parte da comunidade que apoia o desenvolvimento de ferramentas úteis e inovadoras para todos os usuários.</p>
        <strong>Chave Pix</strong>
        <code><pre>71ef8487-f222-4573-976b-0d702ba9fecf</pre></code>
        <a href="https://link.mercadopago.com.br/azuritestore " target="_blank" class="button button-primary">Mercado Pago</a>

    </div>
    <?php
}

add_action('admin_notices', 'exibir_mensagem_doacao');



function meu_tema_enqueue_scripts()
{
    // Verifica se o FontAwesome ainda não foi carregado
    if (!wp_script_is('fontawesome', 'enqueued')) {
        // Registra o FontAwesome
        wp_register_script('fontawesome', 'https://kit.fontawesome.com/b6e807a75d.js', array(), null, true);

        // Enfileira o script FontAwesome
        wp_enqueue_script('fontawesome');
    }
}

add_action('wp_enqueue_scripts', 'meu_tema_enqueue_scripts');


// Registra e enfileira os estilos e scripts do Bootstrap para a página de administração
function meu_plugin_enqueue_bootstrap($hook) {
    // Verifica se estamos na página de configurações do plugin
  
    // Define o caminho base do plugin
    $plugin_url = plugin_dir_url(__FILE__);

    // Enfileira o CSS do Bootstrap
    wp_enqueue_style('bootstrap-css', $plugin_url . 'css/bootstrap.css');

    // Enfileira o JS do Bootstrap
    wp_enqueue_script('bootstrap-js', $plugin_url . 'js/bootstrap.js', array('jquery'), null, true);
}

add_action('admin_enqueue_scripts', 'meu_plugin_enqueue_bootstrap');

