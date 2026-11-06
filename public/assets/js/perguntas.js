document.addEventListener("DOMContentLoaded", function () {
  /* =================================================
       FILTROS AUTOMÁTICOS
    ================================================= */

  const formularioFiltros = document.querySelector(".formulario-filtros");

  const campoBusca = document.getElementById("busca");

  let temporizadorBusca;

  function aplicarFiltros() {
    if (!formularioFiltros) {
      return;
    }

    formularioFiltros.submit();
  }

  if (campoBusca) {
    campoBusca.addEventListener("input", function () {
      window.clearTimeout(temporizadorBusca);

      temporizadorBusca = window.setTimeout(function () {
        aplicarFiltros();
      }, 450);
    });
  }

  if (formularioFiltros) {
    formularioFiltros
      .querySelectorAll('select[name="area"], select[name="dificuldade"], select[name="status"]')
      .forEach(function (campo) {
        campo.addEventListener("change", aplicarFiltros);
      });
  }

  /* =================================================
           FECHAR MENSAGENS
        ================================================= */

  const botoesFecharMensagem = document.querySelectorAll(
    "[data-fechar-mensagem]",
  );

  botoesFecharMensagem.forEach(function (botao) {
    botao.addEventListener("click", function () {
      const mensagem = botao.closest(".mensagem-banco");

      if (mensagem) {
        mensagem.remove();
      }
    });
  });

  /* =================================================
           DETALHES

           Ainda não temos a tela/modal de detalhes.
           Deixamos preparado para o próximo passo.
        ================================================= */

  const botoesDetalhes = document.querySelectorAll("[data-detalhes]");

  botoesDetalhes.forEach(function (botao) {
    botao.addEventListener("click", function () {
      const id = botao.dataset.id;

      alert(
        "Detalhes da questão #" + id + " serão implementados na próxima etapa.",
      );
    });
  });

  document.querySelectorAll("[data-excluir-questao]").forEach(function (formulario) {
    formulario.addEventListener("submit", function (evento) {
      if (!window.confirm("Deseja excluir esta questão? Esta ação não poderá ser desfeita.")) {
        evento.preventDefault();
      }
    });
  });
});
