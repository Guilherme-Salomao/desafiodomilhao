<?php

declare(strict_types=1);

/* ============================================================
   SESSÃO
============================================================ */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}


/* ============================================================
   DEPENDÊNCIAS
============================================================ */

require_once __DIR__ . '/includes/url.php';

require_once __DIR__ . '/includes/conexao.php';


/* ============================================================
   CONEXÃO
============================================================ */

$pdo = conectarBanco();


/* ============================================================
   FUNÇÃO ESCAPE LOCAL
============================================================ */

function escaparAreaPublica(string $valor): string
{
    return htmlspecialchars(
        $valor,
        ENT_QUOTES,
        'UTF-8'
    );
}


/* ============================================================
   RETORNO
============================================================ */

$retorno = trim(
    (string) ($_GET['retorno'] ?? '')
);


/* ============================================================
   BUSCA ÁREAS ATIVAS
============================================================ */

$sql = '
    SELECT
        id,
        nome,
        slug,
        descricao,
        icone,
        ordem
    FROM areas
    WHERE ativo = 1
    ORDER BY
        ordem ASC,
        nome ASC
';

$stmt = $pdo->query($sql);

$areas = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* ============================================================
   CONFIGURAÇÕES DA PÁGINA
============================================================ */

$siteSubtitulo =
    'Escolha sua Área';

$paginaTitulo =
    'Escolha sua Área - Desafio do Milhão';

$paginaDescricao =
    'Selecione uma área de conhecimento para iniciar o desafio.';

$paginaTipo =
    'website';

$paginaCss =
    urlProjeto(
        'assets/css/areas.css'
    );


/* ============================================================
   HEADER
============================================================ */

require_once __DIR__ . '/includes/header.php';

?>


<main class="pagina-areas">


    <!-- =====================================================
         CABEÇALHO
    ====================================================== -->

    <section class="cabecalho-areas">


        <img
            src="<?php
            echo escaparAreaPublica(
                urlProjeto(
                    'assets/img/logo/favicon.png'
                )
            );
            ?>"
            alt="Desafio do Milhão"
            class="logo-areas"
        >


        <span class="rotulo-areas">

            <i class="fa-solid fa-layer-group"></i>

            SELEÇÃO DE ÁREA

        </span>


        <h1>
            ESCOLHA SUA ÁREA
        </h1>


        <p>
            Selecione uma área de conhecimento para iniciar o desafio.
        </p>


    </section>


    <!-- =====================================================
         BARRA SUPERIOR
    ====================================================== -->

    <section class="barra-superior-areas">


        <div class="contador-areas">


            <span>
                ÁREAS DISPONÍVEIS
            </span>


            <strong>

                <?php
                echo count($areas);
                ?> área(s)

            </strong>


        </div>


        <a
            href="<?php
            echo escaparAreaPublica(
                urlProjeto(
                    'login.php?destino=areas'
                )
            );
            ?>"
            class="botao-gerenciar-areas"
        >

            <i class="fa-solid fa-gear"></i>

            GERENCIAR ÁREAS

        </a>


    </section>


    <!-- =====================================================
         LISTA DE ÁREAS
    ====================================================== -->

    <section class="grade-areas">


        <?php if (empty($areas)): ?>


            <div class="estado-vazio-areas">


                <i class="fa-solid fa-circle-exclamation"></i>


                <h2>
                    NENHUMA ÁREA DISPONÍVEL
                </h2>


                <p>
                    Não existem áreas ativas cadastradas no momento.
                </p>


            </div>


        <?php else: ?>


            <?php foreach ($areas as $area): ?>


                <?php

                $areaId =
                    (int) (
                        $area['id']
                        ?? 0
                    );


                $areaNome =
                    trim(
                        (string) (
                            $area['nome']
                            ?? 'Área'
                        )
                    );


                $areaDescricao =
                    trim(
                        (string) (
                            $area['descricao']
                            ?? ''
                        )
                    );


                $areaIcone =
                    trim(
                        (string) (
                            $area['icone']
                            ?? 'fa-solid fa-layer-group'
                        )
                    );


                $parametrosSelecao = [
                    'id' =>
                        $areaId,
                ];


                if ($retorno !== '') {

                    $parametrosSelecao['retorno'] =
                        $retorno;
                }


                $urlSelecionar =
                    urlProjeto(
                        'selecionar-area.php?' .
                        http_build_query(
                            $parametrosSelecao
                        )
                    );

                ?>


                <a
                    href="<?php
                    echo escaparAreaPublica(
                        $urlSelecionar
                    );
                    ?>"
                    class="card-area"
                >


                    <!-- ÍCONE -->

                    <div class="icone-card-area">

                        <i
                            class="<?php
                            echo escaparAreaPublica(
                                $areaIcone
                            );
                            ?>"
                        ></i>

                    </div>


                    <!-- CONTEÚDO -->

                    <div class="conteudo-card-area">


                        <h2>

                            <?php
                            echo escaparAreaPublica(
                                $areaNome
                            );
                            ?>

                        </h2>


                        <?php if ($areaDescricao !== ''): ?>

                            <p>

                                <?php
                                echo escaparAreaPublica(
                                    $areaDescricao
                                );
                                ?>

                            </p>

                        <?php endif; ?>


                    </div>


                    <!-- SETA -->

                    <div class="seta-card-area">

                        <i class="fa-solid fa-chevron-right"></i>

                    </div>


                </a>


            <?php endforeach; ?>


        <?php endif; ?>


    </section>


    <!-- =====================================================
         AÇÕES INFERIORES
    ====================================================== -->

    <section class="acoes-inferiores-areas">


        <a
            href="<?php
            echo escaparAreaPublica(
                urlProjeto()
            );
            ?>"
            class="botao-voltar-areas"
        >

            <i class="fa-solid fa-arrow-left"></i>

            VOLTAR

        </a>


        <a
            href="<?php
            echo escaparAreaPublica(
                urlProjeto(
                    'login.php?destino=areas&acao=cadastrar'
                )
            );
            ?>"
            class="botao-cadastrar-area"
        >

            <i class="fa-solid fa-plus"></i>

            CADASTRAR ÁREA

        </a>


    </section>


</main>


<?php

require_once __DIR__ . '/includes/footer.php';

?>


</body>
</html>