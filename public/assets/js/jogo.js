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
       URL BASE
    ===================================================== */

    const baseUrl =
        window.APP_BASE_URL ||
        "/github/desafiodomilhao/public";


    /* =====================================================
       STORAGE
    ===================================================== */

    const chaveHistorico =
        "desafioMilhaoHistoricoAjudas";


    /* =====================================================
       ESTADO
    ===================================================== */

    let respondida = false;
    let partidaEncerrada = false;
    let aguardandoProxima = false;

    let intervaloCronometro = null;
    let intervaloTurma = null;
    let intervaloTecnico = null;

    let cronometroPausado = false;

    let tempoAtual =
        cronometro
            ? Number(
                cronometro.textContent.trim()
            )
            : 0;


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


    function salvarHistorico(historico) {

        sessionStorage.setItem(
            chaveHistorico,
            JSON.stringify(
                historico
            )
        );
    }


    function limparHistorico() {

        sessionStorage.removeItem(
            chaveHistorico
        );
    }


    function salvarAjuda(registro) {

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


    function ajudaFoiUtilizada(tipo) {

        return carregarHistorico()
            .some(
                function (registro) {

                    return (
                        registro.tipo === tipo
                    );
                }
            );
    }


    /* =====================================================
       CRONÔMETRO
    ===================================================== */

    function atualizarVisualCronometro() {

        if (!cronometro) {
            return;
        }

        cronometro.textContent =
            String(
                tempoAtual
            );

        cronometro.classList.toggle(
            "tempo-pausado",
            cronometroPausado
        );

        cronometro.classList.toggle(
            "tempo-critico",
            tempoAtual <= 5
        );

        cronometro.title =
            cronometroPausado
                ? "Tempo pausado. Clique para continuar."
                : "Clique para pausar o tempo.";
    }


    function iniciarCronometro() {

        if (
            !cronometro ||
            respondida ||
            partidaEncerrada ||
            tempoAtual <= 0 ||
            cronometroPausado ||
            intervaloCronometro
        ) {
            return;
        }


        atualizarVisualCronometro();


        intervaloCronometro =
            window.setInterval(
                function () {

                    if (
                        cronometroPausado ||
                        respondida ||
                        partidaEncerrada
                    ) {
                        return;
                    }


                    tempoAtual -= 1;

                    atualizarVisualCronometro();


                    if (tempoAtual <= 0) {

                        clearInterval(
                            intervaloCronometro
                        );

                        intervaloCronometro =
                            null;

                        tratarTempoEsgotado();
                    }

                },
                1000
            );
    }


    function pausarCronometro() {

        if (
            !cronometro ||
            respondida ||
            partidaEncerrada
        ) {
            return;
        }

        cronometroPausado = true;


        if (intervaloCronometro) {

            clearInterval(
                intervaloCronometro
            );

            intervaloCronometro = null;
        }


        atualizarVisualCronometro();
    }


    function continuarCronometro() {

        if (
            !cronometroPausado ||
            respondida ||
            partidaEncerrada ||
            tempoAtual <= 0
        ) {
            return;
        }


        cronometroPausado = false;

        atualizarVisualCronometro();

        iniciarCronometro();
    }


    /* =====================================================
       QR GRANDE
    ===================================================== */

    function fecharQrGrande() {

        const overlay =
            document.getElementById(
                "overlayQrTurma"
            );

        if (overlay) {
            overlay.remove();
        }

        document.body.classList.remove(
            "qr-turma-aberto"
        );
    }


    function abrirQrGrande(
        urlQr,
        titulo = "CONSULTAR A TURMA"
    ) {

        fecharQrGrande();


        const overlay =
            document.createElement(
                "div"
            );

        overlay.id =
            "overlayQrTurma";

        overlay.className =
            "overlay-qr-turma";


        overlay.innerHTML =
            '<div class="modal-qr-turma">' +

                '<button ' +
                    'type="button" ' +
                    'class="botao-fechar-qr-grande" ' +
                    'id="btnFecharQrGrande">' +

                    '<i class="fa-solid fa-xmark"></i>' +

                    '<span>FECHAR</span>' +

                '</button>' +

                '<div class="titulo-qr-grande">' +

                    '<i class="fa-solid fa-qrcode"></i>' +

                    '<strong>' +
                        escaparHtml(titulo) +
                    '</strong>' +

                '</div>' +

                '<div class="area-qr-grande">' +

                    '<img src="' +
                    escaparHtml(urlQr) +
                    '" alt="QR Code">' +

                '</div>' +

                '<p>' +
                    'Aponte a câmera do celular para responder' +
                '</p>' +

            '</div>';


        document.body.appendChild(
            overlay
        );

        document.body.classList.add(
            "qr-turma-aberto"
        );


        overlay
            .querySelector(
                "#btnFecharQrGrande"
            )
            .addEventListener(
                "click",
                fecharQrGrande
            );


        overlay.addEventListener(
            "click",
            function (evento) {

                if (
                    evento.target === overlay
                ) {

                    fecharQrGrande();
                }
            }
        );
    }


    document.addEventListener(
        "keydown",
        function (evento) {

            if (
                evento.key === "Escape"
            ) {
                fecharQrGrande();
            }
        }
    );


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

        aguardandoProxima = true;

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
            baseUrl +
            "/proxima-pergunta.php";


        const titulo =
            botaoJogar.querySelector(
                ".texto-menu-jogo strong"
            );

        if (titulo) {
            titulo.textContent =
                "PRÓXIMA PERGUNTA";
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
            baseUrl +
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
    }


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
                        link.dataset.novaPartida ===
                        "true"
                    ) {

                        limparHistorico();

                        return;
                    }


                    if (
                        aguardandoProxima &&
                        link === botaoJogar
                    ) {

                        return;
                    }


                    if (partidaEncerrada) {

                        limparHistorico();
                    }
                }
            );
        }
    );


    /* =====================================================
       CARD AJUDA
    ===================================================== */

    function criarCardAjuda(
        tipo,
        titulo,
        icone
    ) {

        if (!painelAjuda) {
            return null;
        }


        painelAjuda.hidden = false;


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
                    escaparHtml(titulo) +
                '</strong>' +

            '</div>' +

            '<div class="conteudo-card-ajuda"></div>';


        return card;
    }


    /* =====================================================
       TÉCNICO REAL
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


        /*
         * Resposta já recebida.
         */

        if (registro.respondida) {

            conteudo.innerHTML =
                '<div class="turma-modo-resultado">' +

                    '<p class="rotulo-resultado-turma">' +
                        'O técnico respondeu:' +
                    '</p>' +

                    '<strong class="quantidade-respostas-turma">' +

                        '<i class="fa-solid fa-user-gear"></i> ' +

                        'RESPOSTA RECEBIDA' +

                    '</strong>' +

                    '<div class="votos-turma">' +

                        '<span>' +

                            '<b>' +
                                escaparHtml(
                                    registro.alternativa || "?"
                                ) +
                            '</b>' +

                            '<i>' +

                                '<u style="width:100%"></u>' +

                            '</i>' +

                            '<em>100%</em>' +

                        '</span>' +

                    '</div>' +

                '</div>';

            return;
        }


        /*
         * QR aguardando uma única pessoa.
         */

        const urlQr =
            "https://api.qrserver.com/v1/create-qr-code/" +
            "?size=600x600" +
            "&data=" +
            encodeURIComponent(
                registro.url
            );


        conteudo.innerHTML =
            '<div class="turma-modo-qr">' +

                '<div class="qr-turma-grande">' +

                    '<button ' +
                        'type="button" ' +
                        'class="botao-abrir-qr-grande" ' +
                        'id="btnAbrirQrTecnico">' +

                        '<img src="' +
                        escaparHtml(urlQr) +
                        '" alt="QR Code do técnico">' +

                        '<span class="indicacao-ampliar-qr">' +

                            '<i class="fa-solid fa-expand"></i> ' +

                            'CLIQUE PARA AMPLIAR' +

                        '</span>' +

                    '</button>' +

                '</div>' +

                '<p class="texto-qr-turma">' +

                    'Somente uma pessoa poderá responder' +

                '</p>' +

            '</div>';


        const botaoQr =
            conteudo.querySelector(
                "#btnAbrirQrTecnico"
            );


        if (botaoQr) {

            botaoQr.addEventListener(
                "click",
                function () {

                    abrirQrGrande(
                        urlQr,
                        "CONSULTAR O TÉCNICO"
                    );
                }
            );
        }
    }


    async function atualizarResultadoTecnico(
        token
    ) {

        try {

            const resposta =
                await fetch(
                    baseUrl +
                    "/api/tecnico-resultado.php?t=" +
                    encodeURIComponent(token),
                    {
                        cache:
                            "no-store"
                    }
                );


            const retorno =
                await resposta.json();


            if (!retorno.sucesso) {
                return;
            }


            if (!retorno.respondida) {
                return;
            }


            const historico =
                carregarHistorico();


            const indice =
                historico.findIndex(
                    function (item) {

                        return (
                            item.tipo ===
                            "tecnico"
                        );
                    }
                );


            if (indice < 0) {
                return;
            }


            historico[indice].respondida =
                true;

            historico[indice].alternativa =
                retorno.alternativa;


            salvarHistorico(
                historico
            );


            fecharQrGrande();


            renderizarTecnico(
                historico[indice]
            );


            if (intervaloTecnico) {

                clearInterval(
                    intervaloTecnico
                );

                intervaloTecnico = null;
            }


        } catch (erro) {

            console.error(
                "Erro ao consultar técnico:",
                erro
            );
        }
    }


    function iniciarAtualizacaoTecnico(
        token
    ) {

        if (!token) {
            return;
        }


        if (intervaloTecnico) {

            clearInterval(
                intervaloTecnico
            );
        }


        atualizarResultadoTecnico(
            token
        );


        intervaloTecnico =
            window.setInterval(
                function () {

                    atualizarResultadoTecnico(
                        token
                    );

                },
                1500
            );
    }


    async function registrarTecnico() {

        try {

            const resposta =
                await fetch(
                    baseUrl +
                    "/api/tecnico-criar.php",
                    {
                        method:
                            "POST"
                    }
                );


            const retorno =
                await resposta.json();


            if (!retorno.sucesso) {

                return false;
            }


            const registro = {

                tipo:
                    "tecnico",

                token:
                    retorno.token,

                url:
                    retorno.url,

                consulta_id:
                    retorno.consulta_id,

                respondida:
                    false,

                alternativa:
                    null

            };


            salvarAjuda(
                registro
            );


            renderizarTecnico(
                registro
            );


            iniciarAtualizacaoTecnico(
                registro.token
            );


            return true;


        } catch (erro) {

            console.error(
                "Erro ao criar consulta ao técnico:",
                erro
            );

            return false;
        }
    }


    /* =====================================================
       TURMA
    ===================================================== */

    function atualizarEstadoExibicaoTurma(
        mostrarResultados
    ) {

        const historico =
            carregarHistorico();

        const indice =
            historico.findIndex(
                function (item) {

                    return (
                        item.tipo ===
                        "turma"
                    );
                }
            );


        if (indice < 0) {
            return;
        }


        historico[indice].mostrarResultados =
            mostrarResultados === true;


        salvarHistorico(
            historico
        );


        renderizarTurma(
            historico[indice]
        );
    }


    function renderizarTurma(registro) {

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


        if (!registro.mostrarResultados) {

            const urlQr =
                "https://api.qrserver.com/v1/create-qr-code/" +
                "?size=600x600" +
                "&data=" +
                encodeURIComponent(
                    registro.url
                );


            conteudo.innerHTML =
                '<div class="turma-modo-qr">' +

                    '<div class="qr-turma-grande">' +

                        '<button type="button" ' +
                        'class="botao-abrir-qr-grande" ' +
                        'id="btnAbrirQrGrande">' +

                            '<img src="' +
                            escaparHtml(urlQr) +
                            '" alt="QR Code">' +

                            '<span class="indicacao-ampliar-qr">' +

                                '<i class="fa-solid fa-expand"></i> ' +

                                'CLIQUE PARA AMPLIAR' +

                            '</span>' +

                        '</button>' +

                    '</div>' +

                    '<p class="texto-qr-turma">' +

                        'Leia o QR Code para responder' +

                    '</p>' +

                    '<button type="button" ' +
                    'class="botao-fechar-qr-turma" ' +
                    'id="btnFecharQrTurma">' +

                        '<i class="fa-solid fa-chart-column"></i> ' +

                        'VER RESULTADOS' +

                    '</button>' +

                '</div>';


            conteudo
                .querySelector(
                    "#btnAbrirQrGrande"
                )
                .addEventListener(
                    "click",
                    function () {

                        abrirQrGrande(
                            urlQr,
                            "CONSULTAR A TURMA"
                        );
                    }
                );


            conteudo
                .querySelector(
                    "#btnFecharQrTurma"
                )
                .addEventListener(
                    "click",
                    function () {

                        fecharQrGrande();

                        atualizarEstadoExibicaoTurma(
                            true
                        );
                    }
                );


            return;
        }


        const votos =
            registro.votos || {
                A: 0,
                B: 0,
                C: 0,
                D: 0
            };


        const total =
            Number(
                registro.total_respostas || 0
            );


        const htmlVotos =
            ["A", "B", "C", "D"]
                .map(
                    function (letra) {

                        const percentual =
                            Number(
                                votos[letra] || 0
                            );


                        return (
                            '<span>' +

                                '<b>' +
                                    letra +
                                '</b>' +

                                '<i>' +

                                    '<u style="width:' +
                                    percentual +
                                    '%"></u>' +

                                '</i>' +

                                '<em>' +
                                    percentual +
                                    '%</em>' +

                            '</span>'
                        );
                    }
                )
                .join("");


        conteudo.innerHTML =
            '<div class="turma-modo-resultado">' +

                '<p class="rotulo-resultado-turma">' +

                    'Veja como a turma votou:' +

                '</p>' +

                '<strong class="quantidade-respostas-turma">' +

                    '<i class="fa-solid fa-users"></i> ' +

                    total +

                    (
                        total === 1
                            ? ' resposta recebida'
                            : ' respostas recebidas'
                    ) +

                '</strong>' +

                '<div class="votos-turma">' +

                    htmlVotos +

                '</div>' +

            '</div>';
    }


    async function atualizarResultadoTurma(
        token
    ) {

        try {

            const resposta =
                await fetch(
                    baseUrl +
                    "/api/turma-resultado.php?t=" +
                    encodeURIComponent(token),
                    {
                        cache:
                            "no-store"
                    }
                );


            const retorno =
                await resposta.json();


            if (!retorno.sucesso) {
                return;
            }


            const historico =
                carregarHistorico();


            const indice =
                historico.findIndex(
                    function (item) {

                        return (
                            item.tipo ===
                            "turma"
                        );
                    }
                );


            if (indice < 0) {
                return;
            }


            historico[indice].votos =
                retorno.votos;

            historico[indice].total_respostas =
                Number(
                    retorno.total_respostas || 0
                );


            salvarHistorico(
                historico
            );


            if (
                historico[indice]
                    .mostrarResultados
            ) {

                renderizarTurma(
                    historico[indice]
                );
            }


        } catch (erro) {

            console.error(
                "Erro ao atualizar turma:",
                erro
            );
        }
    }


    function iniciarAtualizacaoTurma(
        token
    ) {

        if (intervaloTurma) {

            clearInterval(
                intervaloTurma
            );
        }


        atualizarResultadoTurma(
            token
        );


        intervaloTurma =
            window.setInterval(
                function () {

                    atualizarResultadoTurma(
                        token
                    );

                },
                2000
            );
    }


    async function registrarTurma() {

        try {

            const resposta =
                await fetch(
                    baseUrl +
                    "/api/turma-criar.php",
                    {
                        method:
                            "POST"
                    }
                );


            const retorno =
                await resposta.json();


            if (!retorno.sucesso) {

                return false;
            }


            const registro = {

                tipo:
                    "turma",

                token:
                    retorno.token,

                url:
                    retorno.url,

                consulta_id:
                    retorno.consulta_id,

                mostrarResultados:
                    false,

                total_respostas:
                    0,

                votos:
                    {
                        A: 0,
                        B: 0,
                        C: 0,
                        D: 0
                    }

            };


            salvarAjuda(
                registro
            );


            renderizarTurma(
                registro
            );


            iniciarAtualizacaoTurma(
                registro.token
            );


            return true;


        } catch (erro) {

            console.error(
                "Erro ao criar consulta da turma:",
                erro
            );

            return false;
        }
    }


    /* =====================================================
       50/50
    ===================================================== */

    function registrarCinquenta(
        removidas
    ) {

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


        const registro = {

            tipo:
                "cinquenta",

            numero:
                contadorPergunta
                    ? contadorPergunta
                        .textContent
                        .trim()
                    : "",

            pergunta:
                tituloPergunta
                    ? tituloPergunta
                        .textContent
                        .trim()
                    : "",

            alternativas:
                Array.from(
                    alternativas.querySelectorAll(
                        ".alternativa-jogo"
                    )
                )
                    .map(
                        function (opcao) {

                            const span =
                                opcao.querySelector(
                                    "span"
                                );

                            return {

                                letra:
                                    opcao.dataset.letra,

                                texto:
                                    span
                                        ? span.textContent.trim()
                                        : "",

                                removida:
                                    opcao.classList.contains(
                                        "removida"
                                    )

                            };
                        }
                    )

        };


        salvarAjuda(
            registro
        );


        renderizarCinquenta(
            registro
        );
    }


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


        conteudo.innerHTML =
            '<span class="numero-historico-ajuda">' +
                escaparHtml(registro.numero) +
            '</span>' +

            '<p class="pergunta-historico-ajuda">' +
                escaparHtml(registro.pergunta) +
            '</p>' +

            '<div class="mini-alternativas-ajuda">' +

                registro.alternativas
                    .map(
                        function (alternativa) {

                            return (
                                '<div class="mini-alternativa ' +

                                (
                                    alternativa.removida
                                        ? 'mini-alternativa-removida'
                                        : ''
                                ) +

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

                                '</div>'
                            );
                        }
                    )
                    .join("") +

            '</div>';
    }


    /* =====================================================
       TROCAR
    ===================================================== */

    function registrarTrocar() {

        const registro = {

            tipo:
                "trocar",

            mensagem:
                "Será exibida uma nova pergunta do mesmo nível de dificuldade."

        };


        salvarAjuda(
            registro
        );


        renderizarTrocar(
            registro
        );
    }


    function renderizarTrocar(
        registro
    ) {

        const card =
            criarCardAjuda(
                "trocar",
                "Trocar Pergunta",
                "fa-solid fa-arrows-rotate"
            );


        if (!card) {
            return;
        }


        const conteudo =
            card.querySelector(
                ".conteudo-card-ajuda"
            );


        conteudo.innerHTML =
            '<p>' +
                escaparHtml(
                    registro.mensagem
                ) +
            '</p>' +

            '<button type="button" ' +
            'class="botao-confirmar-troca" ' +
            'id="btnConfirmarTroca">' +

                'TROCAR PERGUNTA' +

            '</button>';


        conteudo
            .querySelector(
                "#btnConfirmarTroca"
            )
            .addEventListener(
                "click",
                function () {

                    window.location.reload();
                }
            );
    }


    /* =====================================================
       RESTAURA AJUDAS
    ===================================================== */

    function restaurarHistorico() {

        carregarHistorico()
            .forEach(
                function (registro) {

                    if (
                        registro.tipo ===
                        "cinquenta"
                    ) {

                        renderizarCinquenta(
                            registro
                        );
                    }


                    if (
                        registro.tipo ===
                        "tecnico"
                    ) {

                        renderizarTecnico(
                            registro
                        );

                        if (
                            !registro.respondida &&
                            registro.token
                        ) {

                            iniciarAtualizacaoTecnico(
                                registro.token
                            );
                        }
                    }


                    if (
                        registro.tipo ===
                        "turma"
                    ) {

                        renderizarTurma(
                            registro
                        );

                        if (registro.token) {

                            iniciarAtualizacaoTurma(
                                registro.token
                            );
                        }
                    }


                    if (
                        registro.tipo ===
                        "trocar"
                    ) {

                        renderizarTrocar(
                            registro
                        );
                    }
                }
            );
    }


    function restaurarEstadoBotoesAjuda() {

        botoesAjuda.forEach(
            function (botao) {

                if (
                    ajudaFoiUtilizada(
                        botao.dataset.ajuda
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
       GAME OVER
    ===================================================== */

    function encerrarPartida(
        valorPremio,
        mensagem
    ) {

        partidaEncerrada = true;
        respondida = true;


        if (intervaloCronometro) {
            clearInterval(intervaloCronometro);
        }

        if (intervaloTurma) {
            clearInterval(intervaloTurma);
        }

        if (intervaloTecnico) {
            clearInterval(intervaloTecnico);
        }


        fecharQrGrande();

        bloquearAlternativas();

        desabilitarAjudas();

        liberarTodosMenu();

        liberarJogarNovaPartida();


        if (!menuLateral) {
            return;
        }


        const painel =
            document.createElement(
                "section"
            );

        painel.className =
            "game-over-jogo";


        painel.innerHTML =
            '<div class="game-over-cabecalho">' +

                '<span class="icone-game-over">' +

                    '<i class="fa-solid fa-skull-crossbones"></i>' +

                '</span>' +

                '<div>' +

                    '<strong>GAME OVER</strong>' +

                    '<small>' +
                        escaparHtml(mensagem) +
                    '</small>' +

                '</div>' +

            '</div>' +

            '<div class="game-over-premio">' +

                '<span>PRÊMIO ACUMULADO</span>' +

                '<strong>' +
                    escaparHtml(valorPremio) +
                '</strong>' +

            '</div>';


        menuLateral.insertAdjacentElement(
            "afterend",
            painel
        );
    }


    function tratarTempoEsgotado() {

        if (
            respondida ||
            partidaEncerrada
        ) {
            return;
        }


        encerrarPartida(
            calcularPremioErro(),
            "O tempo da pergunta terminou."
        );
    }


    /* =====================================================
       INICIALIZAÇÃO
    ===================================================== */

    bloquearMenuCompleto();

    restaurarHistorico();

    restaurarEstadoBotoesAjuda();


    if (cronometro) {

        atualizarVisualCronometro();

        iniciarCronometro();


        /*
         * NOVA REGRA:
         *
         * correndo -> pausa
         * pausado  -> continua
         */

        cronometro.addEventListener(
            "click",
            function () {

                if (
                    respondida ||
                    partidaEncerrada
                ) {
                    return;
                }


                if (cronometroPausado) {

                    continuarCronometro();

                } else {

                    pausarCronometro();
                }
            }
        );
    }


    if (botaoSair) {

        botaoSair.addEventListener(
            "click",
            function () {

                limparHistorico();

                fecharQrGrande();
            }
        );
    }


    if (
        !alternativas ||
        !resultado
    ) {
        return;
    }


    /* =====================================================
       RESPOSTAS DO JOGO
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


                        if (intervaloCronometro) {
                            clearInterval(intervaloCronometro);
                        }


                        if (
                            letra === correta
                        ) {

                            respondida = true;

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

                                '<p>' +
                                    escaparHtml(
                                        explicacao
                                            ? explicacao.textContent
                                            : ""
                                    ) +
                                '</p>';


                            const dados =
                                obterDadosPergunta();


                            if (
                                dados.atual >=
                                dados.total
                            ) {

                                encerrarPartida(
                                    calcularPremioAtual(),
                                    "Parabéns! Você concluiu o desafio."
                                );

                                return;
                            }


                            liberarJogarProximaPergunta();

                            return;
                        }


                        botao.classList.add(
                            "incorreta"
                        );


                        const corretaElemento =
                            alternativas.querySelector(
                                '[data-letra="' +
                                correta +
                                '"]'
                            );


                        if (corretaElemento) {

                            corretaElemento.classList.add(
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

                            '<p>' +
                                escaparHtml(
                                    explicacao
                                        ? explicacao.textContent
                                        : ""
                                ) +
                            '</p>';


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


                    /*
                     * Toda ajuda pausa automaticamente.
                     */

                    pausarCronometro();


                    try {

                        const resposta =
                            await fetch(
                                baseUrl +
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


                        if (!retorno.sucesso) {

                            continuarCronometro();

                            return;
                        }


                        botao.disabled =
                            true;


                        if (
                            ajuda ===
                            "cinquenta"
                        ) {

                            registrarCinquenta(
                                Array.isArray(
                                    retorno.removidas
                                )
                                    ? retorno.removidas
                                    : []
                            );

                            return;
                        }


                        if (
                            ajuda ===
                            "tecnico"
                        ) {

                            const criada =
                                await registrarTecnico();


                            if (!criada) {

                                botao.disabled =
                                    false;

                                continuarCronometro();
                            }

                            return;
                        }


                        if (
                            ajuda ===
                            "turma"
                        ) {

                            const criada =
                                await registrarTurma();


                            if (!criada) {

                                botao.disabled =
                                    false;

                                continuarCronometro();
                            }

                            return;
                        }


                        if (
                            ajuda ===
                            "trocar"
                        ) {

                            registrarTrocar();

                            return;
                        }


                    } catch (erro) {

                        console.error(
                            "Erro ao utilizar ajuda:",
                            erro
                        );

                        continuarCronometro();
                    }
                }
            );
        }
    );

});