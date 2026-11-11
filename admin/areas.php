<?php

declare(strict_types=1);

/* =========================================================
   AUTENTICAÇÃO NORMAL
========================================================= */

require_once __DIR__ .
    '/../includes/autenticacao.php';


/* =========================================================
   CONEXÃO
========================================================= */

require_once __DIR__ .
    '/../includes/conexao.php';

$pdo =
    conectarBanco();


/* =========================================================
   REPOSITORY USUÁRIO
========================================================= */

require_once __DIR__ .
    '/../includes/repositories/UsuarioRepository.php';


/* =========================================================
   USUÁRIO
========================================================= */

$usuario =
    $_SESSION['usuario']
    ?? [];


$usuarioId =
    (int) (
        $usuario['id']
        ?? 0
    );


$nomeUsuario =
    trim(
        (string) (
            $usuario['nome']
            ?? 'Usuário'
        )
    );


/* =========================================================
   EXIGE AUTORIZAÇÃO ESPECÍFICA PARA ÁREAS
========================================================= */

$autorizacaoAreas =
    $_SESSION['areas_autorizado']
    ?? null;


if (
    !is_array($autorizacaoAreas) ||
    (int) (
        $autorizacaoAreas['usuario_id']
        ?? 0
    ) !== $usuarioId
) {

    header(
        'Location: ../login.php?destino=areas',
        true,
        303
    );

    exit;
}


/* =========================================================
   REPOSITORY
========================================================= */

$usuarioRepository =
    new UsuarioRepository();


/* =========================================================
   CONFIRMA USUÁRIO ATIVO
========================================================= */

if (
    $usuarioId <= 0 ||
    !$usuarioRepository->estaAtivo(
        $usuarioId
    )
) {

    unset(
        $_SESSION['usuario'],
        $_SESSION['areas_autorizado']
    );


    $_SESSION['erro_login'] =
        'Seu acesso foi desativado ou sua sessão não é mais válida.';


    header(
        'Location: ../login.php?destino=areas',
        true,
        303
    );

    exit;
}


/* =========================================================
   ÁREAS AUTORIZADAS
========================================================= */

$areasUsuario =
    $usuarioRepository
        ->listarAreasDoUsuario(
            $usuarioId
        );


if (empty($areasUsuario)) {

    unset(
        $_SESSION['areas_autorizado']
    );


    $_SESSION['erro_login'] =
        'Sua conta não possui nenhuma área autorizada.';


    header(
        'Location: ../login.php?destino=areas',
        true,
        303
    );

    exit;
}


/* =========================================================
   TODAS AS ÁREAS
========================================================= */

$sql = '
    SELECT

        a.id,
        a.nome,
        a.slug,
        a.descricao,
        a.icone,
        a.ativo,
        a.ordem,
        a.criado_em,
        a.atualizado_em,

        COUNT(
            DISTINCT p.id
        ) AS total_perguntas,

        COUNT(
            DISTINCT ua.usuario_id
        ) AS total_usuarios

    FROM areas a

    LEFT JOIN perguntas p
        ON p.area_id = a.id

    LEFT JOIN usuario_areas ua
        ON ua.area_id = a.id

    GROUP BY

        a.id,
        a.nome,
        a.slug,
        a.descricao,
        a.icone,
        a.ativo,
        a.ordem,
        a.criado_em,
        a.atualizado_em

    ORDER BY
        a.ordem ASC,
        a.nome ASC
';


$stmt =
    $pdo->query(
        $sql
    );


$areas =
    $stmt->fetchAll(
        PDO::FETCH_ASSOC
    );


/* =========================================================
   ESTATÍSTICAS
========================================================= */

$totalAreas =
    count($areas);

$totalAtivas =
    0;

$totalInativas =
    0;

$totalPerguntas =
    0;


foreach ($areas as $area) {

    if (
        (int) (
            $area['ativo']
            ?? 0
        ) === 1
    ) {

        $totalAtivas++;

    } else {

        $totalInativas++;
    }


    $totalPerguntas +=
        (int) (
            $area['total_perguntas']
            ?? 0
        );
}


/* =========================================================
   MENSAGENS
========================================================= */

$mensagemSucesso =
    $_SESSION['sucesso_area']
    ?? null;


$mensagemErro =
    $_SESSION['erro_area']
    ?? null;


unset(
    $_SESSION['sucesso_area'],
    $_SESSION['erro_area']
);


/* =========================================================
   ESCAPE
========================================================= */

function escaparAreaAdmin(
    string $valor
): string {

    return htmlspecialchars(
        $valor,
        ENT_QUOTES,
        'UTF-8'
    );
}


