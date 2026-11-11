<?php

declare(strict_types=1);

/* =========================================================
   SESSÃO
========================================================= */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}


/* =========================================================
   CONEXÃO
========================================================= */

require_once __DIR__ . '/includes/conexao.php';

$pdo = conectarBanco();


/* =========================================================
   BASE URL DINÂMICA
========================================================= */

$scriptName =
    str_replace(
        '\\',
        '/',
        (string) (
            $_SERVER['SCRIPT_NAME']
            ?? ''
        )
    );

$baseUrl =
    rtrim(
        dirname($scriptName),
        '/'
    );

if (
    $baseUrl === '.' ||
    $baseUrl === '/'
) {
    $baseUrl = '';
}


/* =========================================================
   LISTA ÁREAS ATIVAS
========================================================= */

$sqlAreas = '
    SELECT
        id,
        nome,
        slug,
        descricao,
        icone,
        ativo,
        ordem

    FROM areas

    WHERE ativo = 1

    ORDER BY
        ordem ASC,
        nome ASC
';

$stmtAreas =
    $pdo->query(
        $sqlAreas
    );

$areas =
    $stmtAreas->fetchAll(
        PDO::FETCH_ASSOC
    );


/* =========================================================
   CONFIGURAÇÕES
========================================================= */

$siteSubtitulo =
    'Escolha sua área';

$paginaTitulo =
    'Escolha sua Área - Desafio do Milhão';

$paginaDescricao =
    'Escolha uma área de conhecimento para iniciar o desafio.';

$paginaCss =
    $baseUrl .
    '/assets/css/areas.css';


/* =========================================================
   HEADER
========================================================= */

require_once __DIR__ .
    '/includes/header.php';

?>


<main class="pagina-areas">


    <!-- =====================================================
         CABEÇALHO
    ====================================================== -->

    <section class="cabecalho-areas">

        <img
            src="<?php echo htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8'); ?>/assets/img/logo/favicon.png"
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

    <section class="acoes-superiores-areas">


        <div class="titulo-acoes-areas">

            <span>
                ÁREAS DISPONÍVEIS
            </span>

            <strong>

                <?php echo count($areas); ?>

                área(s)

            </strong>

        </div>


        <a
            href="<?php echo htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8'); ?>/login.php?destino=areas"
            class="botao-gerenciar-areas"
        >

            <i class="fa-solid fa-gear"></i>

            GERENCIAR ÁREAS

        </a>


    </section>


    <!-- =====================================================
         ÁREAS
    ====================================================== -->

    <section class="grade-areas">


        <?php if (empty($areas)): ?>


            <div class="areas-vazias">

                <i class="fa-solid fa-layer-group"></i>

                <strong>
                    Nenhuma área disponível
                </strong>

                <span>
                    Não existem áreas ativas para iniciar uma partida.
                </span>

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
                    (string) (
                        $area['nome']
                        ?? 'Área'
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
                            ?? ''
                        )
                    );

                if ($areaIcone === '') {
                    $areaIcone =
                        'fa-solid fa-layer-group';
                }

                ?>


                <form
                    action="<?php echo htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8'); ?>/selecionar-area.php"
                    method="POST"
                    class="form-area"
                >

                    <input
                        type="hidden"
                        name="area_id"
                        value="<?php echo $areaId; ?>"
                    >


                    <button
                        type="submit"
                        class="card-area"
                    >


                        <span class="icone-area">

                            <i
                                class="<?php
                                echo htmlspecialchars(
                                    $areaIcone,
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                                ?>"
                            ></i>

                        </span>


                        <span class="conteudo-area">


                            <strong>

                                <?php
                                echo htmlspecialchars(
                                    $areaNome,
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                                ?>

                            </strong>


                            <small>

                                <?php
                                echo htmlspecialchars(
                                    $areaDescricao,
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                                ?>

                            </small>


                        </span>


                        <span class="seta-area">

                            <i class="fa-solid fa-chevron-right"></i>

                        </span>


                    </button>


                </form>


            <?php endforeach; ?>


        <?php endif; ?>


    </section>


    <!-- =====================================================
         AÇÕES
    ====================================================== -->

    <section class="rodape-areas">


        <a
            href="<?php echo htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8'); ?>/"
            class="botao-voltar-areas"
        >

            <i class="fa-solid fa-arrow-left"></i>

            VOLTAR

        </a>


        <a
            href="<?php echo htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8'); ?>/login.php?destino=areas"
            class="botao-cadastrar-area"
        >

            <i class="fa-solid fa-plus"></i>

            CADASTRAR ÁREA

        </a>


    </section>


</main>


<?php

require_once __DIR__ .
    '/includes/footer.php';

?>


</body>
</html>