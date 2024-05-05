<?php
// Cria a página de configurações
function parcelas_flex_parcelamento_settings_page()
{
    // Verifica qual aba está ativa, padrão para 'configuracao'
    $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'configuracao';
?>


<div class="wrap">
    <h2>Configurações de Parcelamento</h2>

    <form method="post" action="options.php" class="mt-4">
        <?php settings_fields('parcelas-flex-parcelamento-settings-group'); ?>
        <?php do_settings_sections('parcelas-flex-parcelamento-settings-group'); ?>

        <div class="metabox-holder">
            <div class="postbox">
                <div class="inside">
                    <div style="display: flex;">
                        <div style="flex: 1;">
                            <!-- Conteúdo da segunda coluna aqui -->
                            <h3>Configure a Taxa de Juros Por Parcela</h3>
                            <p>
                                <strong>Digite 0 </strong>para parcelamento sem juros
                            </p>

                            <?php for ($i = 1; $i <= 12; $i++) : ?>
                                <div class="row" style="margin-bottom: 18px;">
                                    <div class="col">
                                        <label for="parcelamento_juros_<?php echo $i; ?>">Taxa de Juros para <?php echo $i; ?>x (%)</label>
                                        <input type="number" class="regular-text form-control mb-2" id="parcelamento_juros_<?php echo $i; ?>" name="parcelamento_juros_<?php echo $i; ?>" value="<?php echo esc_attr(get_option("parcelamento_juros_$i", 0)); ?>" step="0.01" min="0" max="100">
                                    </div>
                                </div>
                            <?php endfor; ?>
                            <p>Informações de Shortcode</p> <a href="">ver os shortcodes</a>
                        </div>


                        <div style="flex: 1;">
                            <h3>Configure o desconto e valor mínimo de parcela</h3>
                            <p>
                                <strong>Valor em % Pix e Boleto</strong> Real valor mínimo de parcela
                            </p>

                            <!-- Conteúdo da primeira coluna aqui -->
                            <div class="row mb-3" style="margin-bottom: 18px;"> 
                                <div class="col">
                                    <label for="desconto_pix">Desconto Pix (%)</label>
                                    <input type="number" class="regular-text form-control mb-2" id="desconto_pix" name="desconto_pix" value="<?php echo esc_attr(get_option('desconto_pix')); ?>" step="0.01" min="0" max="100">
                                </div>
                            </div>

                            <div class="row mb-3" style="margin-bottom: 18px;">
                                <div class="col">
                                    <label for="desconto_boleto">Desconto Boleto (%)</label>
                                    <input type="number" class="regular-text form-control mb-2" id="desconto_boleto" name="desconto_boleto" value="<?php echo esc_attr(get_option('desconto_boleto')); ?>" step="0.01" min="0" max="100">
                                </div>
                            </div>


                            <div class="row mb-3" style="margin-bottom: 18px;">
                                <div class="col">
                                    <label for="valor_minimo_parcela" >Valor mínimo da parcela (R$)</label>
                                    <input type="number" class="regular-text form-control mb-2" id="valor_minimo_parcela" name="valor_minimo_parcela" value="<?php echo esc_attr(get_option('valor_minimo_parcela')); ?>" step="0.01" min="0">
                                </div>
                            </div>


                            <div class="row mb-3">
                                <div class="col">
                                    <input type="checkbox" id="exibir_juros_porcentagem" name="exibir_juros_porcentagem" value="1" <?php checked(1, get_option('exibir_juros_porcentagem', 0)); ?>>
                                    <label for="exibir_juros_porcentagem">Exibir a porcentagem de juros no frontend</label>
                                </div>
                                <p></p>
                                <?php
                                // Saída de segurança, ação e opção de campos para a página de configurações
                                settings_fields('parcelas_flex-opcoes-pagamento');
                                do_settings_sections('parcelas_flex-opcoes-pagamento');
                                // Campos de entrada para as preferências do usuário
                                ?>
                                <table style="text-align: left;">
                    <tr>
                        <th scope="row"><label for="parcelas_flex_texto_a_vista">Prefixo 'Pix'</label></th>
                        <td>
                            <input name="parcelas_flex_texto_a_vista" type="text" id="parcelas_flex_texto_a_vista" value="<?php echo esc_attr(get_option('parcelas_flex_texto_a_vista', 'à vista')); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="parcelas_flex_texto_no_pix">Sufixo 'Pix'</label></th>
                        <td>
                            <input name="parcelas_flex_texto_no_pix" type="text" id="parcelas_flex_texto_no_pix" value="<?php echo esc_attr(get_option('parcelas_flex_texto_no_pix', 'no Pix')); ?>" class="regular-text">
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="parcelas_flex_texto_a_boleto">Prefixo 'à vista'</label></th>
                        <td>
                            <input name="parcelas_flex_texto_a_boleto" type="text" id="parcelas_flex_texto_a_boleto" value="<?php echo esc_attr(get_option('parcelas_flex_texto_a_boleto', 'à vista Boleto')); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="parcelas_flex_texto_no_boleto">Prefixo 'no Boleto'</label></th>
                        <td>
                            <input name="parcelas_flex_texto_no_boleto" type="text" id="parcelas_flex_texto_no_boleto" value="<?php echo esc_attr(get_option('parcelas_flex_texto_no_boleto', 'no Boleto')); ?>" class="regular-text">
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="parcelas_flex_texto_economize">Prefixo 'Economize no Pix'</label></th>
                        <td>
                            <input name="parcelas_flex_texto_economize" type="text" id="parcelas_flex_texto_economize" value="<?php echo esc_attr(get_option('parcelas_flex_texto_economize', 'Economize no Pix')); ?>" class="regular-text">
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="parcelas_flex_texto_melhor_parcela">Prefixo 'sem juros'</label></th>
                        <td>
                            <input name="parcelas_flex_texto_melhor_parcela" type="text" id="parcelas_flex_texto_melhor_parcela" value="<?php echo esc_attr(get_option('parcelas_flex_texto_melhor_parcela', 'sem juros')); ?>" class="regular-text">
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="parcelas_flex_texto_melhor_parcelas_cjuros">Prefixo 'com juros'</label></th>
                        <td>
                            <input name="parcelas_flex_texto_melhor_parcelas_cjuros" type="text" id="parcelas_flex_texto_melhor_parcelas_cjuros" value="<?php echo esc_attr(get_option('parcelas_flex_texto_melhor_parcelas_cjuros', 'com juros')); ?>" class="regular-text">
                        </td>
                    </tr>
                </table>
                            </div>
                        </div> <!-- fim da coluna 1 -->

                        
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="button button-primary" style="margin-top: 15px;">Salvar Alterações</button>

    </form>
    
</div>



<?php
}


function parcelas_flex_registrar_configuracoes()
{
    register_setting('parcelas_flex-opcoes-pagamento', 'parcelas_flex_texto_a_vista');
    register_setting('parcelas_flex-opcoes-pagamento', 'parcelas_flex_texto_no_pix');
    register_setting('parcelas_flex-opcoes-pagamento', 'parcelas_flex_texto_a_boleto');
    register_setting('parcelas_flex-opcoes-pagamento', 'parcelas_flex_texto_no_boleto');
    register_setting('parcelas_flex-opcoes-pagamento', 'parcelas_flex_texto_economize');
    register_setting('parcelas_flex-opcoes-pagamento', 'parcelas_flex_texto_melhor_parcela');
    register_setting('parcelas_flex-opcoes-pagamento', 'parcelas_flex_texto_melhor_parcelas_cjuros');


    // ... registrar as outras configurações ...
}

add_action('admin_init', 'parcelas_flex_registrar_configuracoes');
?>
