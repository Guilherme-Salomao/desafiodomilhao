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
   FUNÇÃO ESCAPE
============================================================ */

function escaparRanking(string $valor): string
{
    return htmlspecialchars(
        $valor,
        ENT_QUOTES,
        'UTF-8'
    );
}


/* ============================================================
   FORMATAR DINHEIRO
============================================================ */

function formatarPremioRanking(float $valor): string
{
    return 'R$ ' .
        number_format(
            $valor,
            0,
            ',',
            '.'
        );
}


/* ============================================================
   FORMATAR TEMPO

   Banco armazena em milissegundos.
============================================================ */

function formatarTempoRanking(int $milissegundos): string
{
    if ($milissegundos < 0) {
        $milissegundos = 0;
    }

    $minutos =
        intdiv(
            $milissegundos,
            60000
        );

    $restante =
        $milissegundos % 60000;

    $segundos =
        intdiv(
            $restante,
            1000
        );

    $ms =
        $restante % 1000;

    return sprintf(
        '%02d:%02d.%03d',
        $minutos,
        $segundos,
        $ms
    );
}


/* ============================================================
   TEXTO DA DIFICULDADE
============================================================ */

function dificuldadeRanking(string $dificuldade): string
{
    return match ($dificuldade) {

        'facil' =>
            'Fácil',

        'medio' =>
            'Média',

        'mista' =>
            'Mista',

        'dificil' =>
            'Difícil',

        default =>
            ucfirst($dificuldade),
    };
}


/* ============================================================
   TEXTO DO RESULTADO
============================================================ */

function resultadoRanking(string $resultado): string
{
    return match ($resultado) {

        'vencedor' =>
            'VENCEDOR',

        'game_over' =>
            'GAME OVER',

        default =>
            strtoupper(
                str_replace(
                    '_',
                    ' ',
                    $resultado
                )
            ),
    };
}


/* ============================================================
   ÁREA PARA FILTRO
============================================================ */

$areaFiltro =
    filter_input(
        INPUT_GET,
        'area',
        FILTER_VALIDATE_INT
    );


if (
    $areaFiltro === false ||
    $areaFiltro === null ||
    $areaFiltro <= 0
) {
    $areaFiltro = null;
}


/* ============================================================
   BUSCA ÁREAS
============================================================ */

$stmtAreas =
    $pdo->query(
        '
            SELECT
                id,
                nome
            FROM areas
            WHERE ativo = 1
            ORDER BY
                ordem ASC,
                nome ASC
        '
    );


$areas =
    $stmtAreas->fetchAll(
        PDO::FETCH_ASSOC
    );


/* ============================================================
   RANKING

   Critérios:
   1. Maior prêmio
   2. Menos ajudas
   3. Menor tempo
   4. Resultado mais antigo
============================================================ */

$sqlRanking = '
    SELECT
        r.id,
        r.nome_jogador,
        r.modo,
        r.dificuldade,
        r.quantidade_perguntas,
        r.pergunta_final,
        r.perguntas_respondidas,
        r.acertos,
        r.erros,
        r.tempo_total_ms,
        r.ajudas_usadas,
        r.premio,
        r.resultado,
        r.concluida,
        r.criado_em,

        a.id AS area_id,
        a.nome AS area_nome,
        a.icone AS area_icone

    FROM resultados r

    INNER JOIN areas a
        ON a.id = r.area_id

    WHERE 1 = 1
';


$parametros =
    [];


if ($areaFiltro !== null) {

    $sqlRanking .= '
        AND r.area_id = :area_id
    ';

    $parametros[':area_id'] =
        $areaFiltro;
}


$sqlRanking .= '
    ORDER BY
        r.premio DESC,
        r.ajudas_usadas ASC,
        r.tempo_total_ms ASC,
        r.criado_em ASC

    LIMIT 100
';


$stmtRanking =
    $pdo->prepare(
        $sqlRanking
    );


$stmtRanking->execute(
    $parametros
);


$ranking =
    $stmtRanking->fetchAll(
        PDO::FETCH_ASSOC
    );


/* ============================================================
   ESTATÍSTICAS
============================================================ */

$totalResultados =
    count(
        $ranking
    );


$totalVencedores =
    0;


$maiorPremio =
    0.0;


foreach ($ranking as $registro) {

    if (
        (string) (
            $registro['resultado']
            ?? ''
        ) === 'vencedor'
    ) {
        $totalVencedores++;
    }


    $premioAtual =
        (float) (
            $registro['premio']
            ?? 0
        );


    if ($premioAtual > $maiorPremio) {

        $maiorPremio =
            $premioAtual;
    }
}


/* ============================================================
   CONFIGURAÇÕES DA PÁGINA
============================================================ */

