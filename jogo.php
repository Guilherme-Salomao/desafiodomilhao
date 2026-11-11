<?php

declare(strict_types=1);

/* =========================================================
   SESSÃO
========================================================= */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/* =========================================================
   REPOSITORY
========================================================= */

require_once __DIR__ .
    '/includes/repositories/PerguntaRepository.php';

/* =========================================================
   PARTIDA
========================================================= */

$partida =
    $_SESSION['partida']
    ?? null;

if (
    !is_array($partida) ||
    empty($partida['area_id'])
) {

    header(
        'Location: /github/desafiodomilhao/public/',
        true,
        303
    );

    exit;
}

/* =========================================================
   DADOS DA PARTIDA
========================================================= */

$areaId =
    (int) (
        $partida['area_id']
        ?? 0
    );

$areaNome =
    (string) (
        $partida['area_nome']
        ?? 'Área'
    );

$dificuldadePartida =
    (string) (
        $partida['dificuldade']
        ?? 'facil'
    );

$numeroPergunta =
    (int) (
        $partida['pergunta_atual']
        ?? 1
    );

$quantidadePerguntas =
    (int) (
        $partida['quantidade_perguntas']
        ?? 10
    );

$tempoPergunta =
    (int) (
        $partida['tempo_pergunta']
        ?? 20
    );

/* =========================================================
   NOME DO JOGADOR
========================================================= */

$nomeJogador = '';

if (!empty($partida['nome_jogador'])) {

    $nomeJogador =
        trim(
            (string) $partida['nome_jogador']
        );

} elseif (!empty($partida['jogador_nome'])) {

    $nomeJogador =
        trim(
            (string) $partida['jogador_nome']
        );

} elseif (!empty($partida['jogador'])) {

    $nomeJogador =
        trim(
            (string) $partida['jogador']
        );

} elseif (!empty($partida['nome'])) {

    $nomeJogador =
        trim(
            (string) $partida['nome']
        );
}

/* =========================================================
   DIFICULDADE
========================================================= */

$dificuldade = 'facil';

if ($dificuldadePartida === 'dificil') {

    $dificuldade =
        'dificil';
}

/*
 * A sétima posição da escada equivale a R$ 100.000.
 * Deste prêmio em diante, a partida sempre utiliza
 * questões difíceis, independentemente do nível inicial.
 */
if ($numeroPergunta >= 7) {

    $dificuldade =
        'dificil';
}

/* =========================================================
   BUSCA PERGUNTA
========================================================= */

$repository =
    new PerguntaRepository();

/*
 * Evita repetições durante a partida atual e nas dez partidas
 * anteriores da mesma sessão do jogador.
 */
$historicoPartidas =
    $_SESSION['historico_partidas_perguntas']
    ?? [];

$idsHistorico = [];

if (is_array($historicoPartidas)) {
    foreach (array_slice($historicoPartidas, -10) as $perguntasPartida) {
        if (is_array($perguntasPartida)) {
            $idsHistorico = array_merge(
                $idsHistorico,
                array_map('intval', $perguntasPartida)
            );
        }
    }
}

$idsPartidaAtual = is_array($partida['perguntas_usadas'] ?? null)
    ? array_map('intval', $partida['perguntas_usadas'])
    : [];

$idPerguntaAtual = (int) ($partida['pergunta_atual_id'] ?? 0);

if ($idPerguntaAtual > 0) {
    $idsPartidaAtual[] = $idPerguntaAtual;
}

$idsIgnorar = array_values(
    array_unique(
        array_filter(
            array_merge($idsHistorico, $idsPartidaAtual),
            static fn (int $id): bool => $id > 0
        )
    )
);

$pergunta =
    $repository->buscarParaJogo(
        $areaId,
        $dificuldade,
        $idsIgnorar
    );

/* =========================================================
   RESPOSTA CORRETA
========================================================= */

$letraCorreta = '';

if (
    is_array($pergunta) &&
    isset($pergunta['alternativas']) &&
    is_array($pergunta['alternativas'])
) {

    foreach (
        $pergunta['alternativas']
        as $alternativa
    ) {

        if (
            (int) (
                $alternativa['correta']
                ?? 0
            ) === 1
        ) {

            $letraCorreta =
                (string) (
                    $alternativa['letra']
                    ?? ''
                );

            break;
        }
    }
}

