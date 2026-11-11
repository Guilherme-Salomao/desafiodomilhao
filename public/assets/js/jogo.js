document.addEventListener("DOMContentLoaded", function () {

    /* =====================================================
       ELEMENTOS
    ===================================================== */

    const cronometro =
        document.getElementById("cronometroJogo");

    const alternativas =
        document.getElementById("alternativasJogo");

    const resultado =
        document.getElementById("resultadoJogo");

    const painelAjuda =
        document.getElementById("painelAjudaJogo");

    const explicacao =
        document.getElementById("explicacaoPerguntaJogo");

    const tituloPergunta =
        document.querySelector(
            ".conteudo-pergunta-jogo h1"
        );

    const cartaoPergunta =
        document.querySelector(
            ".cartao-pergunta-jogo"
        );

    const contadorPergunta =
        document.querySelector(
            ".contador-pergunta"
        );

    const menuLateral =
        document.querySelector(
            ".menu-jogo-lateral"
        );

    const linksMenu =
        document.querySelectorAll(
            ".menu-jogo-lateral a"
        );

    const botoesAjuda =
        document.querySelectorAll(
            "[data-ajuda]"
        );

    const botaoSair =
        document.querySelector(
            ".botao-sair-jogo"
        );

    const botaoJogar =
        document.querySelector(
            ".menu-jogo-lateral a.ativo"
        );


    /* =====================================================
       STORAGE
    ===================================================== */

    const chaveHistorico =
        "desafioMilhaoHistoricoAjudas";

    const chaveEstadoPartida =
        "desafioMilhaoEstadoPartida";

    const chaveTempoPartida =
        "desafioMilhaoTempoPartida";


    /* =====================================================
       ESTADO
    ===================================================== */

    let respondida = false;

    let partidaEncerrada = false;

    let aguardandoProxima = false;

    let intervaloCronometro = null;

    let inicioQuestaoMs = null;


    /* =====================================================
       BASE URL
    ===================================================== */

    function obterBaseUrl() {

        return (
            window.APP_BASE_URL ||
            "/github/desafiodomilhao/public"
        );
    }


    /* =====================================================
       ESCAPE HTML
    ===================================================== */

    function escaparHtml(texto) {

        const elemento =
            document.createElement("div");

        elemento.textContent =
            String(texto ?? "");

        return elemento.innerHTML;
    }


    /* =====================================================
       DADOS DA PERGUNTA
    ===================================================== */

    function obterDadosPergunta() {

        if (!contadorPergunta) {

            return {
                atual: 1,
                total: 10
            };
        }

        const texto =
            contadorPergunta.textContent
                .replace(/\s+/g, " ")
                .trim();

        const correspondencia =
            texto.match(
                /pergunta\s+(\d+)\s+de\s+(\d+)/i
            );

        if (!correspondencia) {

            return {
                atual: 1,
                total: 10
            };
        }

        return {

            atual:
                Number(
                    correspondencia[1]
                ) || 1,

            total:
                Number(
                    correspondencia[2]
                ) || 10
        };
    }


    /* =====================================================
       PRÊMIOS
    ===================================================== */

    function obterPremios() {

        const premios = {};

        document
            .querySelectorAll(
                ".painel-premios li"
            )
            .forEach(
                function (item) {

                    const numero =
                        item.querySelector(
                            "strong"
                        );

                    const valor =
                        item.querySelector(
                            "span"
                        );

                    if (
                        !numero ||
                        !valor
                    ) {
                        return;
                    }

                    premios[
                        Number(
                            numero.textContent.trim()
                        )
                    ] =
                        valor.textContent.trim();
                }
            );

        return premios;
    }


    function calcularPremioErro() {

        const dados =
            obterDadosPergunta();

        if (dados.atual <= 1) {

            return "R$ 0";
        }

        const premios =
            obterPremios();

        return (
            premios[
                dados.atual - 1
            ] ||
            "R$ 0"
        );
    }


    function calcularPremioAtual() {

        const dados =
            obterDadosPergunta();

        const premios =
            obterPremios();

        return (
            premios[
                dados.atual
            ] ||
            "R$ 0"
        );
    }


    function converterPremioParaNumero(
        valor
    ) {

        const numero =
            String(
                valor ?? ""
            ).replace(
                /[^0-9]/g,
                ""
            );

        return numero
            ? Number(numero)
            : 0;
    }


    /* =====================================================
       HISTÓRICO DAS AJUDAS
    ===================================================== */

    function carregarHistorico() {

        try {

            const dados =
                sessionStorage.getItem(
                    chaveHistorico
                );

            if (!dados) {
                return [];
            }

            const historico =
                JSON.parse(dados);

            return Array.isArray(
                historico
            )
                ? historico
                : [];

        } catch (erro) {

            console.error(
                "Erro ao recuperar histórico:",
                erro
            );

            return [];
        }
    }


    function salvarHistorico(
        historico
    ) {

        try {

            sessionStorage.setItem(
                chaveHistorico,
                JSON.stringify(
                    historico
                )
            );

        } catch (erro) {

            console.error(
                "Erro ao salvar histórico:",
                erro
            );
        }
    }


    function limparHistorico() {

        sessionStorage.removeItem(
            chaveHistorico
        );
    }


    function salvarAjuda(
        registro
    ) {

        const historico =
            carregarHistorico();

        const indice =
            historico.findIndex(
                function (item) {

                    return (
                        item.tipo ===
                        registro.tipo
                    );
                }
            );

        if (indice >= 0) {

            historico[indice] =
                registro;

        } else {

            historico.push(
                registro
            );
        }

        salvarHistorico(
            historico
        );
    }


    function ajudaFoiUtilizada(
        tipo
    ) {

        return carregarHistorico()
            .some(
                function (registro) {

                    return (
                        registro.tipo ===
                        tipo
                    );
                }
            );
    }


    function obterResumoAjudas() {

        const historico =
            carregarHistorico();

        const tipos =
            new Set(
                historico
                    .map(
                        function (item) {

                            return item.tipo;
                        }
                    )
                    .filter(Boolean)
            );

        return {

            quantidade:
                tipos.size,

            cinquenta:
                tipos.has(
                    "cinquenta"
                ),

            tecnico:
                tipos.has(
                    "tecnico"
                ),

            turma:
                tipos.has(
                    "turma"
                ),

            trocar:
                tipos.has(
                    "trocar"
                )
        };
    }


    /* =====================================================
       ESTADO PERSISTENTE
    ===================================================== */

    function salvarEstadoPartida(
        estado
    ) {

        try {

            sessionStorage.setItem(
                chaveEstadoPartida,
                JSON.stringify(
                    estado
                )
            );

        } catch (erro) {

            console.error(
                "Erro ao salvar estado da partida:",
                erro
            );
        }
    }


    function carregarEstadoPartida() {

        try {

            const dados =
                sessionStorage.getItem(
                    chaveEstadoPartida
                );

            if (!dados) {

                return null;
            }

            const estado =
                JSON.parse(dados);

            return (
                estado &&
                typeof estado ===
                "object"
            )
                ? estado
                : null;

        } catch (erro) {

            console.error(
                "Erro ao recuperar estado:",
                erro
            );

            return null;
        }
    }


    function limparEstadoPartida() {

        sessionStorage.removeItem(
            chaveEstadoPartida
        );
    }


    /* =====================================================
       TEMPO DA PARTIDA
    ===================================================== */

    function carregarTempoPartida() {

        const valor =
            Number(
                sessionStorage.getItem(
                    chaveTempoPartida
                ) || 0
            );

        if (
            !Number.isFinite(
                valor
            )
        ) {

            return 0;
        }

        return Math.max(
            0,
            Math.round(valor)
        );
    }


    function salvarTempoPartida(
        tempo
    ) {

        sessionStorage.setItem(
            chaveTempoPartida,
            String(
                Math.max(
                    0,
                    Math.round(
                        tempo
                    )
                )
            )
        );
    }


    function limparTempoPartida() {

        sessionStorage.removeItem(
            chaveTempoPartida
        );

        inicioQuestaoMs =
            null;
    }


    function iniciarTempoQuestao() {

        if (
            partidaEncerrada ||
            respondida
        ) {

            return;
        }

        inicioQuestaoMs =
            Date.now();
    }


    function consolidarTempoQuestao() {

        if (
            inicioQuestaoMs ===
            null
        ) {

            return carregarTempoPartida();
        }

        const agora =
            Date.now();

        const decorrido =
            Math.max(
                0,
                agora -
                inicioQuestaoMs
            );

        const total =
            carregarTempoPartida() +
            decorrido;

        salvarTempoPartida(
            total
        );

        inicioQuestaoMs =
            null;

        return total;
    }


    /* =====================================================
       SNAPSHOT DA PERGUNTA FINAL
    ===================================================== */

    function capturarPerguntaAtual() {

        return {

            contador:
                contadorPergunta
                    ? contadorPergunta.innerHTML
                    : "",

            cartao:
                cartaoPergunta
                    ? cartaoPergunta.innerHTML
                    : "",

            alternativas:
                alternativas
                    ? alternativas.innerHTML
                    : "",

            correta:
                alternativas
                    ? (
                        alternativas.dataset
                            .correta || ""
                    )
                    : "",

            resultado:
                resultado
                    ? resultado.innerHTML
                    : "",

            resultadoClasse:
                resultado
                    ? resultado.className
                    : "resultado-jogo",

            resultadoOculto:
                resultado
                    ? resultado.hidden
                    : true,

            cronometro:
                cronometro
                    ? cronometro.textContent
                    : "0"
        };
    }


    function restaurarPerguntaCongelada(
        snapshot
    ) {

        if (
            !snapshot ||
            typeof snapshot !==
            "object"
        ) {

            return;
        }


        if (
            contadorPergunta &&
            snapshot.contador !==
            undefined
        ) {

            contadorPergunta.innerHTML =
                snapshot.contador;
        }


        if (
            cartaoPergunta &&
            snapshot.cartao
        ) {

            cartaoPergunta.innerHTML =
                snapshot.cartao;
        }


        if (
            alternativas &&
            snapshot.alternativas
        ) {

            alternativas.innerHTML =
                snapshot.alternativas;

            alternativas.dataset.correta =
                snapshot.correta ||
                "";
        }


        if (
            resultado &&
            snapshot.resultado !==
            undefined
        ) {

            resultado.innerHTML =
                snapshot.resultado;

            resultado.className =
                snapshot.resultadoClasse ||
                "resultado-jogo";

            resultado.hidden =
                Boolean(
                    snapshot
                        .resultadoOculto
                );
        }


        if (cronometro) {

            cronometro.textContent =
                snapshot.cronometro ||
                "0";
        }
    }


    /* =====================================================
       MENU
    ===================================================== */

    function bloquearMenuCompleto() {

        linksMenu.forEach(
            function (link) {

                link.classList.add(
                    "bloqueado-partida"
                );

                link.setAttribute(
                    "aria-disabled",
                    "true"
                );

                link.setAttribute(
                    "tabindex",
                    "-1"
                );
            }
        );
    }


    function liberarTodosMenu() {

        linksMenu.forEach(
            function (link) {

                link.classList.remove(
                    "bloqueado-partida"
                );

                link.removeAttribute(
                    "aria-disabled"
                );

                link.removeAttribute(
                    "tabindex"
                );
            }
        );
    }


    function liberarJogarProximaPergunta() {

        if (!botaoJogar) {

            return;
        }

        aguardandoProxima =
            true;

        botaoJogar.classList.remove(
            "bloqueado-partida"
        );

        botaoJogar.removeAttribute(
            "aria-disabled"
        );

        botaoJogar.removeAttribute(
            "tabindex"
        );

        botaoJogar.href =
            obterBaseUrl() +
            "/proxima-pergunta.php";

        const titulo =
            botaoJogar.querySelector(
                ".texto-menu-jogo strong"
            );

        if (titulo) {

            titulo.textContent =
                "PRÓXIMA PERGUNTA";
        }

        const descricao =
            botaoJogar.querySelector(
                ".texto-menu-jogo small"
            );

        if (descricao) {

            descricao.textContent =
                "Avançar para a próxima pergunta";
        }
    }


    function liberarJogarNovaPartida() {

        if (!botaoJogar) {

            return;
        }

        botaoJogar.classList.remove(
            "bloqueado-partida"
        );

        botaoJogar.removeAttribute(
            "aria-disabled"
        );

        botaoJogar.removeAttribute(
            "tabindex"
        );

        botaoJogar.href =
            obterBaseUrl() +
            "/configurar-partida.php?modo=individual";

        botaoJogar.dataset.novaPartida =
            "true";

        const titulo =
            botaoJogar.querySelector(
                ".texto-menu-jogo strong"
            );

        if (titulo) {

            titulo.textContent =
                "NOVA PARTIDA";
        }

        const descricao =
            botaoJogar.querySelector(
                ".texto-menu-jogo small"
            );

        if (descricao) {

            descricao.textContent =
                "Iniciar uma nova partida";
        }
    }


    /* =====================================================
       CLIQUES DO MENU
    ===================================================== */

    linksMenu.forEach(
        function (link) {

            link.addEventListener(
                "click",
                function (evento) {

                    if (
                        link.classList.contains(
                            "bloqueado-partida"
                        )
                    ) {

                        evento.preventDefault();

                        return;
                    }


                    if (
                        link.dataset
                            .novaPartida ===
                        "true"
                    ) {

                        limparHistorico();

                        limparEstadoPartida();

                        limparTempoPartida();

                        return;
                    }


                    if (
                        aguardandoProxima &&
                        link === botaoJogar
                    ) {

                        return;
                    }


                    if (
                        partidaEncerrada
                    ) {

                        limparHistorico();

                        limparEstadoPartida();

                        limparTempoPartida();
                    }
                }
            );
        }
    );


    /* =====================================================
       CARD FINAL
    ===================================================== */

    function criarEncerramento(
        valorPremio,
        mensagem,
        sucesso = false
    ) {

        if (!menuLateral) {

            return;
        }

        let painel =
            document.getElementById(
                "gameOverJogo"
            );

        if (!painel) {

            painel =
                document.createElement(
                    "section"
                );

            painel.id =
                "gameOverJogo";

            menuLateral
                .insertAdjacentElement(
                    "afterend",
                    painel
                );
        }


        painel.className =
            sucesso
                ? "game-over-jogo parabens"
                : "game-over-jogo";


        const titulo =
            sucesso
                ? "PARABÉNS!"
                : "GAME OVER";

        const icone =
            sucesso
                ? "fa-solid fa-trophy"
                : "fa-solid fa-skull-crossbones";

        const rotuloPremio =
            sucesso
                ? "PRÊMIO CONQUISTADO"
                : "PRÊMIO ACUMULADO";


        painel.innerHTML =
            '<div class="game-over-cabecalho">' +

                '<span class="icone-game-over">' +

                    '<i class="' +
                    icone +
                    '"></i>' +

                '</span>' +

                '<div>' +

                    '<strong>' +
                    titulo +
                    '</strong>' +

                    '<small>' +
                    escaparHtml(
                        mensagem
                    ) +
                    '</small>' +

                '</div>' +

            '</div>' +

            '<div class="game-over-premio">' +

                '<span>' +
                rotuloPremio +
                '</span>' +

                '<strong>' +
                escaparHtml(
                    valorPremio
                ) +
                '</strong>' +

            '</div>';
    }


    /* =====================================================
       DADOS PARA O RANKING
    ===================================================== */

    function montarDadosRanking(
        sucesso,
        valorPremio
    ) {

        const dados =
            obterDadosPergunta();

        const ajudas =
            obterResumoAjudas();

        const perguntaFinal =
            Math.max(
                1,
                dados.atual
            );

        return {

            resultado:
                sucesso
                    ? "vencedor"
                    : "game_over",

            pergunta_final:
                perguntaFinal,

            perguntas_respondidas:
                perguntaFinal,

            acertos:
                sucesso
                    ? perguntaFinal
                    : Math.max(
                        0,
                        perguntaFinal - 1
                    ),

            erros:
                sucesso
                    ? 0
                    : 1,

            tempo_total_ms:
                carregarTempoPartida(),

            ajuda_cinquenta:
                ajudas.cinquenta
                    ? 1
                    : 0,

            ajuda_tecnico:
                ajudas.tecnico
                    ? 1
                    : 0,

            ajuda_turma:
                ajudas.turma
                    ? 1
                    : 0,

            ajuda_trocar:
                ajudas.trocar
                    ? 1
                    : 0,

            premio:
                converterPremioParaNumero(
                    valorPremio
                )
        };
    }


    /* =====================================================
       GRAVA RESULTADO
    ===================================================== */

    async function registrarResultadoRanking(
        estado
    ) {

        if (
            !estado ||
            estado.rankingRegistrado ===
            true ||
            !estado.dadosRanking
        ) {

            return;
        }

        try {

            const resposta =
                await fetch(
                    obterBaseUrl() +
                    "/api/resultado.php",
                    {

                        method:
                            "POST",

                        headers: {

                            "Content-Type":
                                "application/json"
                        },

                        body:
                            JSON.stringify(
                                estado.dadosRanking
                            )
                    }
                );

            const retorno =
                await resposta.json();


            if (
                !resposta.ok ||
                !retorno.sucesso
            ) {

                console.error(
                    "Erro ao gravar ranking:",
                    retorno
                );

                return;
            }


            estado.rankingRegistrado =
                true;

            salvarEstadoPartida(
                estado
            );


        } catch (erro) {

            console.error(
                "Erro ao registrar ranking:",
                erro
            );
        }
    }


    /* =====================================================
       BLOQUEAR ALTERNATIVAS / AJUDAS
    ===================================================== */

    function bloquearAlternativas() {

        if (!alternativas) {

            return;
        }

        alternativas
            .querySelectorAll(
                ".alternativa-jogo"
            )
            .forEach(
                function (opcao) {

                    opcao.disabled =
                        true;
                }
            );
    }


    function desabilitarAjudas() {

        botoesAjuda.forEach(
            function (botao) {

                botao.disabled =
                    true;
            }
        );
    }


    function restaurarEstadoBotoesAjuda() {

        botoesAjuda.forEach(
            function (botao) {

                const tipo =
                    botao.dataset.ajuda;

                if (
                    ajudaFoiUtilizada(
                        tipo
                    )
                ) {

                    botao.disabled =
                        true;
                }
            }
        );
    }


    /* =====================================================
       ENCERRA PARTIDA
    ===================================================== */

    function encerrarPartida(
        valorPremio,
        mensagem,
        sucesso = false
    ) {

        partidaEncerrada =
            true;

        respondida =
            true;

        aguardandoProxima =
            false;


        if (
            intervaloCronometro
        ) {

            clearInterval(
                intervaloCronometro
            );

            intervaloCronometro =
                null;
        }


        consolidarTempoQuestao();

        bloquearAlternativas();

        desabilitarAjudas();

        liberarTodosMenu();

        liberarJogarNovaPartida();


        const snapshot =
            capturarPerguntaAtual();


        const dadosRanking =
            montarDadosRanking(
                sucesso,
                valorPremio
            );


        const estadoFinal = {

            encerrada:
                true,

            sucesso:
                Boolean(
                    sucesso
                ),

            valorPremio:
                valorPremio,

            mensagem:
                mensagem,

            pergunta:
                snapshot,

            dadosRanking:
                dadosRanking,

            rankingRegistrado:
                false
        };


        salvarEstadoPartida(
            estadoFinal
        );


        criarEncerramento(
            valorPremio,
            mensagem,
            sucesso
        );


        registrarResultadoRanking(
            estadoFinal
        );
    }


    /* =====================================================
       RESTAURA PARTIDA ENCERRADA
    ===================================================== */

    function restaurarEstadoPartida() {

        const estado =
            carregarEstadoPartida();

        if (
            !estado ||
            estado.encerrada !==
            true
        ) {

            return false;
        }


        partidaEncerrada =
            true;

        respondida =
            true;

        aguardandoProxima =
            false;


        if (
            estado.pergunta
        ) {

            restaurarPerguntaCongelada(
                estado.pergunta
            );
        }


        bloquearAlternativas();

        desabilitarAjudas();

        liberarTodosMenu();

        liberarJogarNovaPartida();


        criarEncerramento(
            estado.valorPremio ||
            "R$ 0",

            estado.mensagem ||
            "Partida encerrada.",

            Boolean(
                estado.sucesso
            )
        );


        /*
         * Se a gravação falhou antes
         * do F5, tenta novamente.
         *
         * O partida_token UNIQUE no banco
         * impede duplicidade.
         */

        if (
            estado.dadosRanking &&
            estado.rankingRegistrado !==
            true
        ) {

            registrarResultadoRanking(
                estado
            );
        }


        return true;
    }


    /* =====================================================
       CARDS DAS AJUDAS
    ===================================================== */

    function criarCardAjuda(
        tipo,
        titulo,
        icone
    ) {

        if (!painelAjuda) {

            return null;
        }

        painelAjuda.hidden =
            false;

        let card =
            painelAjuda.querySelector(
                '[data-card-ajuda="' +
                tipo +
                '"]'
            );

        if (!card) {

            card =
                document.createElement(
                    "article"
                );

            card.className =
                "card-ajuda-detalhe";

            card.dataset.cardAjuda =
                tipo;

            painelAjuda.appendChild(
                card
            );
        }


        card.innerHTML =
            '<div class="titulo-card-ajuda">' +

                '<span class="icone-card-ajuda">' +

                    '<i class="' +
                    icone +
                    '"></i>' +

                '</span>' +

                '<strong>' +
                escaparHtml(
                    titulo
                ) +
                '</strong>' +

            '</div>' +

            '<div class="conteudo-card-ajuda"></div>';


        return card;
    }


    /* =====================================================
       50/50
    ===================================================== */

    function renderizarCinquenta(
        registro
    ) {

        const card =
            criarCardAjuda(
                "cinquenta",
                "50/50",
                "fa-solid fa-scissors"
            );

        if (!card) {

            return;
        }

        const conteudo =
            card.querySelector(
                ".conteudo-card-ajuda"
            );

        let alternativasHtml =
            "";


        registro.alternativas
            .forEach(
                function (alternativa) {

                    const classe =
                        alternativa.removida
                            ? " mini-alternativa-removida"
                            : "";

                    alternativasHtml +=

                        '<div class="mini-alternativa' +
                        classe +
                        '">' +

                            '<strong>' +
                            escaparHtml(
                                alternativa.letra
                            ) +
                            '</strong>' +

                            '<span>' +
                            escaparHtml(
                                alternativa.texto
                            ) +
                            '</span>' +

                        '</div>';
                }
            );


        conteudo.innerHTML =

            '<span class="numero-historico-ajuda">' +
            escaparHtml(
                registro.numero
            ) +
            '</span>' +

            '<p class="pergunta-historico-ajuda">' +
            escaparHtml(
                registro.pergunta
            ) +
            '</p>' +

            '<div class="mini-alternativas-ajuda">' +
            alternativasHtml +
            '</div>';
    }


    function registrarCinquenta(
        removidas
    ) {

        if (
            !alternativas ||
            !tituloPergunta
        ) {

            return;
        }

        const lista = [];


        alternativas
            .querySelectorAll(
                ".alternativa-jogo"
            )
            .forEach(
                function (opcao) {

                    const texto =
                        opcao.querySelector(
                            "span"
                        );

                    lista.push({

                        letra:
                            opcao.dataset.letra ||
                            "",

                        texto:
                            texto
                                ? texto.textContent
                                    .trim()
                                : "",

                        removida:
                            removidas.includes(
                                opcao.dataset.letra
                            )
                    });
                }
            );


        const registro = {

            tipo:
                "cinquenta",

            numero:
                contadorPergunta
                    ? contadorPergunta
                        .textContent
                        .replace(
                            /\s+/g,
                            " "
                        )
                        .trim()
                    : "",

            pergunta:
                tituloPergunta
                    .textContent
                    .trim(),

            alternativas:
                lista
        };


        salvarAjuda(
            registro
        );

        renderizarCinquenta(
            registro
        );
    }


    /* =====================================================
       TÉCNICO
    ===================================================== */

    function renderizarTecnico(
        registro
    ) {

        const card =
            criarCardAjuda(
                "tecnico",
                "Consultar o Técnico",
                "fa-solid fa-user-gear"
            );

        if (!card) {

            return;
        }

        const conteudo =
            card.querySelector(
                ".conteudo-card-ajuda"
            );


        conteudo.innerHTML =

            '<p class="rotulo-tecnico">' +
            'O técnico diz:' +
            '</p>' +

            '<div class="fala-tecnico">' +
            escaparHtml(
                registro.mensagem
            ) +
            '</div>';
    }


    function registrarTecnico() {

        const registro = {

            tipo:
                "tecnico",

            mensagem:
                "Analise atentamente o enunciado e identifique qual alternativa é tecnicamente mais adequada."
        };


        salvarAjuda(
            registro
        );

        renderizarTecnico(
            registro
        );
    }


    /* =====================================================
       TURMA
    ===================================================== */

    function renderizarTurma(
        registro
    ) {

        const card =
            criarCardAjuda(
                "turma",
                "Consultar a Turma",
                "fa-solid fa-users"
            );

        if (!card) {

            return;
        }

        const conteudo =
            card.querySelector(
                ".conteudo-card-ajuda"
            );


        const html =
            Object.entries(
                registro.votos
            )
                .map(
                    function (item) {

                        return (

                            '<span>' +

                                '<b>' +
                                escaparHtml(
                                    item[0]
                                ) +
                                '</b>' +

                                '<i>' +

                                    '<u style="width:' +
                                    Number(
                                        item[1]
                                    ) +
                                    '%"></u>' +

                                '</i>' +

                                '<em>' +
                                Number(
                                    item[1]
                                ) +
                                '%</em>' +

                            '</span>'
                        );
                    }
                )
                .join("");


        conteudo.innerHTML =

            '<p>' +
            'Veja como a turma votou:' +
            '</p>' +

            '<div class="votos-turma">' +
            html +
            '</div>';
    }


    function registrarTurma() {

        if (!alternativas) {

            return;
        }

        const correta =
            alternativas.dataset.correta;

        const votos = {

            A: 8,
            B: 14,
            C: 7,
            D: 9
        };


        votos[correta] =
            71;


        const registro = {

            tipo:
                "turma",

            votos:
                votos
        };


        salvarAjuda(
            registro
        );

        renderizarTurma(
            registro
        );
    }


    /* =====================================================
       TROCAR PERGUNTA
    ===================================================== */

    function renderizarTrocar(
        registro
    ) {

        const card =
            criarCardAjuda(
                "trocar",
                "Pergunta Trocada",
                "fa-solid fa-arrows-rotate"
            );

        if (!card) {

            return;
        }

        const conteudo =
            card.querySelector(
                ".conteudo-card-ajuda"
            );

        let alternativasHtml =
            "";


        registro.alternativas
            .forEach(
                function (alternativa) {

                    alternativasHtml +=

                        '<div class="mini-alternativa">' +

                            '<strong>' +
                            escaparHtml(
                                alternativa.letra
                            ) +
                            '</strong>' +

                            '<span>' +
                            escaparHtml(
                                alternativa.texto
                            ) +
                            '</span>' +

                        '</div>';
                }
            );


        conteudo.innerHTML =

            '<span class="numero-historico-ajuda">' +
            escaparHtml(
                registro.numero
            ) +
            '</span>' +

            '<p class="pergunta-historico-ajuda">' +
            escaparHtml(
                registro.pergunta
            ) +
            '</p>' +

            '<div class="mini-alternativas-ajuda">' +
            alternativasHtml +
            '</div>';
    }


    function registrarTrocar() {

        if (
            !alternativas ||
            !tituloPergunta
        ) {

            return;
        }

        const lista = [];


        alternativas
            .querySelectorAll(
                ".alternativa-jogo"
            )
            .forEach(
                function (opcao) {

                    const texto =
                        opcao.querySelector(
                            "span"
                        );

                    lista.push({

                        letra:
                            opcao.dataset.letra ||
                            "",

                        texto:
                            texto
                                ? texto.textContent
                                    .trim()
                                : ""
                    });
                }
            );


        const registro = {

            tipo:
                "trocar",

            numero:
                contadorPergunta
                    ? contadorPergunta
                        .textContent
                        .replace(
                            /\s+/g,
                            " "
                        )
                        .trim()
                    : "",

            pergunta:
                tituloPergunta
                    .textContent
                    .trim(),

            alternativas:
                lista
        };


        salvarAjuda(
            registro
        );

        renderizarTrocar(
            registro
        );
    }


    /* =====================================================
       RESTAURA HISTÓRICO
    ===================================================== */

    function restaurarHistorico() {

        const historico =
            carregarHistorico();


        historico.forEach(
            function (registro) {

                switch (
                    registro.tipo
                ) {

                    case "cinquenta":

                        renderizarCinquenta(
                            registro
                        );

                        break;


                    case "tecnico":

                        renderizarTecnico(
                            registro
                        );

                        break;


                    case "turma":

                        renderizarTurma(
                            registro
                        );

                        break;


                    case "trocar":

                        renderizarTrocar(
                            registro
                        );

                        break;
                }
            }
        );
    }


    /* =====================================================
       INICIALIZAÇÃO
    ===================================================== */

    bloquearMenuCompleto();

    restaurarHistorico();

    restaurarEstadoBotoesAjuda();


    const partidaFinalizadaAoCarregar =
        restaurarEstadoPartida();


    if (
        !partidaFinalizadaAoCarregar
    ) {

        iniciarTempoQuestao();
    }


    /* =====================================================
       ATUALIZAÇÃO / F5
    ===================================================== */

    window.addEventListener(
        "beforeunload",
        function () {

            if (
                !partidaEncerrada &&
                !respondida
            ) {

                consolidarTempoQuestao();
            }
        }
    );


    /* =====================================================
       SAIR
    ===================================================== */

    if (botaoSair) {

        botaoSair.addEventListener(
            "click",
            function () {

                limparHistorico();

                limparEstadoPartida();

                limparTempoPartida();
            }
        );
    }


    /* =====================================================
       CRONÔMETRO
    ===================================================== */

    if (
        cronometro &&
        !partidaEncerrada
    ) {

        let tempo =
            Number(
                cronometro.textContent
                    .trim()
            );


        intervaloCronometro =
            window.setInterval(
                function () {

                    if (
                        respondida ||
                        partidaEncerrada ||
                        tempo <= 0
                    ) {

                        return;
                    }


                    tempo -= 1;


                    cronometro.textContent =
                        String(
                            tempo
                        );


                    if (
                        tempo <= 5
                    ) {

                        cronometro
                            .classList
                            .add(
                                "tempo-critico"
                            );
                    }


                    if (
                        tempo <= 0
                    ) {

                        const correta =
                            alternativas
                                ? (
                                    alternativas
                                        .dataset
                                        .correta ||
                                    ""
                                )
                                : "";


                        const alternativaCorreta =
                            alternativas
                                ? alternativas
                                    .querySelector(
                                        '.alternativa-jogo[data-letra="' +
                                        correta +
                                        '"]'
                                    )
                                : null;


                        if (
                            alternativaCorreta
                        ) {

                            alternativaCorreta
                                .classList
                                .add(
                                    "correta"
                                );
                        }


                        if (resultado) {

                            resultado.hidden =
                                false;

                            resultado.className =
                                "resultado-jogo incorreta";


                            resultado.innerHTML =

                                '<strong>' +

                                    '<i class="fa-solid fa-clock"></i> ' +

                                    'TEMPO ESGOTADO!' +

                                '</strong>' +

                                '<span>' +

                                    'A resposta correta era: ' +

                                    '<b></b>' +

                                '</span>' +

                                '<p></p>';


                            const texto =
                                alternativaCorreta
                                    ? alternativaCorreta
                                        .querySelector(
                                            "span"
                                        )
                                    : null;


                            const campo =
                                resultado
                                    .querySelector(
                                        "b"
                                    );


                            if (campo) {

                                campo.textContent =
                                    correta +
                                    ") " +
                                    (
                                        texto
                                            ? texto
                                                .textContent
                                            : ""
                                    );
                            }


                            const campoExplicacao =
                                resultado
                                    .querySelector(
                                        "p"
                                    );


                            if (
                                campoExplicacao
                            ) {

                                campoExplicacao
                                    .textContent =
                                    explicacao
                                        ? explicacao
                                            .textContent
                                        : "";
                            }
                        }


                        encerrarPartida(
                            calcularPremioErro(),
                            "O tempo da pergunta terminou."
                        );
                    }

                },
                1000
            );
    }


    /* =====================================================
       SEM QUESTÃO
    ===================================================== */

    if (
        !alternativas ||
        !resultado
    ) {

        return;
    }


    /* =====================================================
       RESPOSTAS
    ===================================================== */

    alternativas
        .querySelectorAll(
            ".alternativa-jogo"
        )
        .forEach(
            function (botao) {

                botao.addEventListener(
                    "click",
                    function () {

                        if (
                            respondida ||
                            partidaEncerrada ||
                            botao.classList
                                .contains(
                                    "removida"
                                )
                        ) {

                            return;
                        }


                        const correta =
                            alternativas
                                .dataset
                                .correta;


                        const letra =
                            botao.dataset
                                .letra;


                        const alternativaCorreta =
                            alternativas
                                .querySelector(
                                    '.alternativa-jogo[data-letra="' +
                                    correta +
                                    '"]'
                                );


                        if (
                            intervaloCronometro
                        ) {

                            clearInterval(
                                intervaloCronometro
                            );

                            intervaloCronometro =
                                null;
                        }


                        bloquearAlternativas();

                        desabilitarAjudas();


                        /* =================================================
                           ACERTO
                        ================================================= */

                        if (
                            letra === correta
                        ) {

                            respondida =
                                true;


                            botao.classList
                                .add(
                                    "correta"
                                );


                            resultado.hidden =
                                false;

                            resultado.className =
                                "resultado-jogo correta";


                            resultado.innerHTML =

                                '<strong>' +

                                    '<i class="fa-solid fa-circle-check"></i> ' +

                                    'CORRETO!' +

                                '</strong>' +

                                '<p></p>';


                            const campoExplicacao =
                                resultado
                                    .querySelector(
                                        "p"
                                    );


                            if (
                                campoExplicacao
                            ) {

                                campoExplicacao
                                    .textContent =
                                    explicacao
                                        ? explicacao
                                            .textContent
                                        : "";
                            }


                            const dados =
                                obterDadosPergunta();


                            /* =================================================
                               ÚLTIMA PERGUNTA
                            ================================================= */

                            if (
                                dados.atual >=
                                dados.total
                            ) {

                                encerrarPartida(
                                    calcularPremioAtual(),
                                    "Você concluiu o desafio e chegou ao prêmio máximo!",
                                    true
                                );

                                return;
                            }


                            /* =================================================
                               PRÓXIMA PERGUNTA
                            ================================================= */

                            consolidarTempoQuestao();

                            liberarJogarProximaPergunta();

                            return;
                        }


                        /* =================================================
                           ERRO
                        ================================================= */

                        botao.classList
                            .add(
                                "incorreta"
                            );


                        if (
                            alternativaCorreta
                        ) {

                            alternativaCorreta
                                .classList
                                .add(
                                    "correta"
                                );
                        }


                        resultado.hidden =
                            false;

                        resultado.className =
                            "resultado-jogo incorreta";


                        resultado.innerHTML =

                            '<strong>' +

                                '<i class="fa-solid fa-circle-xmark"></i> ' +

                                'INCORRETO!' +

                            '</strong>' +

                            '<span>' +

                                'A resposta correta é: ' +

                                '<b></b>' +

                            '</span>' +

                            '<p></p>';


                        const texto =
                            alternativaCorreta
                                ? alternativaCorreta
                                    .querySelector(
                                        "span"
                                    )
                                : null;


                        const campo =
                            resultado.querySelector(
                                "b"
                            );


                        if (campo) {

                            campo.textContent =
                                correta +
                                ") " +
                                (
                                    texto
                                        ? texto
                                            .textContent
                                        : ""
                                );
                        }


                        const campoExplicacao =
                            resultado
                                .querySelector(
                                    "p"
                                );


                        if (
                            campoExplicacao
                        ) {

                            campoExplicacao
                                .textContent =
                                explicacao
                                    ? explicacao
                                        .textContent
                                    : "";
                        }


                        encerrarPartida(
                            calcularPremioErro(),
                            "Você errou a pergunta."
                        );
                    }
                );
            }
        );


    /* =====================================================
       AJUDAS
    ===================================================== */

    botoesAjuda.forEach(
        function (botao) {

            botao.addEventListener(
                "click",
                async function () {

                    if (
                        respondida ||
                        partidaEncerrada ||
                        botao.disabled
                    ) {

                        return;
                    }


                    const ajuda =
                        botao.dataset.ajuda;


                    try {

                        const resposta =
                            await fetch(
                                obterBaseUrl() +
                                "/api/jogo.php",
                                {

                                    method:
                                        "POST",

                                    headers: {

                                        "Content-Type":
                                            "application/x-www-form-urlencoded"
                                    },

                                    body:
                                        new URLSearchParams(
                                            {
                                                ajuda:
                                                    ajuda
                                            }
                                        )
                                }
                            );


                        const retorno =
                            await resposta.json();


                        if (
                            !retorno.sucesso
                        ) {

                            console.error(
                                retorno
                            );

                            return;
                        }


                        botao.disabled =
                            true;


                        /* =================================================
                           50/50
                        ================================================= */

                        if (
                            ajuda ===
                            "cinquenta"
                        ) {

                            const removidas =
                                Array.isArray(
                                    retorno.removidas
                                )
                                    ? retorno.removidas
                                    : [];


                            alternativas
                                .querySelectorAll(
                                    ".alternativa-jogo"
                                )
                                .forEach(
                                    function (opcao) {

                                        if (
                                            removidas
                                                .includes(
                                                    opcao
                                                        .dataset
                                                        .letra
                                                )
                                        ) {

                                            opcao
                                                .classList
                                                .add(
                                                    "removida"
                                                );

                                            opcao.disabled =
                                                true;
                                        }
                                    }
                                );


                            registrarCinquenta(
                                removidas
                            );

                            return;
                        }


                        /* =================================================
                           TÉCNICO
                        ================================================= */

                        if (
                            ajuda ===
                            "tecnico"
                        ) {

                            registrarTecnico();

                            return;
                        }


                        /* =================================================
                           TURMA
                        ================================================= */

                        if (
                            ajuda ===
                            "turma"
                        ) {

                            registrarTurma();

                            return;
                        }


                        /* =================================================
                           TROCAR
                        ================================================= */

                        if (
                            ajuda ===
                            "trocar"
                        ) {

                            registrarTrocar();

                            /*
                             * O tempo gasto na pergunta
                             * descartada também conta.
                             */

                            consolidarTempoQuestao();

                            window.location
                                .reload();

                            return;
                        }


                    } catch (erro) {

                        console.error(
                            "Erro ao utilizar ajuda:",
                            erro
                        );
                    }
                }
            );
        }
    );

});