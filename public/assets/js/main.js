document.addEventListener("DOMContentLoaded", function () {
  /* =====================================================
       ELEMENTOS
    ===================================================== */

  const musica = document.getElementById("musicaAbertura");

  const telaInicial = document.getElementById("telaInicialAudio");

  const botaoIniciar = document.getElementById("btnIniciarExperiencia");

  /* =====================================================
       VERIFICA SE A EXPERIÊNCIA JÁ FOI INICIADA
    ===================================================== */

  const experienciaIniciada = sessionStorage.getItem("desafioIniciado");

  /*
   * Se o usuário já clicou em INICIAR DESAFIO
   * anteriormente nesta aba, não mostramos novamente
   * a tela de abertura.
   */

  if (experienciaIniciada === "1") {
    if (telaInicial) {
      telaInicial.remove();
    }

    return;
  }

  /* =====================================================
       VALIDA ELEMENTOS
    ===================================================== */

  if (!musica || !telaInicial || !botaoIniciar) {
    return;
  }

  /* =====================================================
       VOLUME
    ===================================================== */

  musica.volume = 0.35;

  /* =====================================================
       INICIAR EXPERIÊNCIA
    ===================================================== */

  botaoIniciar.addEventListener("click", async function () {
    /*
     * Registra que o usuário já passou pela
     * tela inicial nesta aba do navegador.
     */

    sessionStorage.setItem("desafioIniciado", "1");

    /* =============================================
               INICIA ÁUDIO
            ============================================= */

    try {
      await musica.play();
    } catch (erro) {
      console.error("Não foi possível iniciar o áudio:", erro);
    }

    /* =============================================
               REMOVE TELA DE ABERTURA
            ============================================= */

    telaInicial.classList.add("oculta");

    setTimeout(function () {
      telaInicial.remove();
    }, 500);
  });
});