/* =========================================================
   REGISTRA PERGUNTA ATUAL
========================================================= */

$_SESSION['partida']['pergunta_atual_id'] =
    (int) (
        $pergunta['id']
        ?? 0
    );

$_SESSION['partida']['resposta_correta_atual'] =
    $letraCorreta;

/* =========================================================
   IMAGEM DA PERGUNTA
========================================================= */

$imagemPergunta = '';

if (
    is_array($pergunta) &&
    !empty($pergunta['imagem'])
) {

    $imagemPergunta =
        trim(
            (string) $pergunta['imagem']
        );

    $ehHttp =
        strpos(
            $imagemPergunta,
            'http://'
        ) === 0;

    $ehHttps =
        strpos(
            $imagemPergunta,
            'https://'
        ) === 0;

    $ehAbsoluto =
        substr(
            $imagemPergunta,
            0,
            1
        ) === '/';

    if (
        !$ehHttp &&
        !$ehHttps &&
        !$ehAbsoluto
    ) {

        $imagemPergunta =
            '/github/desafiodomilhao/public/' .
            ltrim(
                $imagemPergunta,
                '/'
            );
    }
}

/* =========================================================
   AJUDAS
========================================================= */

$ajudas = [];

if (
    isset($partida['ajudas']) &&
    is_array($partida['ajudas'])
) {

    $ajudas =
        $partida['ajudas'];
}

/* =========================================================
   PREMIAÇÃO
========================================================= */

$premios = [
    1 => 'R$ 1.000',
    2 => 'R$ 2.000',
    3 => 'R$ 5.000',
    4 => 'R$ 10.000',
    5 => 'R$ 20.000',
    6 => 'R$ 50.000',
    7 => 'R$ 100.000',
    8 => 'R$ 200.000',
    9 => 'R$ 500.000',
    10 => 'R$ 1.000.000'
];

/* =========================================================
   CONFIGURAÇÃO DA PÁGINA
========================================================= */

$siteSubtitulo =
    $areaNome;

$paginaTitulo =
    'Jogo - Desafio do Milhão';

$paginaDescricao =
    'Partida em andamento no Desafio do Milhão.';

$paginaCss =
    '/github/desafiodomilhao/public/assets/css/jogo.css';

/* =========================================================
   HEADER
========================================================= */

require_once __DIR__ .
    '/includes/header.php';

?>

<main class="pagina-jogo">

    <!-- =====================================================
         LATERAL ESQUERDA
    ====================================================== -->

    <aside class="painel-jogo-lateral">

        <!-- LOGO -->

        <div class="marca-jogo">

            <img
                src="/github/desafiodomilhao/public/assets/img/logo/favicon.png"
                alt="Desafio do Milhão"
                class="logo-jogo"
            >

        </div>

        <!-- JOGADOR -->

        <div class="bloco-jogador-sidebar">

            <div class="info-jogador-sidebar">

                <?php if ($nomeJogador !== ''): ?>

                    <span class="nome-jogador-sidebar">

                        <?php echo escapar(
                            $nomeJogador
                        ); ?>

                    </span>

                <?php endif; ?>

                <span class="area-jogador-sidebar">

                    <?php echo escapar(
                        $areaNome
                    ); ?>

                </span>

            </div>

            <div class="icone-jogador-sidebar">

                <i class="fa-solid fa-user"></i>

            </div>

        </div>

        <!-- MENU -->

        <nav
            class="menu-jogo-lateral"
            aria-label="Menu do jogo"
        >

            <a
                href="#"
                class="ativo"
            >

                <span class="icone-menu-jogo">

                    <i class="fa-solid fa-play"></i>

                </span>

                <span class="texto-menu-jogo">

                    <strong>
                        JOGAR
                    </strong>

                    <small>
                        Responda e chegue ao milhão
                    </small>

                </span>

            </a>

            <a
                href="/github/desafiodomilhao/public/configurar-partida.php?modo=competicao"
            >

                <span class="icone-menu-jogo">

                    <i class="fa-solid fa-users"></i>

                </span>

                <span class="texto-menu-jogo">

                    <strong>
                        COMPETIÇÃO
                    </strong>

                    <small>
                        Jogue em equipes
                    </small>

                </span>

            </a>

            <a href="/github/desafiodomilhao/public/professor.php">

                <span class="icone-menu-jogo">

                    <i class="fa-solid fa-graduation-cap"></i>

                </span>

                <span class="texto-menu-jogo">

                    <strong>
                        MODO PROFESSOR
                    </strong>

                    <small>
                        Gerencie perguntas e turmas
                    </small>

                </span>

            </a>

            <a href="/github/desafiodomilhao/public/login.php">

                <span class="icone-menu-jogo">

                    <i class="fa-solid fa-database"></i>

                </span>

                <span class="texto-menu-jogo">

                    <strong>
                        BANCO DE QUESTÕES
                    </strong>

                    <small>
                        Categorias e níveis
                    </small>

                </span>

            </a>

