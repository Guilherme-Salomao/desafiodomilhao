document.addEventListener("DOMContentLoaded", function () {
  /* =====================================================
       ELEMENTOS
    ===================================================== */

  const formulario = document.getElementById("formCadastroUsuario");

  const nome = document.getElementById("nomeUsuario");

  const login = document.getElementById("loginUsuario");

  const senha = document.getElementById("senhaUsuario");

  const confirmarSenha = document.getElementById("confirmarSenhaUsuario");

  const areas = document.querySelectorAll('input[name="areas[]"]');

  const textoAreas = document.getElementById("textoAreasSelecionadas");

  /* =====================================================
       REQUISITOS
    ===================================================== */

  const requisitoTamanho = document.getElementById("requisitoTamanho");

  const requisitoMaiuscula = document.getElementById("requisitoMaiuscula");

  const requisitoMinuscula = document.getElementById("requisitoMinuscula");

  const requisitoNumero = document.getElementById("requisitoNumero");

  /* =====================================================
       MOSTRAR / ESCONDER SENHA
    ===================================================== */

  const botoesMostrar = document.querySelectorAll(".botao-mostrar-senha");

  botoesMostrar.forEach(function (botao) {
    botao.addEventListener("click", function () {
      const idAlvo = botao.dataset.alvo;

      const campo = document.getElementById(idAlvo);

      if (!campo) {
        return;
      }

      const mostrando = campo.type === "text";

      campo.type = mostrando ? "password" : "text";

      const icone = botao.querySelector("i");

      if (icone) {
        icone.className = mostrando
          ? "fa-solid fa-eye"
          : "fa-solid fa-eye-slash";
      }
    });
  });

  /* =====================================================
       VALIDA REQUISITOS
    ===================================================== */

  function validarRequisitosSenha() {
    const valor = senha ? senha.value : "";

    const tamanho = valor.length >= 8;

    const maiuscula = /[A-Z]/.test(valor);

    const minuscula = /[a-z]/.test(valor);

    const numero = /[0-9]/.test(valor);

    atualizarRequisito(requisitoTamanho, tamanho);

    atualizarRequisito(requisitoMaiuscula, maiuscula);

    atualizarRequisito(requisitoMinuscula, minuscula);

    atualizarRequisito(requisitoNumero, numero);

    return tamanho && maiuscula && minuscula && numero;
  }

  function atualizarRequisito(elemento, valido) {
    if (!elemento) {
      return;
    }

    elemento.classList.toggle("valido", valido);

    const icone = elemento.querySelector("i");

    if (icone) {
      icone.className = valido
        ? "fa-solid fa-circle-check"
        : "fa-solid fa-circle";
    }
  }

  if (senha) {
    senha.addEventListener("input", validarRequisitosSenha);
  }

  /* =====================================================
       CONTADOR DE ÁREAS
    ===================================================== */

  function atualizarAreasSelecionadas() {
    const selecionadas = Array.from(areas).filter(function (area) {
      return area.checked;
    });

    if (!textoAreas) {
      return;
    }

    if (selecionadas.length === 0) {
      textoAreas.textContent = "Nenhuma área selecionada";

      return;
    }

    if (selecionadas.length === 1) {
      textoAreas.textContent = "1 área selecionada";

      return;
    }

    textoAreas.textContent = selecionadas.length + " áreas selecionadas";
  }

  areas.forEach(function (area) {
    area.addEventListener("change", atualizarAreasSelecionadas);
  });

  /* =====================================================
       NORMALIZA LOGIN
    ===================================================== */

  if (login) {
    login.addEventListener("input", function () {
      login.value = login.value.toLowerCase().replace(/[^a-z0-9._-]/g, "");
    });
  }

  /* =====================================================
       VALIDA FORMULÁRIO
    ===================================================== */

  if (formulario) {
    formulario.addEventListener("submit", function (evento) {
      /* =================================================
                   NOME
                ================================================= */

      if (!nome || nome.value.trim().length < 3) {
        evento.preventDefault();

        alert("Informe seu nome completo.");

        if (nome) {
          nome.focus();
        }

        return;
      }

      /* =================================================
                   LOGIN
                ================================================= */

      if (!login || login.value.trim().length < 3) {
        evento.preventDefault();

        alert("O login deve possuir pelo menos 3 caracteres.");

        if (login) {
          login.focus();
        }

        return;
      }

      /* =================================================
                   SENHA
                ================================================= */

      if (!validarRequisitosSenha()) {
        evento.preventDefault();

        alert("A senha não atende aos requisitos mínimos.");

        if (senha) {
          senha.focus();
        }

        return;
      }

      /* =================================================
                   CONFIRMAÇÃO
                ================================================= */

      if (!confirmarSenha || senha.value !== confirmarSenha.value) {
        evento.preventDefault();

        alert("As senhas informadas não são iguais.");

        if (confirmarSenha) {
          confirmarSenha.focus();
        }

        return;
      }

      /* =================================================
                   ÁREAS
                ================================================= */

      const areasSelecionadas = Array.from(areas).filter(function (area) {
        return area.checked;
      });

      if (areasSelecionadas.length === 0) {
        evento.preventDefault();

        alert("Selecione pelo menos uma área de atuação.");

        return;
      }
    });
  }

  /* =====================================================
       INICIALIZA
    ===================================================== */

  validarRequisitosSenha();

  atualizarAreasSelecionadas();
});
