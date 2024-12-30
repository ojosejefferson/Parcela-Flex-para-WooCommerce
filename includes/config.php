<?php
// Cria a página de configurações
function parcelas_flex_parcelamento_settings_page()
{
    // Verifica qual aba está ativa, padrão para 'configuracao'
    $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'configuracao';
?>


<div class="wrap parcelas-flex-admin">
    <div class="pf-header">
        <h1>Configurações Parcelas Flex</h1>
        <p class="pf-description">Configure as opções de parcelamento e descontos para sua loja</p>
    </div>

    <form method="post" action="options.php" class="pf-form">
        <?php 
        settings_fields('parcelas-flex-parcelamento-settings-group');
        do_settings_sections('parcelas-flex-parcelamento-settings-group');
        settings_fields('parcelas_flex-opcoes-pagamento');
        do_settings_sections('parcelas_flex-opcoes-pagamento');
        ?>

        <div class="pf-grid">
            <!-- Coluna de Descontos -->
            <div class="pf-card">
                <div class="pf-card-header">
                    <h2>Configurações de Desconto</h2>
                    <p>Configure os descontos para PIX e Boleto</p>
                </div>
                <div class="pf-card-content">
                    <div class="pf-form-group">
                        <label for="desconto_pix">
                            <i class="dashicons dashicons-money-alt"></i>
                            Desconto PIX (%)
                        </label>
                        <input type="number" 
                               class="pf-input" 
                               id="desconto_pix" 
                               name="desconto_pix" 
                               value="<?php echo esc_attr(get_option('desconto_pix')); ?>" 
                               step="0.01" 
                               min="0" 
                               max="100">
                    </div>

                    <div class="pf-form-group">
                        <label for="desconto_boleto">
                            <i class="dashicons dashicons-media-text"></i>
                            Desconto Boleto (%)
                        </label>
                        <input type="number" 
                               class="pf-input" 
                               id="desconto_boleto" 
                               name="desconto_boleto" 
                               value="<?php echo esc_attr(get_option('desconto_boleto')); ?>" 
                               step="0.01" 
                               min="0" 
                               max="100">
                    </div>

                    <div class="pf-form-group">
                        <label for="valor_minimo_parcela">
                            <i class="dashicons dashicons-calculator"></i>
                            Valor Mínimo da Parcela (R$)
                        </label>
                        <input type="number" 
                               class="pf-input" 
                               id="valor_minimo_parcela" 
                               name="valor_minimo_parcela" 
                               value="<?php echo esc_attr(get_option('valor_minimo_parcela')); ?>" 
                               step="0.01" 
                               min="0">
                    </div>

                    <div class="pf-form-group pf-checkbox-group">
                        <input type="checkbox" 
                               id="exibir_juros_porcentagem" 
                               name="exibir_juros_porcentagem" 
                               value="1" 
                               <?php checked(1, get_option('exibir_juros_porcentagem', 0)); ?>>
                        <label for="exibir_juros_porcentagem">
                            <i class="dashicons dashicons-visibility"></i>
                            Exibir porcentagem de juros no frontend
                        </label>
                    </div>
                </div>
            </div>

            <!-- Coluna de Parcelamento -->
            <div class="pf-card">
                <div class="pf-card-header">
                    <h2>Configurações de Parcelamento</h2>
                    <p>Configure as taxas de juros para cada parcela</p>
                </div>
                <div class="pf-card-content">
                    <div class="pf-installments-grid">
                        <?php for ($i = 1; $i <= 12; $i++) : ?>
                            <div class="pf-form-group">
                                <label for="parcelamento_juros_<?php echo $i; ?>">
                                    <i class="dashicons dashicons-calendar"></i>
                                    <?php echo $i; ?>x
                                </label>
                                <input type="number" 
                                       class="pf-input" 
                                       id="parcelamento_juros_<?php echo $i; ?>" 
                                       name="parcelamento_juros_<?php echo $i; ?>" 
                                       value="<?php echo esc_attr(get_option("parcelamento_juros_$i", 0)); ?>" 
                                       step="0.01" 
                                       min="0" 
                                       max="100"
                                       placeholder="Taxa %">
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>

            <!-- Nova Coluna de Configurações de Texto -->
            <div class="pf-card">
                <div class="pf-card-header">
                    <h2>Configurações de Texto</h2>
                    <p>Personalize os textos exibidos na loja</p>
                </div>
                <div class="pf-card-content">
                    <div class="pf-form-group">
                        <label for="parcelas_flex_texto_a_vista">
                            <i class="dashicons dashicons-edit"></i>
                            Prefixo 'Pix'
                        </label>
                        <input type="text" 
                               class="pf-input" 
                               id="parcelas_flex_texto_a_vista" 
                               name="parcelas_flex_texto_a_vista" 
                               value="<?php echo esc_attr(get_option('parcelas_flex_texto_a_vista', 'à vista')); ?>"
                               placeholder="Ex: à vista">
                    </div>

                    <div class="pf-form-group">
                        <label for="parcelas_flex_texto_no_pix">
                            <i class="dashicons dashicons-edit"></i>
                            Sufixo 'Pix'
                        </label>
                        <input type="text" 
                               class="pf-input" 
                               id="parcelas_flex_texto_no_pix" 
                               name="parcelas_flex_texto_no_pix" 
                               value="<?php echo esc_attr(get_option('parcelas_flex_texto_no_pix', 'no Pix')); ?>"
                               placeholder="Ex: no Pix">
                    </div>

                    <div class="pf-form-group">
                        <label for="parcelas_flex_texto_a_boleto">
                            <i class="dashicons dashicons-edit"></i>
                            Prefixo 'à vista Boleto'
                        </label>
                        <input type="text" 
                               class="pf-input" 
                               id="parcelas_flex_texto_a_boleto" 
                               name="parcelas_flex_texto_a_boleto" 
                               value="<?php echo esc_attr(get_option('parcelas_flex_texto_a_boleto', 'à vista Boleto')); ?>"
                               placeholder="Ex: à vista Boleto">
                    </div>

                    <div class="pf-form-group">
                        <label for="parcelas_flex_texto_no_boleto">
                            <i class="dashicons dashicons-edit"></i>
                            Prefixo 'no Boleto'
                        </label>
                        <input type="text" 
                               class="pf-input" 
                               id="parcelas_flex_texto_no_boleto" 
                               name="parcelas_flex_texto_no_boleto" 
                               value="<?php echo esc_attr(get_option('parcelas_flex_texto_no_boleto', 'no Boleto')); ?>"
                               placeholder="Ex: no Boleto">
                    </div>

                    <div class="pf-form-group">
                        <label for="parcelas_flex_texto_economize">
                            <i class="dashicons dashicons-edit"></i>
                            Prefixo 'Economize no Pix'
                        </label>
                        <input type="text" 
                               class="pf-input" 
                               id="parcelas_flex_texto_economize" 
                               name="parcelas_flex_texto_economize" 
                               value="<?php echo esc_attr(get_option('parcelas_flex_texto_economize', 'Economize no Pix')); ?>"
                               placeholder="Ex: Economize no Pix">
                    </div>

                    <div class="pf-form-group">
                        <label for="parcelas_flex_texto_melhor_parcela">
                            <i class="dashicons dashicons-edit"></i>
                            Prefixo 'sem juros'
                        </label>
                        <input type="text" 
                               class="pf-input" 
                               id="parcelas_flex_texto_melhor_parcela" 
                               name="parcelas_flex_texto_melhor_parcela" 
                               value="<?php echo esc_attr(get_option('parcelas_flex_texto_melhor_parcela', 'sem juros')); ?>"
                               placeholder="Ex: sem juros">
                    </div>

                    <div class="pf-form-group">
                        <label for="parcelas_flex_texto_melhor_parcelas_cjuros">
                            <i class="dashicons dashicons-edit"></i>
                            Prefixo 'com juros'
                        </label>
                        <input type="text" 
                               class="pf-input" 
                               id="parcelas_flex_texto_melhor_parcelas_cjuros" 
                               name="parcelas_flex_texto_melhor_parcelas_cjuros" 
                               value="<?php echo esc_attr(get_option('parcelas_flex_texto_melhor_parcelas_cjuros', 'com juros')); ?>"
                               placeholder="Ex: com juros">
                    </div>
                </div>
            </div>
        </div>

        <div class="pf-footer">
            <button type="submit" class="button button-primary">
                <i class="dashicons dashicons-saved"></i>
                Salvar Alterações
            </button>
            <a href="https://github.com/ojosejefferson/Parcelas-Flex-For-WooCommerce" 
               target="_blank" 
               class="pf-docs-link">
                <i class="dashicons dashicons-book"></i>
                Documentação e Shortcodes
            </a>
        </div>
    </form>
</div>

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
