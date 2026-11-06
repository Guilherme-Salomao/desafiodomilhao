<?php

    /* =========================================================
   CONFIGURAÇÕES GERAIS DO SISTEMA
========================================================= */

    $siteNome = $siteNome ?? 'Desafio do Milhão';

    /* =========================================================
   SUBTÍTULO / ÁREA ATUAL
========================================================= */

    /*
   No futuro poderá vir de:

   - sessão;
   - banco de dados;
   - parâmetro da URL;
   - seleção feita pelo usuário.

   Exemplos:

   - Manutenção de Computadores
   - Redes de Computadores
   - Desenvolvimento Web
   - Sistemas Operacionais
*/

    $siteSubtitulo = $siteSubtitulo ?? 'Escolha seu desafio';

    /* =========================================================
   CONFIGURAÇÕES DA PÁGINA
========================================================= */

    $paginaTitulo = $paginaTitulo ??
    $siteNome . ' - ' . $siteSubtitulo;

    $paginaDescricao = $paginaDescricao ??
    'Jogo educativo de perguntas e desafios sobre ' .
    $siteSubtitulo . '.';

    $paginaImagem = $paginaImagem ??
    '/desafiodomilhao/assets/img/logo/logo-compartilhamento.png';

    $paginaUrl = $paginaUrl ??
    'http://localhost/desafiodomilhao/';

    $paginaTipo = $paginaTipo ?? 'website';

    /*
   CSS específico da página.

   Exemplos:

   areas.php
   $paginaCss = '/desafiodomilhao/assets/css/areas.css';

   jogo.php
   $paginaCss = '/desafiodomilhao/assets/css/jogo.css';

   Se não for informado, somente style.css será carregado.
*/

    $paginaCss = $paginaCss ?? null;

    /* =========================================================
   DESENVOLVEDOR
========================================================= */

    $desenvolvedor = $desenvolvedor ??
    'Guilherme Salomão Shorane';

    /* =========================================================
   FUNÇÕES AUXILIARES
========================================================= */

    if (! function_exists('escapar')) {

    function escapar($valor)
    {
        return htmlspecialchars(
            $valor,
            ENT_QUOTES,
            'UTF-8'
        );
    }

    }

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

  <!-- =====================================================
         CONFIGURAÇÕES BÁSICAS
    ====================================================== -->

  <meta charset="UTF-8">

  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <meta http-equiv="X-UA-Compatible" content="IE=edge">


  <!-- =====================================================
         TÍTULO DA PÁGINA
    ====================================================== -->

  <title>
    <?php echo escapar($paginaTitulo); ?>
  </title>


  <!-- =====================================================
         SEO
    ====================================================== -->

  <meta name="description" content="<?php echo escapar($paginaDescricao); ?>">

  <meta name="robots" content="index, follow">

  <meta name="googlebot" content="index, follow">

  <meta name="author" content="<?php echo escapar($desenvolvedor); ?>">

  <meta name="copyright" content="<?php echo escapar($desenvolvedor); ?>">

  <meta name="application-name" content="<?php echo escapar($siteNome); ?>">


  <!-- =====================================================
         URL CANÔNICA
    ====================================================== -->

  <link rel="canonical" href="<?php echo escapar($paginaUrl); ?>">


  <!-- =====================================================
         OPEN GRAPH
         WhatsApp / Facebook / LinkedIn / Discord
    ====================================================== -->

  <meta property="og:locale" content="pt_BR">

  <meta property="og:type" content="<?php echo escapar($paginaTipo); ?>">

  <meta property="og:title" content="<?php echo escapar($paginaTitulo); ?>">

  <meta property="og:description" content="<?php echo escapar($paginaDescricao); ?>">

  <meta property="og:url" content="<?php echo escapar($paginaUrl); ?>">

  <meta property="og:site_name" content="<?php echo escapar($siteNome); ?>">

  <meta property="og:image" content="<?php echo escapar($paginaImagem); ?>">


  <!-- =====================================================
         X / TWITTER
    ====================================================== -->

  <meta name="twitter:card" content="summary_large_image">

  <meta name="twitter:title" content="<?php echo escapar($paginaTitulo); ?>">

  <meta name="twitter:description" content="<?php echo escapar($paginaDescricao); ?>">

  <meta name="twitter:image" content="<?php echo escapar($paginaImagem); ?>">


  <!-- =====================================================
         NAVEGADORES / DISPOSITIVOS
    ====================================================== -->

  <meta name="theme-color" content="#061221">

  <meta name="color-scheme" content="dark">

  <meta name="mobile-web-app-capable" content="yes">

  <meta name="apple-mobile-web-app-capable" content="yes">

  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

  <meta name="apple-mobile-web-app-title" content="<?php echo escapar($siteNome); ?>">


  <!-- =====================================================
         PRIVACIDADE
    ====================================================== -->

  <meta name="referrer" content="strict-origin-when-cross-origin">


  <!-- =====================================================
         FAVICON
    ====================================================== -->

  <link rel="icon" type="image/jpeg" sizes="32x32" href="/desafiodomilhao/assets/img/logo/favicon-32x32.jpeg">

  <link rel="icon" type="image/jpeg" sizes="16x16" href="/desafiodomilhao/assets/img/logo/favicon-16x16.jpeg">

  <link rel="apple-touch-icon" sizes="180x180" href="/desafiodomilhao/assets/img/logo/apple-touch-icon.jpeg">


  <!-- =====================================================
         CSS PRINCIPAL
    ====================================================== -->

  <link rel="stylesheet" href="/desafiodomilhao/assets/css/style.css">


  <!-- =====================================================
         CSS ESPECÍFICO DA PÁGINA
    ====================================================== -->

  <?php if (! empty($paginaCss)): ?>

  <link rel="stylesheet" href="<?php echo escapar($paginaCss); ?>">

  <?php endif; ?>


  <!-- =====================================================
         FONT AWESOME LOCAL
    ====================================================== -->

  <link rel="stylesheet" href="/desafiodomilhao/assets/fontawesome/css/all.min.css">


  <!-- =====================================================
         DADOS ESTRUTURADOS
         SCHEMA.ORG / GOOGLE
    ====================================================== -->

  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "WebApplication",
    "name": "<?php echo escapar($siteNome); ?>",
    "description": "<?php echo escapar($paginaDescricao); ?>",
    "applicationCategory": "EducationalApplication",
    "operatingSystem": "Web",
    "inLanguage": "pt-BR",
    "about": {
      "@type": "Thing",
      "name": "<?php echo escapar($siteSubtitulo); ?>"
    },
    "author": {
      "@type": "Person",
      "name": "<?php echo escapar($desenvolvedor); ?>"
    }
  }
  </script>

</head>


<body>

  <!-- =====================================================
         OVERLAY GLOBAL
    ====================================================== -->

  <div class="overlay"></div>