$siteSubtitulo =
    'Ranking';


$paginaTitulo =
    'Ranking - Desafio do Milhão';


$paginaDescricao =
    'Confira os melhores resultados do Desafio do Milhão.';


$paginaTipo =
    'website';


$paginaCss =
    urlProjeto(
        'assets/css/ranking.css'
    );


/* ============================================================
   HEADER
============================================================ */

require_once __DIR__ . '/includes/header.php';

?>


<main class="pagina-ranking">


    <!-- =====================================================
         CABEÇALHO
    ====================================================== -->

    <section class="cabecalho-ranking">


        <img
            src="<?php
            echo escaparRanking(
                urlProjeto(
                    'assets/img/logo/favicon.png'
                )
            );
            ?>"
            alt="Desafio do Milhão"
            class="logo-ranking"
        >


        <span class="rotulo-ranking">

            <i class="fa-solid fa-trophy"></i>

            CLASSIFICAÇÃO

        </span>


        <h1>
            RANKING
        </h1>


        <p>
            Os melhores resultados do Desafio do Milhão.
        </p>


    </section>


    <!-- =====================================================
         ESTATÍSTICAS
    ====================================================== -->

    <section class="grade-estatisticas-ranking">


        <article class="card-estatistica-ranking">


            <div class="icone-estatistica-ranking">

                <i class="fa-solid fa-ranking-star"></i>

            </div>


            <div>

                <span>
                    PARTICIPANTES
                </span>

                <strong>
                    <?php echo $totalResultados; ?>
                </strong>

            </div>


        </article>


        <article class="card-estatistica-ranking">


            <div class="icone-estatistica-ranking vencedor">

                <i class="fa-solid fa-crown"></i>

            </div>


            <div>

                <span>
                    VENCEDORES
                </span>

                <strong>
                    <?php echo $totalVencedores; ?>
                </strong>

            </div>


        </article>


        <article class="card-estatistica-ranking">


            <div class="icone-estatistica-ranking premio">

                <i class="fa-solid fa-sack-dollar"></i>

            </div>


            <div>

                <span>
                    MAIOR PRÊMIO
                </span>

                <strong>
                    <?php
                    echo formatarPremioRanking(
                        $maiorPremio
                    );
                    ?>
                </strong>

            </div>


        </article>


    </section>


    <!-- =====================================================
         FILTRO
    ====================================================== -->

    <section class="painel-filtro-ranking">


        <form
            method="GET"
            action="<?php
            echo escaparRanking(
                urlProjeto(
                    'ranking.php'
                )
            );
            ?>"
        >


            <div class="campo-filtro-ranking">


                <label for="area">

                    <i class="fa-solid fa-layer-group"></i>

                    Área

                </label>


                <select
                    name="area"
                    id="area"
                >


                    <option value="">
                        Todas as áreas
                    </option>


                    <?php foreach ($areas as $area): ?>


                        <?php

                        $idArea =
                            (int) (
                                $area['id']
                                ?? 0
                            );

                        ?>


                        <option
                            value="<?php echo $idArea; ?>"
                            <?php
                            echo $areaFiltro === $idArea
                                ? 'selected'
                                : '';
                            ?>
                        >

                            <?php
                            echo escaparRanking(
                                (string) (
                                    $area['nome']
                                    ?? ''
                                )
                            );
                            ?>

                        </option>


                    <?php endforeach; ?>


                </select>


            </div>


            <button
                type="submit"
                class="botao-filtrar-ranking"
            >

                <i class="fa-solid fa-filter"></i>

                FILTRAR

            </button>


            <?php if ($areaFiltro !== null): ?>

                <a
                    href="<?php
                    echo escaparRanking(
                        urlProjeto(
                            'ranking.php'
                        )
                    );
                    ?>"
                    class="botao-limpar-ranking"
                >

                    <i class="fa-solid fa-eraser"></i>

                    LIMPAR

                </a>

            <?php endif; ?>


        </form>


    </section>


    <!-- =====================================================
         RANKING
    ====================================================== -->

    <section class="painel-ranking">


        <div class="cabecalho-lista-ranking">


            <div>

                <span>
                    CLASSIFICAÇÃO GERAL
                </span>

                <h2>
                    Melhores jogadores
                </h2>

            </div>


            <span class="quantidade-ranking">

                <?php echo $totalResultados; ?>

                resultado(s)

            </span>


        </div>


        <?php if (empty($ranking)): ?>


            <div class="ranking-vazio">


                <i class="fa-solid fa-trophy"></i>


                <h3>
                    NENHUM RESULTADO ENCONTRADO
                </h3>


                <p>
                    Ainda não existem partidas registradas para este filtro.
                </p>


            </div>


        <?php else: ?>


            <div class="tabela-ranking-wrapper">


                <table class="tabela-ranking">


                    <thead>


                        <tr>

                            <th>
                                POSIÇÃO
                            </th>

                            <th>
                                JOGADOR
                            </th>

                            <th>
                                ÁREA
                            </th>

                            <th>
                                PRÊMIO
                            </th>

                            <th>
                                AJUDAS
                            </th>

                            <th>
                                TEMPO
                            </th>

                            <th>
                                ACERTOS
                            </th>

                            <th>
                                DIFICULDADE
                            </th>

                            <th>
                                RESULTADO
                            </th>

                        </tr>


                    </thead>


                    <tbody>


                        <?php foreach ($ranking as $indice => $registro): ?>


                            <?php

                            $posicao =
                                $indice + 1;


                            $classePosicao =
                                '';

                            if ($posicao === 1) {

                                $classePosicao =
                                    ' primeiro';

                            } elseif ($posicao === 2) {

                                $classePosicao =
                                    ' segundo';

                            } elseif ($posicao === 3) {

                                $classePosicao =
                                    ' terceiro';
                            }


                            $resultado =
                                (string) (
                                    $registro['resultado']
                                    ?? ''
                                );


                            $classeResultado =
                                $resultado === 'vencedor'
                                    ? 'vencedor'
                                    : 'game-over';

                            ?>


                            <tr>


                                <td>


                                    <span class="posicao-ranking<?php echo $classePosicao; ?>">


                                        <?php if ($posicao === 1): ?>

                                            <i class="fa-solid fa-crown"></i>

                                        <?php endif; ?>


                                        <?php echo $posicao; ?>º


                                    </span>


                                </td>


                                <td>


                                    <strong class="nome-jogador-ranking">

                                        <?php
                                        echo escaparRanking(
                                            (string) (
                                                $registro['nome_jogador']
                                                ?? 'Jogador'
                                            )
                                        );
                                        ?>

                                    </strong>


                                </td>


                                <td>


                                    <span class="area-ranking">


                                        <i
                                            class="<?php
                                            echo escaparRanking(
                                                (string) (
                                                    $registro['area_icone']
                                                    ?? 'fa-solid fa-layer-group'
                                                )
                                            );
                                            ?>"
                                        ></i>


                                        <?php
                                        echo escaparRanking(
                                            (string) (
                                                $registro['area_nome']
                                                ?? 'Área'
                                            )
                                        );
                                        ?>


                                    </span>


                                </td>


                                <td>


                                    <strong class="premio-ranking">

                                        <?php
                                        echo formatarPremioRanking(
                                            (float) (
                                                $registro['premio']
                                                ?? 0
                                            )
                                        );
                                        ?>

                                    </strong>


                                </td>


                                <td>

                                    <?php
                                    echo (int) (
                                        $registro['ajudas_usadas']
                                        ?? 0
                                    );
                                    ?>

                                </td>


                                <td>


                                    <span class="tempo-ranking">

                                        <?php
                                        echo formatarTempoRanking(
                                            (int) (
                                                $registro['tempo_total_ms']
                                                ?? 0
                                            )
                                        );
                                        ?>

                                    </span>


                                </td>


                                <td>


                                    <span class="acertos-ranking">

                                        <?php
                                        echo (int) (
                                            $registro['acertos']
                                            ?? 0
                                        );
                                        ?>

                                    </span>


                                </td>


                                <td>


                                    <span class="dificuldade-ranking">


                                        <?php
                                        echo escaparRanking(
                                            dificuldadeRanking(
                                                (string) (
                                                    $registro['dificuldade']
                                                    ?? ''
                                                )
                                            )
                                        );
                                        ?>


                                    </span>


                                </td>


                                <td>


                                    <span
                                        class="resultado-ranking <?php echo $classeResultado; ?>"
                                    >

                                        <?php
                                        echo escaparRanking(
                                            resultadoRanking(
                                                $resultado
                                            )
                                        );
                                        ?>

                                    </span>


                                </td>


                            </tr>


                        <?php endforeach; ?>


                    </tbody>


                </table>


            </div>


        <?php endif; ?>


    </section>


    <!-- =====================================================
         VOLTAR
    ====================================================== -->

    <section class="acoes-ranking">


        <a
            href="<?php
            echo escaparRanking(
                urlProjeto()
            );
            ?>"
            class="botao-voltar-ranking"
        >

            <i class="fa-solid fa-arrow-left"></i>

            VOLTAR AO JOGO

        </a>


    </section>


</main>


<?php

require_once __DIR__ . '/includes/footer.php';

?>


</body>
</html>