/* =========================================================
   BASE URL
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
        dirname(
            dirname(
                $scriptName
            )
        ),
        '/'
    );


if (
    $baseUrl === '.' ||
    $baseUrl === '/'
) {
    $baseUrl = '';
}


/* =========================================================
   PÁGINA
========================================================= */

$siteSubtitulo =
    'Gerenciar Áreas';


$paginaTitulo =
    'Gerenciar Áreas - Desafio do Milhão';


$paginaDescricao =
    'Cadastre e gerencie as áreas disponíveis no Desafio do Milhão.';


$paginaCss =
    $baseUrl .
    '/assets/css/admin-areas.css';


/* =========================================================
   HEADER
========================================================= */

require_once __DIR__ .
    '/../includes/header.php';

?>


<main class="pagina-admin-areas">


    <section class="cabecalho-admin-areas">


        <img
            src="<?php echo escaparAreaAdmin($baseUrl); ?>/assets/img/logo/favicon.png"
            alt="Desafio do Milhão"
            class="logo-admin-areas"
        >


        <span class="rotulo-admin-areas">

            <i class="fa-solid fa-layer-group"></i>

            ÁREA ADMINISTRATIVA

        </span>


        <h1>
            GERENCIAR ÁREAS
        </h1>


        <p>
            Cadastre, edite e organize as áreas disponíveis no jogo.
        </p>


    </section>


    <!-- USUÁRIO -->

    <section class="painel-usuario-areas">


        <div class="usuario-logado-areas">


            <div class="icone-usuario-areas">

                <i class="fa-solid fa-user-shield"></i>

            </div>


            <div>

                <span>
                    USUÁRIO LOGADO
                </span>

                <strong>

                    <?php
                    echo escaparAreaAdmin(
                        $nomeUsuario
                    );
                    ?>

                </strong>

            </div>


        </div>


        <div class="areas-autorizadas-admin">


            <span class="titulo-areas-autorizadas">

                ÁREAS AUTORIZADAS

            </span>


            <div class="tags-areas-autorizadas">


                <?php foreach ($areasUsuario as $areaUsuario): ?>


                    <span class="tag-area-autorizada">


                        <i
                            class="<?php
                            echo escaparAreaAdmin(
                                (string) (
                                    $areaUsuario['icone']
                                    ?? 'fa-solid fa-layer-group'
                                )
                            );
                            ?>"
                        ></i>


                        <?php
                        echo escaparAreaAdmin(
                            (string) (
                                $areaUsuario['nome']
                                ?? 'Área'
                            )
                        );
                        ?>


                    </span>


                <?php endforeach; ?>


            </div>


        </div>


<a href="/desafiodomilhao/public/logout.php" class="botao-sair-admin-areas">
    <i class="fa-solid fa-right-from-bracket"></i>
    SAIR