<a href="/github/desafiodomilhao/public/ranking.php">

    <span class="icone-menu-jogo">
        <i class="fa-solid fa-trophy"></i>
    </span>

    <span class="texto-menu-jogo">

        <strong>
            RANKING
        </strong>

        <small>
            Veja os melhores jogadores
        </small>

    </span>

</a>

            <a href="/github/desafiodomilhao/public/configuracoes.php">

                <span class="icone-menu-jogo">

                    <i class="fa-solid fa-gear"></i>

                </span>

                <span class="texto-menu-jogo">

                    <strong>
                        CONFIGURAÇÕES
                    </strong>

                    <small>
                        Som, tempo e preferências
                    </small>

                </span>

            </a>

        </nav>

        <!-- SAIR -->

        <a
            href="/github/desafiodomilhao/public/"
            class="botao-sair-jogo"
        >

            <i class="fa-solid fa-arrow-left"></i>

            SAIR DA PARTIDA

        </a>

    </aside>

    <!-- =====================================================
         CENTRO
    ====================================================== -->

    <section class="palco-jogo">

        <!-- =================================================
             TOPO DO MOCKUP
        ================================================== -->

        <header class="topo-palco-jogo">

            <!-- AJUDAS -->

            <nav
                class="ajudas-jogo"
                aria-label="Ajudas da partida"
            >

                <button
                    type="button"
                    data-ajuda="cinquenta"
                    <?php echo
                        !empty(
                            $ajudas['cinquenta']
                        )
                            ? 'disabled'
                            : '';
                    ?>
                >

                    <span class="icone-ajuda">

                        <i class="fa-solid fa-scissors"></i>

                    </span>

                    <span class="texto-ajuda">
                        50/50
                    </span>

                </button>

                <button
                    type="button"
                    data-ajuda="tecnico"
                    <?php echo
                        !empty(
                            $ajudas['tecnico']
                        )
                            ? 'disabled'
                            : '';
                    ?>
                >

                    <span class="icone-ajuda">

                        <i class="fa-solid fa-user-gear"></i>

                    </span>

                    <span class="texto-ajuda">
                        Consultar o Técnico
                    </span>

                </button>

                <button
                    type="button"
                    data-ajuda="turma"
                    <?php echo
                        !empty(
                            $ajudas['turma']
                        )
                            ? 'disabled'
                            : '';
                    ?>
                >

                    <span class="icone-ajuda">

                        <i class="fa-solid fa-users"></i>

                    </span>

                    <span class="texto-ajuda">
                        Consultar a Turma
                    </span>

                </button>

                <button
                    type="button"
                    data-ajuda="trocar"
                    <?php echo
                        !empty(
                            $ajudas['trocar']
                        )
                            ? 'disabled'
                            : '';
                    ?>
                >

                    <span class="icone-ajuda">

                        <i class="fa-solid fa-arrows-rotate"></i>

                    </span>

                    <span class="texto-ajuda">
                        Trocar Pergunta
                    </span>

                </button>

            </nav>

            <!-- STATUS -->

            <div class="status-pergunta-jogo">

                <div class="contador-pergunta">

                    Pergunta

                    <?php echo $numeroPergunta; ?>

                    de

                    <?php echo $quantidadePerguntas; ?>

                </div>

                <div class="container-cronometro">

                    <strong
                        id="cronometroJogo"
                        class="cronometro-jogo"
                    >

                        <?php echo $tempoPergunta; ?>

                    </strong>

                </div>

            </div>

        </header>

        <!-- =================================================
             CONTEÚDO
        ================================================== -->

        <?php if ($pergunta === null): ?>

            <section class="estado-jogo-vazio">

                <i class="fa-solid fa-circle-exclamation"></i>

                <h1>
                    AINDA NÃO HÁ QUESTÕES DISPONÍVEIS
                </h1>

                <p>

                    Cadastre perguntas ativas de nível

                    <?php echo escapar(
                        $dificuldade
                    ); ?>

                    para esta área.

                </p>

                <a href="/github/desafiodomilhao/public/">

                    VOLTAR AO INÍCIO

                </a>

            </section>

        <?php else: ?>

            <!-- PERGUNTA -->

            <section class="cartao-pergunta-jogo">

                <div class="conteudo-pergunta-jogo">

                    <h1>

                        <?php echo escapar(
                            (string) (
                                $pergunta['pergunta']
                                ?? ''
                            )
                        ); ?>

                    </h1>

                    <?php if ($imagemPergunta !== ''): ?>

                        <div class="imagem-pergunta-jogo">

                            <img
                                src="<?php echo escapar(
                                    $imagemPergunta
                                ); ?>"
                                alt="Imagem relacionada à pergunta"
                            >

                        </div>

                    <?php endif; ?>

                </div>

            </section>

            <!-- ALTERNATIVAS -->

            <section
                class="alternativas-jogo"
                id="alternativasJogo"
                data-correta="<?php echo escapar(
                    $letraCorreta
                ); ?>"
            >

                <?php if (
                    isset($pergunta['alternativas']) &&
                    is_array($pergunta['alternativas'])
                ): ?>

                    <?php foreach (
                        $pergunta['alternativas']
                        as $alternativa
                    ): ?>

                        <button
                            type="button"
                            class="alternativa-jogo"
                            data-letra="<?php echo escapar(
                                (string) (
                                    $alternativa['letra']
                                    ?? ''
                                )
                            ); ?>"
                        >

                            <strong>

                                <?php echo escapar(
                                    (string) (
                                        $alternativa['letra']
                                        ?? ''
                                    )
                                ); ?>

                            </strong>

                            <span>

                                <?php echo escapar(
                                    (string) (
                                        $alternativa['texto']
                                        ?? ''
                                    )
                                ); ?>

                            </span>

                        </button>

                    <?php endforeach; ?>

                <?php endif; ?>

            </section>

            <!-- EXPLICAÇÃO -->

            <div
                id="explicacaoPerguntaJogo"
                hidden
            >

                <?php echo escapar(
                    (string) (
                        $pergunta['explicacao']
                        ?? ''
                    )
                ); ?>

            </div>

            <!-- RESULTADO -->

            <section
                class="resultado-jogo"
                id="resultadoJogo"
                hidden
            ></section>

            <!-- AJUDA -->

            <section
                class="painel-ajuda-jogo"
                id="painelAjudaJogo"
                hidden
            ></section>

        <?php endif; ?>

    </section>

    <!-- =====================================================
         PRÊMIOS
    ====================================================== -->

    <aside class="painel-premios">

        <ol>

            <?php foreach (
                array_reverse(
                    $premios,
                    true
                )
                as $numero => $valor
            ): ?>

                <li
                    class="<?php echo
                        $numero === $numeroPergunta
                            ? 'atual'
                            : '';
                    ?>"
                >

                    <strong>

                        <?php echo $numero; ?>

                    </strong>

                    <span>

                        <?php echo escapar(
                            $valor
                        ); ?>

                    </span>

                </li>

            <?php endforeach; ?>

        </ol>

    </aside>

</main>

<?php

require_once __DIR__ .
    '/includes/footer.php';

?>

<?php

$arquivoJs =
    __DIR__ .
    '/public/assets/js/jogo.js';

$versaoJs =
    is_file($arquivoJs)
        ? (int) filemtime(
            $arquivoJs
        )
        : 1;

?>

<script
    src="/github/desafiodomilhao/public/assets/js/jogo.js?v=<?php echo $versaoJs; ?>"
></script>

</body>
</html>
