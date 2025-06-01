<?php
// Cria a página de configurações
function parcelas_flex_parcelamento_settings_page()
{
    // Verifica qual aba está ativa, padrão para 'configuracao'
    $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'configuracao';
?>

<div class="wrap woocommerce">
    <h1 class="wp-heading-inline">Configurações de Parcelamento</h1>
    <hr class="wp-header-end">

    <div class="notice notice-info inline">
        <p>Configure as opções de parcelamento e descontos para seu e-commerce.</p>
    </div>

    <form method="post" action="options.php">
        <?php settings_fields('parcelas-flex-parcelamento-settings-group'); ?>
        <?php do_settings_sections('parcelas-flex-parcelamento-settings-group'); ?>

        <div class="wc-settings-panel">
            <div class="wc-settings-panel__header">
                <h2>Configurações de Parcelamento</h2>
            </div>
            <div class="wc-settings-panel__body">
                <div class="wc-settings-panel__content">
                    <div class="wc-settings-panel__row">
                        <div class="wc-settings-panel__col">
                            <div class="wc-settings-panel__field">
                                <h3>Taxa de Juros por Parcela</h3>
                                <p class="description">Digite 0 para parcelamento sem juros ou deixe em branco para não mostrar a parcela.</p>
                                
                                <div class="wc-settings-panel__fields-grid">
                                    <?php for ($i = 1; $i <= 12; $i++) : ?>
                                        <div class="wc-settings-panel__field">
                                            <label for="parcelamento_juros_<?php echo $i; ?>">
                                                Taxa de Juros para <?php echo $i; ?>x (%)
                                            </label>
                                            <input type="number" 
                                                   class="regular-text" 
                                                   id="parcelamento_juros_<?php echo $i; ?>" 
                                                   name="parcelamento_juros_<?php echo $i; ?>" 
                                                   value="<?php echo esc_attr(get_option("parcelamento_juros_$i", 0)); ?>" 
                                                   step="0.01" 
                                                   min="0" 
                                                   max="100">
                                        </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>

                        <div class="wc-settings-panel__col">
                            <div class="wc-settings-panel__field">
                                <h3>Descontos e Valores Mínimos</h3>
                                
                                <div class="wc-settings-panel__field">
                                    <label for="desconto_pix">Desconto Pix (%)</label>
                                    <input type="number" 
                                           class="regular-text" 
                                           id="desconto_pix" 
                                           name="desconto_pix" 
                                           value="<?php echo esc_attr(get_option('desconto_pix')); ?>" 
                                           step="0.01" 
                                           min="0" 
                                           max="100">
                                </div>

                                <div class="wc-settings-panel__field">
                                    <label for="desconto_boleto">Desconto Boleto (%)</label>
                                    <input type="number" 
                                           class="regular-text" 
                                           id="desconto_boleto" 
                                           name="desconto_boleto" 
                                           value="<?php echo esc_attr(get_option('desconto_boleto')); ?>" 
                                           step="0.01" 
                                           min="0" 
                                           max="100">
                                </div>

                                <div class="wc-settings-panel__field">
                                    <label for="valor_minimo_parcela">Valor mínimo da parcela (R$)</label>
                                    <input type="number" 
                                           class="regular-text" 
                                           id="valor_minimo_parcela" 
                                           name="valor_minimo_parcela" 
                                           value="<?php echo esc_attr(get_option('valor_minimo_parcela')); ?>" 
                                           step="0.01" 
                                           min="0">
                                </div>

                                <div class="wc-settings-panel__field">
                                    <label class="wc-settings-panel__checkbox">
                                        <input type="checkbox" 
                                               id="exibir_juros_porcentagem" 
                                               name="exibir_juros_porcentagem" 
                                               value="1" 
                                               <?php checked(1, get_option('exibir_juros_porcentagem', 0)); ?>>
                                        Exibir a porcentagem de juros no frontend
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="wc-settings-panel__row">
                        <div class="wc-settings-panel__col">
                            <div class="wc-settings-panel__field">
                                <h3>Textos Personalizados</h3>
                                
                                <?php
                                settings_fields('parcelas_flex-opcoes-pagamento');
                                do_settings_sections('parcelas_flex-opcoes-pagamento');
                                ?>

                                <div class="wc-settings-panel__fields-grid">
                                    <div class="wc-settings-panel__field">
                                        <label for="parcelas_flex_texto_a_vista">Prefixo 'Pix'</label>
                                        <input type="text" 
                                               class="regular-text" 
                                               id="parcelas_flex_texto_a_vista" 
                                               name="parcelas_flex_texto_a_vista" 
                                               value="<?php echo esc_attr(get_option('parcelas_flex_texto_a_vista', 'à vista')); ?>">
                                    </div>

                                    <div class="wc-settings-panel__field">
                                        <label for="parcelas_flex_texto_no_pix">Sufixo 'Pix'</label>
                                        <input type="text" 
                                               class="regular-text" 
                                               id="parcelas_flex_texto_no_pix" 
                                               name="parcelas_flex_texto_no_pix" 
                                               value="<?php echo esc_attr(get_option('parcelas_flex_texto_no_pix', 'no Pix')); ?>">
                                    </div>

                                    <div class="wc-settings-panel__field">
                                        <label for="parcelas_flex_texto_a_boleto">Prefixo 'à vista'</label>
                                        <input type="text" 
                                               class="regular-text" 
                                               id="parcelas_flex_texto_a_boleto" 
                                               name="parcelas_flex_texto_a_boleto" 
                                               value="<?php echo esc_attr(get_option('parcelas_flex_texto_a_boleto', 'à vista Boleto')); ?>">
                                    </div>

                                    <div class="wc-settings-panel__field">
                                        <label for="parcelas_flex_texto_no_boleto">Prefixo 'no Boleto'</label>
                                        <input type="text" 
                                               class="regular-text" 
                                               id="parcelas_flex_texto_no_boleto" 
                                               name="parcelas_flex_texto_no_boleto" 
                                               value="<?php echo esc_attr(get_option('parcelas_flex_texto_no_boleto', 'no Boleto')); ?>">
                                    </div>

                                    <div class="wc-settings-panel__field">
                                        <label for="parcelas_flex_texto_economize">Prefixo 'Economize no Pix'</label>
                                        <input type="text" 
                                               class="regular-text" 
                                               id="parcelas_flex_texto_economize" 
                                               name="parcelas_flex_texto_economize" 
                                               value="<?php echo esc_attr(get_option('parcelas_flex_texto_economize', 'Economize no Pix')); ?>">
                                    </div>

                                    <div class="wc-settings-panel__field">
                                        <label for="parcelas_flex_texto_melhor_parcela">Prefixo 'sem juros'</label>
                                        <input type="text" 
                                               class="regular-text" 
                                               id="parcelas_flex_texto_melhor_parcela" 
                                               name="parcelas_flex_texto_melhor_parcela" 
                                               value="<?php echo esc_attr(get_option('parcelas_flex_texto_melhor_parcela', 'sem juros')); ?>">
                                    </div>

                                    <div class="wc-settings-panel__field">
                                        <label for="parcelas_flex_texto_melhor_parcelas_cjuros">Prefixo 'com juros'</label>
                                        <input type="text" 
                                               class="regular-text" 
                                               id="parcelas_flex_texto_melhor_parcelas_cjuros" 
                                               name="parcelas_flex_texto_melhor_parcelas_cjuros" 
                                               value="<?php echo esc_attr(get_option('parcelas_flex_texto_melhor_parcelas_cjuros', 'com juros')); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="wc-settings-panel__footer">
                <button type="submit" class="button button-primary">Salvar Alterações</button>
            </div>
        </div>
    </form>

    <div class="wc-settings-panel">
        <div class="wc-settings-panel__header">
            <h2>Informações de Shortcode</h2>
        </div>
        <div class="wc-settings-panel__body">
            <div class="wc-settings-panel__content">
                <h3>Shortcodes Disponíveis:</h3>
                <table class="widefat" style="margin-top: 15px;">
                    <thead>
                        <tr>
                            <th>Shortcode</th>
                            <th>Descrição</th>
                            <th>Exemplo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>[desconto_pix]</code></td>
                            <td>Mostra o valor do desconto no Pix</td>
                            <td>Economize R$ 22,00 no Pix</td>
                        </tr>
                        <tr>
                            <td><code>[desconto_boleto]</code></td>
                            <td>Mostra o valor do desconto no Boleto</td>
                            <td>Economize R$ 15,00 no Boleto</td>
                        </tr>
                        <tr>
                            <td><code>[tabela_parcelas]</code></td>
                            <td>Exibe uma tabela com todas as opções de parcelamento</td>
                            <td>Tabela completa de parcelas</td>
                        </tr>
                        <tr>
                            <td><code>[economize]</code></td>
                            <td>Mostra quanto o cliente economiza no Pix</td>
                            <td>Economize R$ 22,00</td>
                        </tr>
                        <tr>
                            <td><code>[melhor_parcela]</code></td>
                            <td>Exibe a melhor opção de parcelamento sem juros</td>
                            <td>Em até 3x de R$ 66,00 sem juros</td>
                        </tr>
                    </tbody>
                </table>

                <div style="margin-top: 20px;">
                    <h4>Como usar:</h4>
                    <p>Copie o shortcode desejado e cole em qualquer página, post ou widget do seu site. Por exemplo:</p>
                    <pre style="background: #f0f0f1; padding: 10px; border-radius: 4px;">[desconto_pix] - Mostra o desconto no Pix
[tabela_parcelas] - Mostra todas as opções de parcelamento</pre>
                </div>

                <p style="margin-top: 20px;">Para mais informações sobre os shortcodes disponíveis, visite nossa <a href="https://github.com/ojosejefferson/Parcelas-Flex-For-WooCommerce" target="_blank">documentação no GitHub</a>.</p>
            </div>
        </div>
    </div>
</div>

<style>
.wc-settings-panel {
    background: #fff;
    border: 1px solid #ccd0d4;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
    margin-top: 20px;
}

.wc-settings-panel__header {
    padding: 15px 20px;
    border-bottom: 1px solid #ccd0d4;
}

.wc-settings-panel__header h2 {
    margin: 0;
    font-size: 1.3em;
    font-weight: 600;
}

.wc-settings-panel__body {
    padding: 20px;
}

.wc-settings-panel__content {
    max-width: 100%;
}

.wc-settings-panel__row {
    display: flex;
    flex-wrap: wrap;
    margin: -10px;
}

.wc-settings-panel__col {
    flex: 1;
    min-width: 300px;
    padding: 10px;
}

.wc-settings-panel__field {
    margin-bottom: 20px;
}

.wc-settings-panel__field h3 {
    margin-top: 0;
    margin-bottom: 15px;
    font-size: 1.1em;
    font-weight: 600;
}

.wc-settings-panel__fields-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 15px;
}

