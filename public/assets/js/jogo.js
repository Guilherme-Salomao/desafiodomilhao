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


    /* =====================================================
       ESTADO
    ===================================================== */

    let respondida = false;

    let partidaEncerrada = false;

    let aguardandoProxima =
        false;

    let intervaloCronometro =
        null;


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

        const premios =
            {};

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
            premios[dados.atual - 1]
            || "R$ 0"
        );
    }


    function calcularPremioAtual() {

        const dados =
            obterDadosPergunta();

        const premios =
            obterPremios();

        return (
            premios[dados.atual]
            || "R$ 0"
        );
    }


    /* =====================================================
       HISTÓRICO
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


    /* =====================================================
       ESTADO PERSISTENTE DA PARTIDA
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
                typeof estado === "object"
            )
                ? estado
                : null;

        } catch (erro) {

            console.error(
                "Erro ao recuperar estado da partida:",
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
                    ? alternativas.dataset.correta || ""
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
            typeof snapshot !== "object"
        ) {
            return;
        }


        /* =================================================
           CONTADOR
        ================================================= */

        if (
            contadorPergunta &&
            snapshot.contador !== undefined
        ) {

            contadorPergunta.innerHTML =
                snapshot.contador;
        }


        /* =================================================
           PERGUNTA
        ================================================= */

        if (
            cartaoPergunta &&
            snapshot.cartao
        ) {

            cartaoPergunta.innerHTML =
                snapshot.cartao;
        }


        /* =================================================
           ALTERNATIVAS
        ================================================= */

        if (
            alternativas &&
            snapshot.alternativas
        ) {

            alternativas.innerHTML =
                snapshot.alternativas;

            alternativas.dataset.correta =
                snapshot.correta || "";
        }


        /* =================================================
           RESULTADO
        ================================================= */

        if (
            resultado &&
            snapshot.resultado !== undefined
        ) {

            resultado.innerHTML =
                snapshot.resultado;

            resultado.className =
                snapshot.resultadoClasse ||
                "resultado-jogo";

            resultado.hidden =
                Boolean(
                    snapshot.resultadoOculto
                );
        }


        /* =================================================
           CRONÔMETRO
        ================================================= */

        if (cronometro) {

            cronometro.textContent =
                snapshot.cronometro || "0";
        }
    }


    /* =====================================================
       HISTÓRICO DAS AJUDAS
    ===================================================== */

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


    /* =====================================================
       LIBERA SOMENTE JOGAR
    ===================================================== */

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
            (
                window.APP_BASE_URL ||
                "/github/desafiodomilhao/public"
            ) +
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


    /* =====================================================
       NOVA PARTIDA
    ===================================================== */

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
            (
                window.APP_BASE_URL ||
                "/github/desafiodomilhao/public"
            ) +
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


                    /* =========================================
                       NOVA PARTIDA
                    ========================================= */

                    if (
                        link.dataset.novaPartida ===
                        "true"
                    ) {

                        limparHistorico();

                        limparEstadoPartida();

                        return;
                    }


                    /* =========================================
                       PRÓXIMA PERGUNTA
                    ========================================= */

                    if (
                        aguardandoProxima &&
                        link === botaoJogar
                    ) {

                        return;
                    }


                    /* =========================================
                       SAIU DO ENCERRAMENTO
                    ========================================= */

                    if (partidaEncerrada) {

                        limparHistorico();

                        limparEstadoPartida();
                    }
                }
            );
        }
    );


    /* =====================================================
       ENCERRAMENTO DA PARTIDA
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

            menuLateral.insertAdjacentElement(
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


        if (intervaloCronometro) {

            clearInterval(
                intervaloCronometro
            );

            intervaloCronometro =
                null;
        }


        bloquearAlternativas();

        desabilitarAjudas();

        liberarTodosMenu();

        liberarJogarNovaPartida();


        /* =================================================
           CAPTURA A PERGUNTA EXATAMENTE COMO ESTÁ
        ================================================= */

        const snapshot =
            capturarPerguntaAtual();


        /* =================================================
           SALVA O ESTADO COMPLETO
        ================================================= */

        salvarEstadoPartida({

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
                snapshot

        });


        criarEncerramento(
            valorPremio,
            mensagem,
            sucesso
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
            estado.encerrada !== true
        ) {

            return false;
        }


        /* =================================================
           BLOQUEIA A PARTIDA
        ================================================= */

        partidaEncerrada =
            true;

        respondida =
            true;

        aguardandoProxima =
            false;


        /* =================================================
           RESTAURA A PERGUNTA QUE TERMINOU A PARTIDA

           O PHP pode ter carregado outra pergunta,
           mas ela é imediatamente substituída pela
           pergunta congelada.
        ================================================= */

        if (estado.pergunta) {

            restaurarPerguntaCongelada(
                estado.pergunta
            );
        }


        /* =================================================
           BLOQUEIA ALTERNATIVAS
        ================================================= */

        bloquearAlternativas();

        desabilitarAjudas();


        /* =================================================
           MENUS
        ================================================= */

        liberarTodosMenu();

        liberarJogarNovaPartida();


        /* =================================================
           CARD FINAL
        ================================================= */

        criarEncerramento(

            estado.valorPremio ||
            "R$ 0",

            estado.mensagem ||
            "Partida encerrada.",

            Boolean(
                estado.sucesso
            )
        );


        return true;
    }


    /* =====================================================
       BOTÕES DE AJUDA
    ===================================================== */

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


    function desabilitarAjudas() {

        botoesAjuda.forEach(
            function (botao) {

                botao.disabled =
                    true;
            }
        );
    }


    /* =====================================================
       CARD DE AJUDA
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

        registro.alternativas.forEach(
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

        const lista =
            [];

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
                            opcao.dataset.letra || "",

                        texto:
                            texto
                                ? texto.textContent.trim()
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

        registro.alternativas.forEach(
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

        const lista =
            [];

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
                            opcao.dataset.letra || "",

                        texto:
                            texto
                                ? texto.textContent.trim()
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
       RESTAURAR HISTÓRICO
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
       BLOQUEAR ALTERNATIVAS
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


    /* =====================================================
       INICIALIZAÇÃO
    ===================================================== */

    bloquearMenuCompleto();

    restaurarHistorico();

    restaurarEstadoBotoesAjuda();


    /* =====================================================
       PRIMEIRO RESTAURA O ESTADO FINAL

       Se houver GAME OVER ou PARABÉNS salvo,
       substitui imediatamente qualquer pergunta nova
       que o PHP tenha carregado.
    ===================================================== */

    restaurarEstadoPartida();


    /* =====================================================
       SAIR
    ===================================================== */

    if (botaoSair) {

        botaoSair.addEventListener(
            "click",
            function () {

                limparHistorico();

                limparEstadoPartida();
            }
        );
    }


    /* =====================================================
       CRONÔMETRO
    ===================================================== */

    if (cronometro) {

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


                    if (tempo <= 5) {

                        cronometro
                            .classList
                            .add(
                                "tempo-critico"
                            );
                    }


                    if (tempo <= 0) {

                        const correta =
                            alternativas
                                ? alternativas.dataset.correta
                                : "";

                        const alternativaCorreta =
                            alternativas
                                ? alternativas.querySelector(
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
                                resultado.querySelector(
                                    "b"
                                );

                            if (campo) {

                                campo.textContent =
                                    correta +
                                    ") " +
                                    (
                                        texto
                                            ? texto.textContent
                                            : ""
                                    );
                            }


                            const campoExplicacao =
                                resultado.querySelector(
                                    "p"
                                );

                            if (
                                campoExplicacao
                            ) {

                                campoExplicacao.textContent =
                                    explicacao
                                        ? explicacao.textContent
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
                            botao.classList.contains(
                                "removida"
                            )
                        ) {

                            return;
                        }


                        const correta =
                            alternativas.dataset.correta;

                        const letra =
                            botao.dataset.letra;

                        const alternativaCorreta =
                            alternativas.querySelector(
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

                            botao.classList.add(
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
                                resultado.querySelector(
                                    "p"
                                );


                            if (
                                campoExplicacao
                            ) {

                                campoExplicacao.textContent =
                                    explicacao
                                        ? explicacao.textContent
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
                               AINDA EXISTEM PERGUNTAS
                            ================================================= */

                            liberarJogarProximaPergunta();

                            return;
                        }


                        /* =================================================
                           ERRO
                        ================================================= */

                        botao.classList.add(
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
                                        ? texto.textContent
                                        : ""
                                );
                        }


                        const campoExplicacao =
                            resultado.querySelector(
                                "p"
                            );


                        if (
                            campoExplicacao
                        ) {

                            campoExplicacao.textContent =
                                explicacao
                                    ? explicacao.textContent
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

                                (
                                    window.APP_BASE_URL ||
                                    "/github/desafiodomilhao/public"
                                ) +

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
                                            removidas.includes(
                                                opcao.dataset.letra
                                            )
                                        ) {

                                            opcao.classList.add(
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

                            window.location.reload();

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