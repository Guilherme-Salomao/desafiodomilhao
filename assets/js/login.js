document.addEventListener("DOMContentLoaded", function () {
  /* =====================================================
       ELEMENTOS
    ===================================================== */

  const formulario = document.getElementById("formLogin");

  const login = document.getElementById("loginUsuario");

  const senha = document.getElementById("senhaUsuario");

  const btnMostrarSenha = document.getElementById("btnMostrarSenha");

  /* =====================================================
       MOSTRAR / OCULTAR SENHA
    ===================================================== */

  if (btnMostrarSenha && senha) {
    btnMostrarSenha.addEventListener("click", function () {
      const mostrando = senha.type === "text";

      senha.type = mostrando ? "password" : "text";

      const icone = btnMostrarSenha.querySelector("i");

      if (icone) {
        icone.className = mostrando
          ? "fa-solid fa-eye"
          : "fa-solid fa-eye-slash";
      }

      btnMostrarSenha.title = mostrando ? "Mostrar senha" : "Ocultar senha";

      btnMostrarSenha.setAttribute("aria-label", btnMostrarSenha.title);
    });
  }

  /* =====================================================
       NORMALIZA LOGIN
    ===================================================== */

  if (login) {
    login.addEventListener("input", function () {
      login.value = login.value.toLowerCase().replace(/[^a-z0-9._-]/g, "");
    });
  }

  /* =====================================================
       FECHAR MENSAGENS
    ===================================================== */

  const botoesFecharMensagem = document.querySelectorAll(
    "[data-fechar-mensagem]",
  );

  botoesFecharMensagem.forEach(function (botao) {
    botao.addEventListener("click", function () {
      const mensagem = botao.closest(".mensagem-login");

      if (mensagem) {
        mensagem.remove();
      }
    });
  });

  /* =====================================================
       VALIDA FORMULÁRIO
    ===================================================== */

  if (formulario) {
    formulario.addEventListener("submit", function (evento) {
      /* =================================================
                   LOGIN
                ================================================= */

      if (!login || login.value.trim().length < 3) {
        evento.preventDefault();

        alert("Informe um login válido.");

        if (login) {
          login.focus();
        }

        return;
      }

      /* =================================================
                   SENHA
                ================================================= */

      if (!senha || senha.value.length === 0) {
        evento.preventDefault();

        alert("Informe sua senha.");

        if (senha) {
          senha.focus();
        }

        return;
      }
    });
  }
});
