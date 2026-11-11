<?php

declare(strict_types=1);

/* =========================================================
   SESSÃO / AUTENTICAÇÃO
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
   USUÁRIO LOGADO
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
    $usuarioId <= 0 ||
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
   FUNÇÃO ESCAPE
========================================================= */

function escaparCadastroArea(
    string $valor
): string {

    return htmlspecialchars(
        $valor,
        ENT_QUOTES,
        'UTF-8'
    );
}


/* =========================================================
   FUNÇÃO PARA GERAR SLUG
========================================================= */

function gerarSlugArea(
    string $texto
): string {

    $texto =
        trim($texto);


    if (
        function_exists(
            'iconv'
        )
    ) {

        $convertido =
            iconv(
                'UTF-8',
                'ASCII//TRANSLIT//IGNORE',
                $texto
            );

        if ($convertido !== false) {

            $texto =
                $convertido;
        }
    }


    $texto =
        strtolower(
            $texto
        );


    $texto =
        preg_replace(
            '/[^a-z0-9]+/',
            '-',
            $texto
        )
        ?? '';


    $texto =
        trim(
            $texto,
            '-'
        );


    return $texto;
}


/* =========================================================
   PRÓXIMA ORDEM
========================================================= */

$stmtOrdem =
    $pdo->query(
        '
            SELECT
                COALESCE(
                    MAX(ordem),
                    0
                ) + 1

            FROM areas
        '
    );


$proximaOrdem =
    (int) $stmtOrdem->fetchColumn();


if ($proximaOrdem < 1) {

    $proximaOrdem = 1;
}


/* =========================================================
   VALORES PADRÃO
========================================================= */

$nome =
    '';

$descricao =
    '';

$icone =
    'fa-solid fa-layer-group';

$ordem =
    $proximaOrdem;

$ativo =
    1;

$erros =
    [];


/* =========================================================
   PROCESSA FORMULÁRIO
========================================================= */