.wc-settings-panel__field label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
}

.wc-settings-panel__field input[type="text"],
.wc-settings-panel__field input[type="number"] {
    width: 100%;
    max-width: 300px;
}

.wc-settings-panel__checkbox {
    display: flex;
    align-items: center;
    gap: 8px;
}

.wc-settings-panel__footer {
    padding: 15px 20px;
    border-top: 1px solid #ccd0d4;
    text-align: right;
}

.description {
    color: #666;
    font-style: italic;
    margin-bottom: 15px;
}

.notice {
    margin: 20px 0;
}
</style>

<?php
}

function parcelas_flex_registrar_configuracoes()
{
    // Registro de campos individuais
    register_setting('parcelas_flex-opcoes-pagamento', 'desconto_pix');
    register_setting('parcelas_flex-opcoes-pagamento', 'desconto_boleto');
    register_setting('parcelas_flex-opcoes-pagamento', 'valor_minimo_parcela');
    register_setting('parcelas_flex-opcoes-pagamento', 'exibir_juros_porcentagem');
    register_setting('parcelas_flex-opcoes-pagamento', 'parcelas_flex_texto_a_vista');
    register_setting('parcelas_flex-opcoes-pagamento', 'parcelas_flex_texto_no_pix');
    register_setting('parcelas_flex-opcoes-pagamento', 'parcelas_flex_texto_a_boleto');
    register_setting('parcelas_flex-opcoes-pagamento', 'parcelas_flex_texto_no_boleto');
    register_setting('parcelas_flex-opcoes-pagamento', 'parcelas_flex_texto_economize');
    register_setting('parcelas_flex-opcoes-pagamento', 'parcelas_flex_texto_melhor_parcela');
    register_setting('parcelas_flex-opcoes-pagamento', 'parcelas_flex_texto_melhor_parcelas_cjuros');

    // Loop para registrar as taxas de juros por parcela
    for ($i = 1; $i <= 12; $i++) {
        register_setting('parcelas_flex-opcoes-pagamento', "parcelamento_juros_$i");
    }
}

add_action('admin_init', 'parcelas_flex_registrar_configuracoes');
?>
