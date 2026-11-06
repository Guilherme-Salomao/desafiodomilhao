document.addEventListener("DOMContentLoaded", function () {
  const formulario = document.getElementById("formularioPergunta");

  const blocoResposta = document.getElementById("blocoRespostaCorreta");

  const mensagemValidacao = document.getElementById(
    "mensagemValidacaoPergunta",
  );

  const textoMensagemValidacao = document.getElementById(
    "textoMensagemValidacaoPergunta",
  );

  function fecharMensagem(mensagem) {
    mensagem.hidden = true;
  }

  function exibirErroValidacao(mensagem) {
    if (!mensagemValidacao || !textoMensagemValidacao) {
      return;
    }

    textoMensagemValidacao.textContent = mensagem;

    mensagemValidacao.hidden = false;

    mensagemValidacao.scrollIntoView({
      behavior: "smooth",
      block: "center",
    });
  }

  document.querySelectorAll("[data-fechar-mensagem]").forEach(function (botao) {
    botao.addEventListener("click", function () {
      const mensagem = botao.closest(".mensagem-status-pergunta");

      if (mensagem) {
        fecharMensagem(mensagem);
      }
    });
  });

  if (!formulario) {
    return;
  }

  formulario.addEventListener("submit", function (evento) {
    const respostaCorreta = formulario.querySelector(
      'input[name="resposta_correta"]:checked',
    );

    if (respostaCorreta) {
      if (blocoResposta) {
        blocoResposta.classList.remove("invalida");
      }

      return;
    }

    evento.preventDefault();

    if (blocoResposta) {
      blocoResposta.classList.add("invalida");
    }

    exibirErroValidacao("Selecione qual alternativa é a resposta correta.");
  });

  formulario.querySelectorAll('input[name="resposta_correta"]').forEach(
    function (opcao) {
      opcao.addEventListener("change", function () {
        if (blocoResposta) {
          blocoResposta.classList.remove("invalida");
        }

        if (mensagemValidacao) {
          fecharMensagem(mensagemValidacao);
        }
      });
    },
  );
});
