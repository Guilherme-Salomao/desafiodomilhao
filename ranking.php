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
   FILTRO DE ÁREA
========================================================= */

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


/* =========================================================
   LISTA DE ÁREAS
========================================================= */

$sqlAreas = '
    SELECT
        id,
        nome
    FROM areas
    ORDER BY nome ASC
';

$stmtAreas =
    $pdo->query($sqlAreas);

$areas =
    $stmtAreas->fetchAll(
        PDO::FETCH_ASSOC
    );


/* =========================================================
   SQL DO RANKING
========================================================= */

$sql = '
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
        r.ajuda_cinquenta,
        r.ajuda_tecnico,
        r.ajuda_turma,
        r.ajuda_trocar,
        r.premio,
        r.resultado,
        r.concluida,
        r.criado_em,

        a.nome AS area_nome

    FROM resultados r

    INNER JOIN areas a
        ON a.id = r.area_id
';


/* =========================================================
   PARÂMETROS
========================================================= */

$parametros = [];


/* =========================================================
   FILTRO
========================================================= */

if ($areaFiltro !== null) {

    $sql .= '
        WHERE r.area_id = :area_id
    ';

    $parametros[':area_id'] =
        $areaFiltro;
}


/* =========================================================
   ORDENAÇÃO OFICIAL

   1. Maior prêmio
   2. Menor número de ajudas
   3. Menor tempo
   4. Registro mais antigo
========================================================= */

$sql .= '
    ORDER BY
        r.premio DESC,
        r.ajudas_usadas ASC,
        r.tempo_total_ms ASC,
        r.criado_em ASC
';


/* =========================================================
   LIMITE
========================================================= */

$sql .= '
    LIMIT 100
';


/* =========================================================
   EXECUTA CONSULTA
========================================================= */

$stmt =
    $pdo->prepare($sql);

$stmt->execute($parametros);

$ranking =
    $stmt->fetchAll(
        PDO::FETCH_ASSOC
    );


/* =========================================================
   FUNÇÃO ESCAPE
========================================================= */

function escaparRanking(string $valor): string
{
    return htmlspecialchars(
        $valor,
        ENT_QUOTES,
        'UTF-8'
    );
}


/* =========================================================
   FORMATA PRÊMIO
========================================================= */

function formatarPremioRanking(float $valor): string
{
    return
        'R$ ' .
        number_format(
            $valor,
            0,
            ',',
            '.'
        );
}


/* =========================================================
   FORMATA TEMPO
========================================================= */

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


/* =========================================================
   TEXTO DIFICULDADE
========================================================= */

function textoDificuldadeRanking(string $dificuldade): string
{
    return match ($dificuldade) {

        'facil' =>
            'Fácil',

        'mista' =>
            'Mista',

        'dificil' =>
            'Difícil',

        default =>
            ucfirst($dificuldade),
    };
}


/* =========================================================
   CONFIGURAÇÕES DA PÁGINA
========================================================= */

$siteSubtitulo =
    'Ranking';

$paginaTitulo =
    'Ranking - Desafio do Milhão';

$paginaDescricao =
    'Confira a classificação dos jogadores do Desafio do Milhão.';

$paginaCss =
    '/github/desafiodomilhao/public/assets/css/ranking.css';


/* =========================================================
   HEADER
========================================================= */

require_once __DIR__ . '/includes/header.php';

?>