</a>

    </section>


    <?php if ($mensagemSucesso): ?>


        <section class="mensagem-admin-areas sucesso">

            <i class="fa-solid fa-circle-check"></i>

            <div>

                <strong>
                    OPERAÇÃO REALIZADA
                </strong>

                <span>
                    <?php
                    echo escaparAreaAdmin(
                        (string) $mensagemSucesso
                    );
                    ?>
                </span>

            </div>

        </section>


    <?php endif; ?>


    <?php if ($mensagemErro): ?>


        <section class="mensagem-admin-areas erro">

            <i class="fa-solid fa-circle-exclamation"></i>

            <div>

                <strong>
                    NÃO FOI POSSÍVEL CONCLUIR
                </strong>

                <span>
                    <?php
                    echo escaparAreaAdmin(
                        (string) $mensagemErro
                    );
                    ?>
                </span>

            </div>

        </section>


    <?php endif; ?>


    <!-- GERENCIAMENTO -->

    <section class="barra-gerenciamento-areas">


        <div>

            <span>
                GERENCIAMENTO
            </span>

            <h2>
                Áreas cadastradas
            </h2>

        </div>


        <a
            href="area-cadastrar.php"
            class="botao-nova-area"
        >

            <i class="fa-solid fa-plus"></i>

            CADASTRAR NOVA ÁREA

        </a>


    </section>


    <!-- ESTATÍSTICAS -->

    <section class="estatisticas-admin-areas">


        <article class="card-estatistica-area">

            <div class="icone-estatistica-area">

                <i class="fa-solid fa-layer-group"></i>

            </div>

            <div>

                <span>
                    TOTAL DE ÁREAS
                </span>

                <strong>
                    <?php echo $totalAreas; ?>
                </strong>

            </div>

        </article>


        <article class="card-estatistica-area">

            <div class="icone-estatistica-area ativo">

                <i class="fa-solid fa-circle-check"></i>

            </div>

            <div>

                <span>
                    ATIVAS
                </span>

                <strong>
                    <?php echo $totalAtivas; ?>
                </strong>

            </div>

        </article>


        <article class="card-estatistica-area">

            <div class="icone-estatistica-area inativo">

                <i class="fa-solid fa-circle-pause"></i>

            </div>

            <div>

                <span>
                    INATIVAS
                </span>

                <strong>
                    <?php echo $totalInativas; ?>
                </strong>

            </div>

        </article>


        <article class="card-estatistica-area">

            <div class="icone-estatistica-area">

                <i class="fa-solid fa-database"></i>

            </div>

            <div>

                <span>
                    PERGUNTAS
                </span>

                <strong>
                    <?php echo $totalPerguntas; ?>
                </strong>

            </div>

        </article>


    </section>


    <!-- LISTA -->

    <section class="lista-admin-areas">


        <?php foreach ($areas as $area): ?>


            <?php

            $areaId =
                (int) (
                    $area['id']
                    ?? 0
                );

            $ativo =
                (int) (
                    $area['ativo']
                    ?? 0
                ) === 1;

            $icone =
                trim(
                    (string) (
                        $area['icone']
                        ?? ''
                    )
                );

            if ($icone === '') {
                $icone =
                    'fa-solid fa-layer-group';
            }

            $descricao =
                trim(
                    (string) (
                        $area['descricao']
                        ?? ''
                    )
                );

            if ($descricao === '') {
                $descricao =
                    'Área sem descrição cadastrada.';
            }

            ?>


            <article
                class="card-admin-area <?php
                echo $ativo
                    ? 'area-ativa'
                    : 'area-inativa';
                ?>"
            >


                <header class="topo-card-admin-area">


                    <div class="icone-card-admin-area">

                        <i
                            class="<?php
                            echo escaparAreaAdmin(
                                $icone
                            );
                            ?>"
                        ></i>

                    </div>


                    <div class="identificacao-admin-area">


                        <span class="ordem-admin-area">

                            ORDEM

                            <strong>

                                <?php
                                echo (int) (
                                    $area['ordem']
                                    ?? 0
                                );
                                ?>

                            </strong>

                        </span>


                        <span
                            class="status-admin-area <?php
                            echo $ativo
                                ? 'ativo'
                                : 'inativo';
                            ?>"
                        >

                            <i
                                class="<?php
                                echo $ativo
                                    ? 'fa-solid fa-circle-check'
                                    : 'fa-solid fa-circle-pause';
                                ?>"
                            ></i>

                            <?php
                            echo $ativo
                                ? 'ATIVA'
                                : 'INATIVA';
                            ?>

                        </span>


                    </div>


                </header>


                <div class="conteudo-card-admin-area">


                    <h3>

                        <?php
                        echo escaparAreaAdmin(
                            (string) (
                                $area['nome']
                                ?? 'Área'
                            )
                        );
                        ?>

                    </h3>


                    <span class="slug-admin-area">

                        <i class="fa-solid fa-link"></i>

                        <?php
                        echo escaparAreaAdmin(
                            (string) (
                                $area['slug']
                                ?? ''
                            )
                        );
                        ?>

                    </span>


                    <p>

                        <?php
                        echo escaparAreaAdmin(
                            $descricao
                        );
                        ?>

                    </p>


                </div>


                <div class="informacoes-card-admin-area">


                    <span>

                        <i class="fa-solid fa-database"></i>

                        <strong>
                            <?php
                            echo (int) (
                                $area['total_perguntas']
                                ?? 0
                            );
                            ?>
                        </strong>

                        pergunta(s)

                    </span>


                    <span>

                        <i class="fa-solid fa-users"></i>

                        <strong>
                            <?php
                            echo (int) (
                                $area['total_usuarios']
                                ?? 0
                            );
                            ?>
                        </strong>

                        usuário(s)

                    </span>


                </div>


                <footer class="acoes-card-admin-area">


                    <a
                        href="area-editar.php?id=<?php echo $areaId; ?>"
                        class="botao-editar-area"
                    >

                        <i class="fa-solid fa-pen"></i>

                        EDITAR

                    </a>


                </footer>


            </article>


        <?php endforeach; ?>


    </section>


    <section class="rodape-admin-areas">


        <a
            href="<?php echo escaparAreaAdmin($baseUrl); ?>/areas.php"
            class="botao-voltar-selecao"
        >

            <i class="fa-solid fa-arrow-left"></i>

            VOLTAR PARA ÁREAS

        </a>


        <a
            href="area-cadastrar.php"
            class="botao-cadastrar-rodape"
        >

            <i class="fa-solid fa-plus"></i>

            CADASTRAR NOVA ÁREA

        </a>


    </section>


</main>


<?php

require_once __DIR__ .
    '/../includes/footer.php';

?>


</body>
</html>