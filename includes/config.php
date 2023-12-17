<?php
// Cria a página de configurações
function meu_plugin_de_parcelamento_settings_page()
{
    // Verifica qual aba está ativa, padrão para 'configuracao'
    $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'configuracao';
?>


    <div class="wrap">
        <h3>Configurações de Parcelamento</h3>

        <!-- Abas de navegação -->
        <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">

                <button class="nav-link active" id="pills-configuracao-tab" data-bs-toggle="pill" data-bs-target="#pills-configuracao" type="button" role="tab" aria-controls="pills-configuracao" aria-selected="true">Configuração</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo $active_tab == 'estilos' ? 'active' : ''; ?>" id="pills-estilos-tab" data-bs-toggle="pill" data-bs-target="#pills-estilos" type="button" role="tab" aria-controls="pills-estilos" aria-selected="<?php echo $active_tab == 'estilos' ? 'true' : 'false'; ?>">Estilos</button>
            </li>

            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo $active_tab == 'shortcode' ? 'active' : ''; ?>" id="pills-shortcode-tab" data-bs-toggle="pill" data-bs-target="#pills-shortcode" type="button" role="tab" aria-controls="pills-shortcode" aria-selected="<?php echo $active_tab == 'shortcode' ? 'true' : 'false'; ?>">Shortcode</button>
            </li>
        </ul>

        <!-- Conteúdo das abas -->
        <div class="tab-content" id="pills-tabContent">

            <div class="tab-pane fade show active" id="pills-configuracao" role="tabpanel" aria-labelledby="pills-configuracao" tabindex="0">
                <!-- Conteúdo da aba Configuração -->

                <form method="post" action="options.php" class="mt-4">
                    <?php settings_fields('meu-plugin-de-parcelamento-settings-group'); ?>
                    <?php do_settings_sections('meu-plugin-de-parcelamento-settings-group'); ?>

                    <div class="container">
                        <div class="row">
                            <div class="col-6">
                                <!-- Conteúdo da segunda coluna aqui -->
                                <h3>Configure a Taxa de Juros Por Parcela</h3>
                                <p>
                                    <mark><strong> Digite 0 </strong></mark>para parcelamento sem juros
                                </p>

                                <?php for ($i = 1; $i <= 12; $i++) : ?>
                                    <div class="row mb-3">
                                        <div class="col">
                                            <div class="form-floating">
                                                <input type="number" class="form-control" id="parcelamento_juros_<?php echo $i; ?>" name="parcelamento_juros_<?php echo $i; ?>" placeholder="Taxa de Juros para <?php echo $i; ?>x" value="<?php echo esc_attr(get_option("parcelamento_juros_$i", 0)); ?>" step="0.01" min="0" max="100">
                                                <label for="parcelamento_juros_<?php echo $i; ?>">Taxa de Juros para <?php echo $i; ?>x (%)</label>
                                            </div>
                                        </div>

                                    </div>
                                <?php endfor; ?>
                            </div>


                            <div class="col-6">
                                <h3>Configure o desconto e valor minimo de parcela</h3>
                                <p>
                                    <strong>Valor em % Pix e Boleto</strong> Real valor minimo de parcela
                                </p>

                                <!-- Conteúdo da primeira coluna aqui -->
                                <div class="row mb-3">
                                    <div class="col-">
                                        <div class="form-floating">
                                            <input type="number" class="form-control" id="desconto_pix" name="desconto_pix" placeholder="Desconto Pix" value="<?php echo esc_attr(get_option('desconto_pix')); ?>" step="0.01" min="0" max="100">
                                            <label for="desconto_pix">Desconto Pix (%)</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-">
                                        <div class="form-floating">
                                            <input type="number" class="form-control" id="desconto_boleto" name="desconto_boleto" placeholder="Desconto Boleto" value="<?php echo esc_attr(get_option('desconto_boleto')); ?>" step="0.01" min="0" max="100">
                                            <label for="desconto_boleto">Desconto Boleto (%)</label>
                                        </div>
                                    </div>
                                </div>


                                <div class="row mb-3">
                                    <div class="col">
                                        <div class="form-floating">
                                            <input type="number" class="form-control" id="valor_minimo_parcela" name="valor_minimo_parcela" placeholder="Valor mínimo da parcela" value="<?php echo esc_attr(get_option('valor_minimo_parcela')); ?>" step="0.01" min="0">
                                            <label for="valor_minimo_parcela">Valor mínimo da parcela (R$)</label>
                                        </div>
                                    </div>
                                </div>


                                <input type="checkbox" class="form-check-input" id="exibir_juros_porcentagem" name="exibir_juros_porcentagem" value="1" <?php checked(1, get_option('exibir_juros_porcentagem', 0)); ?>>
                                <label class="form-check-label" for="exibir_juros_porcentagem">Exibir a porcentagem de juros no frontend</label>



                            </div> <!-- fim da coluna 1 -->
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>

                </form>

            </div>


            <div class="tab-pane fade <?php echo $active_tab == 'estilos' ? 'show active' : ''; ?>" id="pills-estilos" role="tabpanel" aria-labelledby="pills-estilos-tab" tabindex="0">
                <!-- Conteúdo da aba Estilos -->
                <h3>Nome para shortcode para loops e pagina do produto</h3>
                <p>
                    <strong>Nome </strong> para pix e boleto
                </p>

                <div class="container">
                    <div class="col-6">
                        <div class="wrap">
                            <form action="options.php" method="post">
                                <?php
                                // Saída de segurança, ação e opção de campos para a página de configurações
                                settings_fields('meu-plugin-opcoes-pagamento');
                                do_settings_sections('meu-plugin-opcoes-pagamento');
                                // Campos de entrada para as preferências do usuário
                                ?>
                                <table class="form-table">
                                    <tr>
                                        <th scope="row"><label for="meu_plugin_texto_a_vista">Prefixo 'Pix'</label></th>
                                        <td>
                                            <input name="meu_plugin_texto_a_vista" type="text" id="meu_plugin_texto_a_vista" value="<?php echo esc_attr(get_option('meu_plugin_texto_a_vista', 'à vista')); ?>" class="regular-text">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="meu_plugin_texto_no_pix">Sufixo 'Pix'</label></th>
                                        <td>
                                            <input name="meu_plugin_texto_no_pix" type="text" id="meu_plugin_texto_no_pix" value="<?php echo esc_attr(get_option('meu_plugin_texto_no_pix', 'no Pix')); ?>" class="regular-text">
                                        </td>
                                    </tr>

                                    <tr>
                                        <th scope="row"><label for="meu_plugin_texto_a_boleto">Prefixo 'á vista'</label></th>
                                        <td>
                                            <input name="meu_plugin_texto_a_boleto" type="text" id="meu_plugin_texto_a_boleto" value="<?php echo esc_attr(get_option('meu_plugin_texto_a_boleto', 'á vista Boleto')); ?>" class="regular-text">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="meu_plugin_texto_no_boleto">Prefixo 'no Boleto'</label></th>
                                        <td>
                                            <input name="meu_plugin_texto_no_boleto" type="text" id="meu_plugin_texto_no_boleto" value="<?php echo esc_attr(get_option('meu_plugin_texto_no_boleto', 'no Boleto')); ?>" class="regular-text">
                                        </td>
                                    </tr>

                                    <tr>
                                        <th scope="row"><label for="meu_plugin_texto_economize">Prefixo 'Economize no Pix'</label></th>
                                        <td>
                                            <input name="meu_plugin_texto_economize" type="text" id="meu_plugin_texto_economize" value="<?php echo esc_attr(get_option('meu_plugin_texto_economize', 'Economize no Pix')); ?>" class="regular-text">
                                        </td>
                                    </tr>

                                    <tr>
                                        <th scope="row"><label for="meu_plugin_texto_melhor_parcela">Prefixo 'sem juros'</label></th>
                                        <td>
                                            <input name="meu_plugin_texto_melhor_parcela" type="text" id="meu_plugin_texto_melhor_parcela" value="<?php echo esc_attr(get_option('meu_plugin_texto_melhor_parcela', 'sem juros')); ?>" class="regular-text">
                                        </td>
                                    </tr>

                                    <th scope="row"><label for="meu_plugin_texto_melhor_parcela_cjuros">Prefixo 'com juros'</label></th>
                                    <td>
                                        <input name="meu_plugin_texto_melhor_parcela_cjuros" type="text" id="meu_plugin_texto_melhor_parcela_cjuros" value="<?php echo esc_attr(get_option('meu_plugin_texto_melhor_parcela_cjuros', 'com juros')); ?>" class="regular-text">
                                    </td>
                                    </tr>

                                </table>
                                <?php
                                // Botão de envio
                                submit_button('Salvar Configurações');
                                ?>
                            </form>
                        </div>
                    </div>
                    <div class="col-6">

                    </div>

                </div>




            </div>


            <div class="tab-pane fade <?php echo $active_tab == 'shortcode' ? 'show active' : ''; ?>" id="pills-shortcode" role="tabpanel" aria-labelledby="pills-shortcode-tab" tabindex="0">
                <!-- Conteúdo da aba Estilos -->

                <div class="container mt-5">
                    <div class="row">
                        <div class="col-md-6">
                            <h3>Shortcodes para Pagina de produto</h3>
                            <ul>
                                <li>Mostrar desconto no Pix - <code>[desconto_pix]</code></li>
                                <img src="<?php echo plugins_url('img/descontopix.jpg', __FILE__); ?>" alt="Imagem Desconto no Pix" width="250">
                                <br><br>
                                <li>Mostrar Desconto no Boleto - <code>[desconto_boleto]</code></li>
                                <img src="<?php echo plugins_url('img/descontoboleto.jpg', __FILE__); ?>" alt="Imagem Desconto no Boleto" width="250">
                                <br><br>
                                <li>Mostrar melhor parcela - <code>[melhor_parcelamento]</code></li>
                                <img src="<?php echo plugins_url('img/melhorparcela.jpg', __FILE__); ?>" alt="Imagem Melhor Parcela" width="250">
                                <br><br>
                                <li>Mostrar economize - <code>[economize]</code></li>
                                <img src="<?php echo plugins_url('img/economizepix.jpg', __FILE__); ?>" alt="Imagem Economize" width="250">
                                <br><br>
                                <li>Mostrar tabela de parcelamento - <code>[tabela_parcelamento]</code></li>
                                <img src="<?php echo plugins_url('img/tabeladeparcela.jpg', __FILE__); ?>" alt="Imagem Tabela Parcelamento" width="250">

                            </ul>

 
                        </div>
                        <div class="col-md-6">
                            <h3>Shortcodes para Loops, Categories, etc...</h3>
                            <ul>
                                <li>Mostrar Desconto no Pix - <code>[desconto_pix_loop]</code></li>
                                <img src="<?php echo plugins_url('img/descontopix.jpg', __FILE__); ?>" alt="Imagem Desconto no Pix" width="250">
                                <br><br>
                                <li>Mostrar Desconto no Boleto -<code> [desconto_boleto_loop]</code></li>
                                <img src="<?php echo plugins_url('img/descontoboleto.jpg', __FILE__); ?>" alt="Imagem Desconto no Boleto" width="250">
                                <br><br>

                                <li>Mostrar Economize - <code>[economize_loop]</code></li>
                                <img src="<?php echo plugins_url('img/economizepix.jpg', __FILE__); ?>" alt="Imagem Economize" width="250">

                                <br><br>

                                <li>Mostrar melhor Parcela -<code> [melhor_parcelamento_loop]</code></li>
                                <img src="<?php echo plugins_url('img/melhorparcela.jpg', __FILE__); ?>" alt="Imagem Melhor Parcela" width="250">

                            </ul>

    

                        </div>
                    </div>
                </div>



                <div class="container mt-5">
                    <div class="row">
                        <div class="col-md-6">
                        <h3>Usar codigo direto no functions.php Exemplos</h3>


                            <strong>Paginas de Produtos Exemplos:</strong>

                            <code>
                                <pre>
<!--inline code goes here-->
add_action('woocommerce_before_single_product', 'adicionar_desconto_pix', 5);

function adicionar_desconto_pix() {
    echo do_shortcode('[economize][desconto_pix][desconto_boleto][melhor_parcelamento][tabela_parcelamento]');
}

?> 
</pre>
                            </code>
                        </div>
                        <div class="col-md-6">


                            <h3>Usar codigo direto no functions.php Exemplos</h3>
                            <strong>Paginas de Loops E Categoria Exemplos:</strong>

                            <code>
                                <pre>

    function adicionar_shortcode_acima_preco_produto() {
        echo do_shortcode('[economize_loop][melhor_parcelamento_loop][desconto_boleto_loop]');
    }

    add_action('woocommerce_after_shop_loop_item_title', 'adicionar_shortcode_acima_preco_produto', 9);

    add_action('woocommerce_after_shop_loop_item_title', 'custom_content_after_shop_loop_item_title');
    function custom_content_after_shop_loop_item_title() {
        // Aqui você pode chamar o shortcode que deseja exibir
        echo do_shortcode('[desconto_pix_loop]');
    }

    </pre>
                            </code>

                        </div>
                    </div>
                </div>
                

            </div>
        </div>
    </div>
<?php
}


function meu_plugin_registrar_configuracoes()
{
    register_setting('meu-plugin-opcoes-pagamento', 'meu_plugin_texto_a_vista');
    register_setting('meu-plugin-opcoes-pagamento', 'meu_plugin_texto_no_pix');
    register_setting('meu-plugin-opcoes-pagamento', 'meu_plugin_texto_a_boleto');
    register_setting('meu-plugin-opcoes-pagamento', 'meu_plugin_texto_no_boleto');
    register_setting('meu-plugin-opcoes-pagamento', 'meu_plugin_texto_economize');
    register_setting('meu-plugin-opcoes-pagamento', 'meu_plugin_texto_melhor_parcela');
    register_setting('meu-plugin-opcoes-pagamento', 'meu_plugin_texto_melhor_parcela_cjuros');


    // ... registrar as outras configurações ...
}

add_action('admin_init', 'meu_plugin_registrar_configuracoes');
