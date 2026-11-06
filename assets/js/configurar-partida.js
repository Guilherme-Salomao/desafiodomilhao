document.addEventListener("DOMContentLoaded", function () {
  /* =====================================================
       ELEMENTOS
    ===================================================== */

  const formulario = document.getElementById("formConfigurarPartida");

  const quantidadePerguntas = document.getElementById("quantidadePerguntas");

  const dificuldades = document.querySelectorAll('input[name="dificuldade"]');

  const resumoPerguntas = document.getElementById("resumoPerguntas");

  const resumoTempo = document.getElementById("resumoTempo");

  const resumoDificuldade = document.getElementById("resumoDificuldade");

  const tituloExplicacao = document.getElementById(
    "tituloExplicacaoDificuldade",
  );

  const textoExplicacao = document.getElementById("textoExplicacaoDificuldade");

  const botaoVoltar = document.getElementById("btnVoltarConfiguracao");

  /* =====================================================
       CONFIGURAÇÕES DE DIFICULDADE
    ===================================================== */

  const configuracoesDificuldade = {
    facil: {
      nome: "Fácil",

      tempo: 20,

      titulo: "Modo Fácil",

      texto:
        "A partida utilizará somente perguntas fáceis, " +
        "com 20 segundos para responder cada questão.",
    },

    mista: {
      nome: "Mista",

      tempo: 30,

      titulo: "Modo Misto",

      texto:
        "A partida utilizará perguntas fáceis, médias " +
        "e difíceis, com 30 segundos para responder " +
        "cada questão.",
    },

    dificil: {
      nome: "Difícil",

      tempo: 45,

      titulo: "Modo Difícil",

      texto:
        "A partida utilizará somente perguntas difíceis, " +
        "com 45 segundos para responder cada questão.",
    },
  };

  /* =====================================================
       OBTÉM DIFICULDADE
    ===================================================== */

  function obterDificuldadeSelecionada() {
    return document.querySelector('input[name="dificuldade"]:checked');
  }

  /* =====================================================
       ATUALIZA RESUMO
    ===================================================== */

  function atualizarResumo() {
    /* =================================================
           QUANTIDADE
        ================================================= */

    if (quantidadePerguntas && resumoPerguntas) {
      resumoPerguntas.textContent = quantidadePerguntas.value;
    }

    /* =================================================
           DIFICULDADE
        ================================================= */

    const selecionada = obterDificuldadeSelecionada();

    if (!selecionada) {
      return;
    }

    const configuracao = configuracoesDificuldade[selecionada.value];

    if (!configuracao) {
      return;
    }

    /* =================================================
           NOME
        ================================================= */

    if (resumoDificuldade) {
      resumoDificuldade.textContent = configuracao.nome;
    }

    /* =================================================
           TEMPO
        ================================================= */

    if (resumoTempo) {
      resumoTempo.textContent = configuracao.tempo + "s";
    }

    /* =================================================
           EXPLICAÇÃO
        ================================================= */

    if (tituloExplicacao) {
      tituloExplicacao.textContent = configuracao.titulo;
    }

    if (textoExplicacao) {
      textoExplicacao.textContent = configuracao.texto;
    }
  }

  /* =====================================================
       EVENTO - QUANTIDADE
    ===================================================== */

  if (quantidadePerguntas) {
    quantidadePerguntas.addEventListener("change", atualizarResumo);
  }

  /* =====================================================
       EVENTO - DIFICULDADE
    ===================================================== */

  dificuldades.forEach(function (campo) {
    campo.addEventListener("change", atualizarResumo);
  });

  /* =====================================================
       VOLTAR
    ===================================================== */

  if (botaoVoltar) {
    botaoVoltar.addEventListener("click", function () {
      history.back();
    });
  }

  /* =====================================================
       VALIDAÇÃO DO FORMULÁRIO
    ===================================================== */

  if (formulario) {
    formulario.addEventListener("submit", function (evento) {
      /* =================================================
                   MODO
                ================================================= */

      const modoCampo = formulario.querySelector('input[name="modo"]');

      const modo = modoCampo ? modoCampo.value : "individual";

      /* =================================================
                   INDIVIDUAL
                ================================================= */

      if (modo === "individual") {
        const nome = document.getElementById("nomeJogador");

        if (!nome || nome.value.trim().length < 2) {
          evento.preventDefault();

          alert("Informe o nome do jogador.");

          if (nome) {
            nome.focus();
          }

          return;
        }
      }

      /* =================================================
                   COMPETIÇÃO
                ================================================= */

      if (modo === "competicao") {
        const equipe1 = document.getElementById("equipe1");

        const equipe2 = document.getElementById("equipe2");

        if (!equipe1 || equipe1.value.trim().length < 2) {
          evento.preventDefault();

          alert("Informe o nome da Equipe 1.");

          if (equipe1) {
            equipe1.focus();
          }

          return;
        }

        if (!equipe2 || equipe2.value.trim().length < 2) {
          evento.preventDefault();

          alert("Informe o nome da Equipe 2.");

          if (equipe2) {
            equipe2.focus();
          }

          return;
        }

        if (
          equipe1.value.trim().toLowerCase() ===
          equipe2.value.trim().toLowerCase()
        ) {
          evento.preventDefault();

          alert("As equipes precisam ter nomes diferentes.");

          equipe2.focus();

          return;
        }
      }

      /* =================================================
                   QUANTIDADE
                ================================================= */

      const quantidade = Number(quantidadePerguntas.value);

      const quantidadesPermitidas = [10, 15, 20];

      if (!quantidadesPermitidas.includes(quantidade)) {
        evento.preventDefault();

        alert("Quantidade de perguntas inválida.");

        return;
      }

      /* =================================================
                   DIFICULDADE
                ================================================= */

      const dificuldade = obterDificuldadeSelecionada();

      if (!dificuldade) {
        evento.preventDefault();

        alert("Selecione uma dificuldade.");

        return;
      }
    });
  }

  /* =====================================================
       INICIALIZA
    ===================================================== */

  atualizarResumo();
});
