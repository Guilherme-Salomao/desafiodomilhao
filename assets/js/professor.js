document.addEventListener("DOMContentLoaded", function () {
  /* =====================================================
       ELEMENTOS
    ===================================================== */

  const formulario = document.getElementById("formProfessor");

  const areas = document.querySelectorAll('input[name="area_id"]');

  const quantidades = document.querySelectorAll(
    'input[name="quantidade_perguntas"]',
  );

  const dificuldades = document.querySelectorAll('input[name="dificuldade"]');

  const resumoArea = document.getElementById("resumoArea");

  const resumoQuantidade = document.getElementById("resumoQuantidade");

  const resumoDificuldade = document.getElementById("resumoDificuldade");

  /* =====================================================
       NOMES DAS DIFICULDADES
    ===================================================== */

  const nomesDificuldade = {
    facil: "Fácil",

    mista: "Mista",

    dificil: "Difícil",
  };

  /* =====================================================
       ATUALIZA RESUMO
    ===================================================== */

  function atualizarResumo() {
    /* =================================================
           ÁREA
        ================================================= */

    const areaSelecionada = document.querySelector(
      'input[name="area_id"]:checked',
    );

    if (areaSelecionada && resumoArea) {
      resumoArea.textContent = areaSelecionada.dataset.nome || "-";
    }

    /* =================================================
           QUANTIDADE
        ================================================= */

    const quantidadeSelecionada = document.querySelector(
      'input[name="quantidade_perguntas"]:checked',
    );

    if (quantidadeSelecionada && resumoQuantidade) {
      resumoQuantidade.textContent = quantidadeSelecionada.value;
    }

    /* =================================================
           DIFICULDADE
        ================================================= */

    const dificuldadeSelecionada = document.querySelector(
      'input[name="dificuldade"]:checked',
    );

    if (dificuldadeSelecionada && resumoDificuldade) {
      resumoDificuldade.textContent =
        nomesDificuldade[dificuldadeSelecionada.value] || "-";
    }
  }

  /* =====================================================
       EVENTOS
    ===================================================== */

  areas.forEach(function (campo) {
    campo.addEventListener("change", atualizarResumo);
  });

  quantidades.forEach(function (campo) {
    campo.addEventListener("change", atualizarResumo);
  });

  dificuldades.forEach(function (campo) {
    campo.addEventListener("change", atualizarResumo);
  });

  /* =====================================================
       VALIDAÇÃO
    ===================================================== */

  if (formulario) {
    formulario.addEventListener("submit", function (evento) {
      const area = document.querySelector('input[name="area_id"]:checked');

      const quantidade = document.querySelector(
        'input[name="quantidade_perguntas"]:checked',
      );

      const dificuldade = document.querySelector(
        'input[name="dificuldade"]:checked',
      );

      if (!area) {
        evento.preventDefault();

        alert("Selecione uma área para o quiz.");

        return;
      }

      if (!quantidade) {
        evento.preventDefault();

        alert("Selecione a quantidade de perguntas.");

        return;
      }

      if (!dificuldade) {
        evento.preventDefault();

        alert("Selecione a dificuldade.");

        return;
      }
    });
  }

  /* =====================================================
       INICIALIZA
    ===================================================== */

  atualizarResumo();
});