if (
    ($_SERVER['REQUEST_METHOD'] ?? '')
    === 'POST'
) {


    /* =====================================================
       RECEBE DADOS
    ====================================================== */

    $nome =
        trim(
            (string) (
                $_POST['nome']
                ?? ''
            )
        );


    $descricao =
        trim(
            (string) (
                $_POST['descricao']
                ?? ''
            )
        );


    $icone =
        trim(
            (string) (
                $_POST['icone']
                ?? ''
            )
        );


    $ordem =
        (int) (
            $_POST['ordem']
            ?? $proximaOrdem
        );


    $ativo =
        isset(
            $_POST['ativo']
        )
            ? 1
            : 0;


    /* =====================================================
       VALIDA NOME
    ====================================================== */

    if ($nome === '') {

        $erros[] =
            'Informe o nome da área.';

    } elseif (
        mb_strlen(
            $nome
        ) > 120
    ) {

        $erros[] =
            'O nome da área deve possuir no máximo 120 caracteres.';
    }


    /* =====================================================
       VALIDA DESCRIÇÃO
    ====================================================== */

    if (
        mb_strlen(
            $descricao
        ) > 500
    ) {

        $erros[] =
            'A descrição deve possuir no máximo 500 caracteres.';
    }


    /* =====================================================
       VALIDA ÍCONE
    ====================================================== */

    if ($icone === '') {

        $icone =
            'fa-solid fa-layer-group';
    }


    if (
        mb_strlen(
            $icone
        ) > 120
    ) {

        $erros[] =
            'O código do ícone é muito longo.';
    }


    /* =====================================================
       VALIDA ORDEM
    ====================================================== */

    if ($ordem < 1) {

        $ordem = 1;
    }


    /* =====================================================
       GERA SLUG
    ====================================================== */

    $slug =
        gerarSlugArea(
            $nome
        );


    if ($slug === '') {

        $erros[] =
            'Não foi possível gerar o identificador da área.';
    }


    /* =====================================================
       VERIFICA NOME DUPLICADO
    ====================================================== */

    if (empty($erros)) {

        $stmtNome =
            $pdo->prepare(
                '
                    SELECT
                        id

                    FROM areas

                    WHERE
                        LOWER(nome) =
                        LOWER(:nome)

                    LIMIT 1
                '
            );


        $stmtNome->execute(
            [
                ':nome' =>
                    $nome,
            ]
        );


        if (
            $stmtNome->fetchColumn()
        ) {

            $erros[] =
                'Já existe uma área cadastrada com esse nome.';
        }
    }


    /* =====================================================
       GARANTE SLUG ÚNICO
    ====================================================== */

    if (empty($erros)) {

        $slugBase =
            $slug;


        $contador =
            2;


        while (true) {

            $stmtSlug =
                $pdo->prepare(
                    '
                        SELECT
                            id

                        FROM areas

                        WHERE slug = :slug

                        LIMIT 1
                    '
                );


            $stmtSlug->execute(
                [
                    ':slug' =>
                        $slug,
                ]
            );


            if (
                !$stmtSlug->fetchColumn()
            ) {

                break;
            }


            $slug =
                $slugBase .
                '-' .
                $contador;


            $contador++;
        }
    }


    /* =====================================================
       CADASTRA
    ====================================================== */

    if (empty($erros)) {

        try {

            $pdo->beginTransaction();


            /* =============================================
               INSERE ÁREA
            ============================================== */

            $sqlInsert =
                '
                    INSERT INTO areas
                    (
                        nome,
                        slug,
                        descricao,
                        icone,
                        ativo,
                        ordem,
                        criado_em,
                        atualizado_em
                    )
                    VALUES
                    (
                        :nome,
                        :slug,
                        :descricao,
                        :icone,
                        :ativo,
                        :ordem,
                        NOW(),
                        NOW()
                    )
                ';


            $stmtInsert =
                $pdo->prepare(
                    $sqlInsert
                );


            $stmtInsert->execute(
                [
                    ':nome' =>
                        $nome,

                    ':slug' =>
                        $slug,

                    ':descricao' =>
                        $descricao,

                    ':icone' =>
                        $icone,

                    ':ativo' =>
                        $ativo,

                    ':ordem' =>
                        $ordem,
                ]
            );


            $novaAreaId =
                (int) $pdo->lastInsertId();


            /* =============================================
               VINCULA AO USUÁRIO QUE CADASTROU

               Assim ele já terá acesso à nova área
               no Banco de Questões.
            ============================================== */

            $sqlVinculo =
                '
                    INSERT INTO usuario_areas
                    (
                        usuario_id,
                        area_id
                    )
                    VALUES
                    (
                        :usuario_id,
                        :area_id
                    )
                ';


            $stmtVinculo =
                $pdo->prepare(
                    $sqlVinculo
                );


            $stmtVinculo->execute(
                [
                    ':usuario_id' =>
                        $usuarioId,

                    ':area_id' =>
                        $novaAreaId,
                ]
            );


            $pdo->commit();


            /* =============================================
               ATUALIZA ÁREAS NA SESSÃO
            ============================================== */

            if (
                !isset(
                    $_SESSION['usuario']['areas']
                ) ||
                !is_array(
                    $_SESSION['usuario']['areas']
                )
            ) {

                $_SESSION['usuario']['areas'] =
                    [];
            }


            $_SESSION['usuario']['areas'][] = [

                'id' =>
                    $novaAreaId,

                'nome' =>
                    $nome,

                'slug' =>
                    $slug,

                'descricao' =>
                    $descricao,

                'icone' =>
                    $icone,
            ];


            /* =============================================
               MENSAGEM
            ============================================== */

            $_SESSION['sucesso_area'] =
                'Área "' .
                $nome .
                '" cadastrada com sucesso.';


            header(
                'Location: areas.php',
                true,
                303
            );

            exit;


        } catch (Throwable $erro) {


            if (
                $pdo->inTransaction()
            ) {

                $pdo->rollBack();
            }


            error_log(
                '[Cadastro de Área] ' .
                $erro->getMessage()
            );


            $erros[] =
                'Não foi possível cadastrar a área.';
        }
    }
}


/* =========================================================
   CONFIGURAÇÕES DA PÁGINA
========================================================= */

$siteSubtitulo =
    'Cadastrar Área';


$paginaTitulo =
    'Cadastrar Área - Desafio do Milhão';


$paginaDescricao =
    'Cadastre uma nova área no Desafio do Milhão.';


$paginaCss =
    $baseUrl .
    '/assets/css/area-formulario.css';


/* =========================================================
   HEADER
========================================================= */

require_once __DIR__ .
    '/../includes/header.php';

?>


