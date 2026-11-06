document.addEventListener("DOMContentLoaded", function () {
  /* =====================================================
       FECHAR MENSAGENS DE CADASTRO
    ===================================================== */

  const botoesFecharMensagem = document.querySelectorAll(
    "[data-fechar-mensagem]",
  );

  botoesFecharMensagem.forEach(function (botao) {
    botao.addEventListener("click", function () {
      const mensagem = botao.closest(".mensagem-cadastro-status");

      if (mensagem) {
        mensagem.remove();
      }
    });
  });

  /* =====================================================
       CARDS
    ===================================================== */

  const cards = document.querySelectorAll(".card-configuracao");

  const paineis = document.querySelectorAll(".painel-configuracao");

  const botoesFechar = document.querySelectorAll(".botao-fechar-painel");

  /* =====================================================
       ABRIR PAINEL
    ===================================================== */

  function abrirPainel(idPainel) {
    paineis.forEach(function (painel) {
      painel.hidden = painel.id !== idPainel;
    });

    const painel = document.getElementById(idPainel);

    if (!painel) {
      return;
    }

    painel.hidden = false;

    painel.scrollIntoView({
      behavior: "smooth",

      block: "start",
    });
  }

  /* =====================================================
       CLIQUE NOS CARDS
    ===================================================== */

  cards.forEach(function (card) {
    card.addEventListener("click", function () {
      const idPainel = card.dataset.painel;

      if (!idPainel) {
        return;
      }

      abrirPainel(idPainel);
    });
  });

  /* =====================================================
       FECHAR PAINÉIS
    ===================================================== */

  botoesFechar.forEach(function (botao) {
    botao.addEventListener("click", function () {
      const painel = botao.closest(".painel-configuracao");

      if (painel) {
        painel.hidden = true;
      }
    });
  });

  /* =====================================================
       CAMPOS
    ===================================================== */

  const campos = {
    musica: document.getElementById("configMusica"),

    volumeMusica: document.getElementById("configVolumeMusica"),

    efeitos: document.getElementById("configEfeitos"),

    volumeEfeitos: document.getElementById("configVolumeEfeitos"),

    animacoes: document.getElementById("configAnimacoes"),

    telaCheia: document.getElementById("configTelaCheia"),

    efeitosVisuais: document.getElementById("configEfeitosVisuais"),

    embaralharPerguntas: document.getElementById("configEmbaralharPerguntas"),

    embaralharAlternativas: document.getElementById(
      "configEmbaralharAlternativas",
    ),

    mostrarExplicacao: document.getElementById("configMostrarExplicacao"),

    mostrarDificuldade: document.getElementById("configMostrarDificuldade"),

    confirmarSaida: document.getElementById("configConfirmarSaida"),

    participarRanking: document.getElementById("configParticiparRanking"),

    mostrarPosicao: document.getElementById("configMostrarPosicao"),
  };

  /* =====================================================
       TEXTOS DE VOLUME
    ===================================================== */

  const valorVolumeMusica = document.getElementById("valorVolumeMusica");

  const valorVolumeEfeitos = document.getElementById("valorVolumeEfeitos");

  /* =====================================================
       CONFIGURAÇÕES PADRÃO
    ===================================================== */

  const configuracoesPadrao = {
    musica: true,

    volumeMusica: 35,

    efeitos: true,

    volumeEfeitos: 70,

    animacoes: true,

    telaCheia: false,

    efeitosVisuais: true,

    embaralharPerguntas: true,

    embaralharAlternativas: true,

    mostrarExplicacao: true,

    mostrarDificuldade: true,

    confirmarSaida: true,

    participarRanking: true,

    mostrarPosicao: true,
  };

  /* =====================================================
       MONTA CONFIGURAÇÕES ATUAIS
    ===================================================== */

  function obterConfiguracoesAtuais() {
    return {
      musica: campos.musica
        ? campos.musica.checked
        : configuracoesPadrao.musica,

      volumeMusica: campos.volumeMusica
        ? Number(campos.volumeMusica.value)
        : configuracoesPadrao.volumeMusica,

      efeitos: campos.efeitos
        ? campos.efeitos.checked
        : configuracoesPadrao.efeitos,

      volumeEfeitos: campos.volumeEfeitos
        ? Number(campos.volumeEfeitos.value)
        : configuracoesPadrao.volumeEfeitos,

      animacoes: campos.animacoes
        ? campos.animacoes.checked
        : configuracoesPadrao.animacoes,

      telaCheia: campos.telaCheia
        ? campos.telaCheia.checked
        : configuracoesPadrao.telaCheia,

      efeitosVisuais: campos.efeitosVisuais
        ? campos.efeitosVisuais.checked
        : configuracoesPadrao.efeitosVisuais,

      embaralharPerguntas: campos.embaralharPerguntas
        ? campos.embaralharPerguntas.checked
        : configuracoesPadrao.embaralharPerguntas,

      embaralharAlternativas: campos.embaralharAlternativas
        ? campos.embaralharAlternativas.checked
        : configuracoesPadrao.embaralharAlternativas,

      mostrarExplicacao: campos.mostrarExplicacao
        ? campos.mostrarExplicacao.checked
        : configuracoesPadrao.mostrarExplicacao,

      mostrarDificuldade: campos.mostrarDificuldade
        ? campos.mostrarDificuldade.checked
        : configuracoesPadrao.mostrarDificuldade,

      confirmarSaida: campos.confirmarSaida
        ? campos.confirmarSaida.checked
        : configuracoesPadrao.confirmarSaida,

      participarRanking: campos.participarRanking
        ? campos.participarRanking.checked
        : configuracoesPadrao.participarRanking,

      mostrarPosicao: campos.mostrarPosicao
        ? campos.mostrarPosicao.checked
        : configuracoesPadrao.mostrarPosicao,
    };
  }

  /* =====================================================
       SALVAR
    ===================================================== */

  function salvarConfiguracoes() {
    const configuracoes = obterConfiguracoesAtuais();

    localStorage.setItem(
      "desafioConfiguracoes",

      JSON.stringify(configuracoes),
    );
  }

  /* =====================================================
       APLICAR CONFIGURAÇÕES
    ===================================================== */

  function aplicarConfiguracoes(config) {
    if (campos.musica) {
      campos.musica.checked = Boolean(config.musica);
    }

    if (campos.volumeMusica) {
      campos.volumeMusica.value = String(config.volumeMusica);
    }

    if (campos.efeitos) {
      campos.efeitos.checked = Boolean(config.efeitos);
    }

    if (campos.volumeEfeitos) {
      campos.volumeEfeitos.value = String(config.volumeEfeitos);
    }

    if (campos.animacoes) {
      campos.animacoes.checked = Boolean(config.animacoes);
    }

    if (campos.telaCheia) {
      campos.telaCheia.checked = Boolean(config.telaCheia);
    }

    if (campos.efeitosVisuais) {
      campos.efeitosVisuais.checked = Boolean(config.efeitosVisuais);
    }

    if (campos.embaralharPerguntas) {
      campos.embaralharPerguntas.checked = Boolean(config.embaralharPerguntas);
    }

    if (campos.embaralharAlternativas) {
      campos.embaralharAlternativas.checked = Boolean(
        config.embaralharAlternativas,
      );
    }

    if (campos.mostrarExplicacao) {
      campos.mostrarExplicacao.checked = Boolean(config.mostrarExplicacao);
    }

    if (campos.mostrarDificuldade) {
      campos.mostrarDificuldade.checked = Boolean(config.mostrarDificuldade);
    }

    if (campos.confirmarSaida) {
      campos.confirmarSaida.checked = Boolean(config.confirmarSaida);
    }

    if (campos.participarRanking) {
      campos.participarRanking.checked = Boolean(config.participarRanking);
    }

    if (campos.mostrarPosicao) {
      campos.mostrarPosicao.checked = Boolean(config.mostrarPosicao);
    }

    /* =================================================
           TEXTOS DOS VOLUMES
        ================================================= */

    if (valorVolumeMusica) {
      valorVolumeMusica.textContent = String(config.volumeMusica) + "%";
    }

    if (valorVolumeEfeitos) {
      valorVolumeEfeitos.textContent = String(config.volumeEfeitos) + "%";
    }
  }

  /* =====================================================
       CARREGAR CONFIGURAÇÕES
    ===================================================== */

  function carregarConfiguracoes() {
    const salvas = localStorage.getItem("desafioConfiguracoes");

    if (!salvas) {
      aplicarConfiguracoes(configuracoesPadrao);

      return;
    }

    try {
      const configuracoesSalvas = JSON.parse(salvas);

      const configuracoes = {
        ...configuracoesPadrao,

        ...configuracoesSalvas,
      };

      aplicarConfiguracoes(configuracoes);
    } catch (erro) {
      localStorage.removeItem("desafioConfiguracoes");

      aplicarConfiguracoes(configuracoesPadrao);
    }
  }

  /* =====================================================
       ATUALIZAR VALOR DOS VOLUMES
    ===================================================== */

  function atualizarTextoVolumeMusica() {
    if (!campos.volumeMusica || !valorVolumeMusica) {
      return;
    }

    valorVolumeMusica.textContent = campos.volumeMusica.value + "%";
  }

  function atualizarTextoVolumeEfeitos() {
    if (!campos.volumeEfeitos || !valorVolumeEfeitos) {
      return;
    }

    valorVolumeEfeitos.textContent = campos.volumeEfeitos.value + "%";
  }

  /* =====================================================
       EVENTOS DOS SWITCHES
    ===================================================== */

  const camposCheckbox = [
    campos.musica,

    campos.efeitos,

    campos.animacoes,

    campos.telaCheia,

    campos.efeitosVisuais,

    campos.embaralharPerguntas,

    campos.embaralharAlternativas,

    campos.mostrarExplicacao,

    campos.mostrarDificuldade,

    campos.confirmarSaida,

    campos.participarRanking,

    campos.mostrarPosicao,
  ];

  camposCheckbox.forEach(function (campo) {
    if (!campo) {
      return;
    }

    campo.addEventListener("change", function () {
      salvarConfiguracoes();
    });
  });

  /* =====================================================
       VOLUME DA MÚSICA
    ===================================================== */

  if (campos.volumeMusica) {
    campos.volumeMusica.addEventListener("input", function () {
      atualizarTextoVolumeMusica();

      salvarConfiguracoes();
    });
  }

  /* =====================================================
       VOLUME DOS EFEITOS
    ===================================================== */

  if (campos.volumeEfeitos) {
    campos.volumeEfeitos.addEventListener("input", function () {
      atualizarTextoVolumeEfeitos();

      salvarConfiguracoes();
    });
  }

  /* =====================================================
       RESTAURAR PADRÕES
    ===================================================== */

  const btnRestaurar = document.getElementById("btnRestaurarConfiguracoes");

  if (btnRestaurar) {
    btnRestaurar.addEventListener("click", function () {
      const confirmar = window.confirm(
        "Deseja restaurar todas as configurações para o padrão?",
      );

      if (!confirmar) {
        return;
      }

      localStorage.removeItem("desafioConfiguracoes");

      aplicarConfiguracoes(configuracoesPadrao);

      salvarConfiguracoes();
    });
  }

  /* =====================================================
       INICIALIZAÇÃO
    ===================================================== */

  carregarConfiguracoes();

  atualizarTextoVolumeMusica();

  atualizarTextoVolumeEfeitos();
});