<main class="pagina-ranking">


    <!-- =====================================================
         CABEÇALHO
    ====================================================== -->

    <section class="cabecalho-ranking">

        <img
            src="/github/desafiodomilhao/public/assets/img/logo/favicon.png"
            alt="Desafio do Milhão"
            class="logo-ranking"
        >

        <div>

            <span class="rotulo-ranking">

                <i class="fa-solid fa-trophy"></i>

                CLASSIFICAÇÃO

            </span>

            <h1>
                RANKING
            </h1>

            <p>
                Os melhores jogadores do Desafio do Milhão.
            </p>

        </div>

    </section>


    <!-- =====================================================
         REGRA
    ====================================================== -->

    <section class="regra-ranking">

        <i class="fa-solid fa-circle-info"></i>

        <div>

            <strong>
                Critério de classificação
            </strong>

            <span>
                Maior prêmio, menor quantidade de ajudas e menor tempo total.
            </span>

        </div>

    </section>


    <!-- =====================================================
         FILTRO
    ====================================================== -->

    <section class="filtro-ranking">

        <form
            action=""
            method="GET"
        >

            <label for="areaRanking">

                <i class="fa-solid fa-layer-group"></i>

                Área

            </label>

            <select
                name="area"
                id="areaRanking"
                onchange="this.form.submit()"
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
                        echo (
                            $areaFiltro === $idArea
                        )
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

        </form>

    </section>


    <!-- =====================================================
         PÓDIO
    ====================================================== -->

    <?php if (!empty($ranking)): ?>

        <section class="podio-ranking">

            <?php

            $topTres =
                array_slice(
                    $ranking,
                    0,
                    3
                );

            ?>

            <?php foreach ($topTres as $indice => $jogador): ?>

                <?php

                $posicao =
                    $indice + 1;

                ?>

                <article
                    class="card-podio posicao-<?php echo $posicao; ?>"
                >

                    <div class="numero-podio">

                        <?php if ($posicao === 1): ?>

                            <i class="fa-solid fa-crown"></i>

                        <?php else: ?>

                            <?php echo $posicao; ?>º

                        <?php endif; ?>

                    </div>


                    <strong class="nome-podio">

                        <?php
                        echo escaparRanking(
                            (string) (
                                $jogador['nome_jogador']
                                ?? 'Jogador'
                            )
                        );
                        ?>

                    </strong>


                    <span class="area-podio">

                        <?php
                        echo escaparRanking(
                            (string) (
                                $jogador['area_nome']
                                ?? ''
                            )
                        );
                        ?>

                    </span>


                    <span class="premio-podio">

                        <?php
                        echo formatarPremioRanking(
                            (float) (
                                $jogador['premio']
                                ?? 0
                            )
                        );
                        ?>

                    </span>


                    <div class="dados-podio">

                        <span>

                            <i class="fa-solid fa-clock"></i>

                            <?php
                            echo formatarTempoRanking(
                                (int) (
                                    $jogador['tempo_total_ms']
                                    ?? 0
                                )
                            );
                            ?>

                        </span>


                        <span>

                            <i class="fa-solid fa-life-ring"></i>

                            <?php
                            echo (int) (
                                $jogador['ajudas_usadas']
                                ?? 0
                            );
                            ?>

                            ajuda(s)

                        </span>

                    </div>

                </article>

            <?php endforeach; ?>

        </section>

    <?php endif; ?>


    <!-- =====================================================
         RANKING
    ====================================================== -->

    <section class="container-ranking">


        <?php if (empty($ranking)): ?>


            <div class="ranking-vazio">

                <i class="fa-solid fa-trophy"></i>

                <h2>
                    Nenhum resultado registrado
                </h2>

                <p>
                    Finalize uma partida para aparecer no ranking.
                </p>

            </div>


        <?php else: ?>


            <div class="tabela-ranking-wrapper">

                <table class="tabela-ranking">

                    <thead>

                        <tr>

                            <th>
                                POS.
                            </th>

                            <th>
                                JOGADOR
                            </th>

                            <th>
                                ÁREA
                            </th>

                            <th>
                                DIFICULDADE
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
                                RESULTADO
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                        <?php foreach ($ranking as $indice => $jogador): ?>


                            <?php

                            $posicao =
                                $indice + 1;

                            $resultadoPartida =
                                (string) (
                                    $jogador['resultado']
                                    ?? 'game_over'
                                );

                            ?>


                            <tr>


                                <td class="coluna-posicao">


                                    <?php if ($posicao === 1): ?>


                                        <span class="medalha ouro">

                                            <i class="fa-solid fa-crown"></i>

                                        </span>


                                    <?php elseif ($posicao === 2): ?>


                                        <span class="medalha prata">

                                            <i class="fa-solid fa-medal"></i>

                                        </span>


                                    <?php elseif ($posicao === 3): ?>


                                        <span class="medalha bronze">

                                            <i class="fa-solid fa-medal"></i>

                                        </span>


                                    <?php else: ?>


                                        <strong>

                                            <?php echo $posicao; ?>º

                                        </strong>


                                    <?php endif; ?>


                                </td>


                                <td class="coluna-jogador">

                                    <strong>

                                        <?php
                                        echo escaparRanking(
                                            (string) (
                                                $jogador['nome_jogador']
                                                ?? 'Jogador'
                                            )
                                        );
                                        ?>

                                    </strong>


                                    <small>

                                        <?php
                                        echo (int) (
                                            $jogador['acertos']
                                            ?? 0
                                        );
                                        ?>

                                        acerto(s)

                                    </small>

                                </td>


                                <td>

                                    <?php
                                    echo escaparRanking(
                                        (string) (
                                            $jogador['area_nome']
                                            ?? ''
                                        )
                                    );
                                    ?>

                                </td>


                                <td>

                                    <span
                                        class="tag-dificuldade dificuldade-<?php
                                        echo escaparRanking(
                                            (string) (
                                                $jogador['dificuldade']
                                                ?? ''
                                            )
                                        );
                                        ?>"
                                    >

                                        <?php
                                        echo escaparRanking(
                                            textoDificuldadeRanking(
                                                (string) (
                                                    $jogador['dificuldade']
                                                    ?? ''
                                                )
                                            )
                                        );
                                        ?>

                                    </span>

                                </td>


                                <td class="coluna-premio">

                                    <strong>

                                        <?php
                                        echo formatarPremioRanking(
                                            (float) (
                                                $jogador['premio']
                                                ?? 0
                                            )
                                        );
                                        ?>

                                    </strong>

                                </td>


                                <td>

                                    <span class="quantidade-ajudas">

                                        <i class="fa-solid fa-life-ring"></i>

                                        <?php
                                        echo (int) (
                                            $jogador['ajudas_usadas']
                                            ?? 0
                                        );
                                        ?>

                                    </span>

                                </td>


                                <td class="coluna-tempo">

                                    <i class="fa-solid fa-stopwatch"></i>

                                    <?php
                                    echo formatarTempoRanking(
                                        (int) (
                                            $jogador['tempo_total_ms']
                                            ?? 0
                                        )
                                    );
                                    ?>

                                </td>


                                <td>


                                    <?php if ($resultadoPartida === 'vencedor'): ?>


                                        <span class="resultado-ranking vencedor">

                                            <i class="fa-solid fa-trophy"></i>

                                            VENCEDOR

                                        </span>


                                    <?php else: ?>


                                        <span class="resultado-ranking game-over">

                                            GAME OVER

                                        </span>


                                    <?php endif; ?>


                                </td>


                            </tr>


                        <?php endforeach; ?>


                    </tbody>

                </table>

            </div>


        <?php endif; ?>


    </section>


    <!-- =====================================================
         AÇÕES
    ====================================================== -->

    <section class="acoes-ranking">


        <a
            href="/github/desafiodomilhao/public/"
            class="botao-voltar-ranking"
        >

            <i class="fa-solid fa-arrow-left"></i>

            VOLTAR AO INÍCIO

        </a>


        <a
            href="/github/desafiodomilhao/public/configurar-partida.php?modo=individual"
            class="botao-jogar-ranking"
        >

            <i class="fa-solid fa-play"></i>

            NOVA PARTIDA

        </a>


    </section>


</main>


<?php

require_once __DIR__ . '/includes/footer.php';

?>


</body>

</html>