<main class="pagina-formulario-area">


    <!-- =====================================================
         CABEÇALHO
    ====================================================== -->

    <section class="cabecalho-formulario-area">


        <img
            src="<?php
            echo escaparCadastroArea(
                $baseUrl
            );
            ?>/assets/img/logo/favicon.png"
            alt="Desafio do Milhão"
            class="logo-formulario-area"
        >


        <span class="rotulo-formulario-area">

            <i class="fa-solid fa-layer-group"></i>

            ÁREA ADMINISTRATIVA

        </span>


        <h1>
            CADASTRAR NOVA ÁREA
        </h1>


        <p>
            Crie uma nova área de conhecimento para o Desafio do Milhão.
        </p>


    </section>


    <!-- =====================================================
         IDENTIFICAÇÃO
    ====================================================== -->

    <section class="usuario-formulario-area">


        <div>

            <i class="fa-solid fa-user-shield"></i>

        </div>


        <span>

            <small>
                USUÁRIO RESPONSÁVEL
            </small>

            <strong>

                <?php
                echo escaparCadastroArea(
                    $nomeUsuario
                );
                ?>

            </strong>

        </span>


    </section>


    <!-- =====================================================
         ERROS
    ====================================================== -->

    <?php if (!empty($erros)): ?>


        <section class="erros-formulario-area">


            <i class="fa-solid fa-circle-exclamation"></i>


            <div>


                <strong>
                    VERIFIQUE OS DADOS
                </strong>


                <ul>


                    <?php foreach ($erros as $erro): ?>

                        <li>

                            <?php
                            echo escaparCadastroArea(
                                $erro
                            );
                            ?>

                        </li>

                    <?php endforeach; ?>


                </ul>


            </div>


        </section>


    <?php endif; ?>


    <!-- =====================================================
         FORMULÁRIO
    ====================================================== -->

    <section class="painel-formulario-area">


        <form
            action=""
            method="POST"
            autocomplete="off"
        >


            <!-- =================================================
                 NOME
            ================================================== -->

            <div class="grupo-form-area">


                <label for="nome">

                    <i class="fa-solid fa-font"></i>

                    Nome da área

                </label>


                <input
                    type="text"
                    id="nome"
                    name="nome"
                    maxlength="120"
                    value="<?php
                    echo escaparCadastroArea(
                        $nome
                    );
                    ?>"
                    placeholder="Ex.: Segurança da Informação"
                    required
                    autofocus
                >


                <small>
                    O slug será criado automaticamente a partir deste nome.
                </small>


            </div>


            <!-- =================================================
                 DESCRIÇÃO
            ================================================== -->

            <div class="grupo-form-area">


                <label for="descricao">

                    <i class="fa-solid fa-align-left"></i>

                    Descrição

                </label>


                <textarea
                    id="descricao"
                    name="descricao"
                    maxlength="500"
                    rows="4"
                    placeholder="Descreva os principais conteúdos dessa área."
                ><?php
                echo escaparCadastroArea(
                    $descricao
                );
                ?></textarea>


                <small>
                    Essa descrição será exibida no cartão de seleção da área.
                </small>


            </div>


            <!-- =================================================
                 DUAS COLUNAS
            ================================================== -->

            <div class="linha-form-area">


                <!-- ÍCONE -->

                <div class="grupo-form-area">


                    <label for="icone">

                        <i class="fa-solid fa-icons"></i>

                        Ícone Font Awesome

                    </label>


                    <input
                        type="text"
                        id="icone"
                        name="icone"
                        maxlength="120"
                        value="<?php
                        echo escaparCadastroArea(
                            $icone
                        );
                        ?>"
                        placeholder="fa-solid fa-shield-halved"
                    >


                    <small>
                        Exemplo: fa-solid fa-shield-halved
                    </small>


                </div>


                <!-- ORDEM -->

                <div class="grupo-form-area">


                    <label for="ordem">

                        <i class="fa-solid fa-arrow-down-1-9"></i>

                        Ordem de exibição

                    </label>


                    <input
                        type="number"
                        id="ordem"
                        name="ordem"
                        min="1"
                        max="999"
                        value="<?php
                        echo $ordem;
                        ?>"
                        required
                    >


                    <small>
                        Define a posição da área na tela de seleção.
                    </small>


                </div>


            </div>


            <!-- =================================================
                 STATUS
            ================================================== -->

            <div class="grupo-form-area">


                <label class="titulo-status-area">

                    <i class="fa-solid fa-toggle-on"></i>

                    Status da área

                </label>


                <label class="controle-status-area">


                    <input
                        type="checkbox"
                        name="ativo"
                        value="1"
                        <?php
                        echo $ativo === 1
                            ? 'checked'
                            : '';
                        ?>
                    >


                    <span class="switch-area"></span>


                    <span class="texto-status-area">

                        <strong>
                            ÁREA ATIVA
                        </strong>

                        <small>
                            Quando ativa, a área fica disponível para seleção no jogo.
                        </small>

                    </span>


                </label>


            </div>


            <!-- =================================================
                 AVISO
            ================================================== -->

            <div class="aviso-formulario-area">


                <i class="fa-solid fa-circle-info"></i>


                <span>
                    A nova área será vinculada automaticamente ao seu usuário para que você possa cadastrar questões nela.
                </span>


            </div>


            <!-- =================================================
                 BOTÕES
            ================================================== -->

            <div class="acoes-formulario-area">


                <a
                    href="areas.php"
                    class="botao-cancelar-area"
                >

                    <i class="fa-solid fa-arrow-left"></i>

                    CANCELAR

                </a>


                <button
                    type="submit"
                    class="botao-salvar-area"
                >

                    <i class="fa-solid fa-floppy-disk"></i>

                    CADASTRAR ÁREA

                </button>


            </div>


        </form>


    </section>


</main>


<?php

require_once __DIR__ .
    '/../includes/footer.php';

?>


</body>
</html>