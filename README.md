# 🏆 Desafio do Milhão

<div align="center">

### 💰 Acerte. Avance. Conquiste o milhão!

Um jogo educativo de perguntas e respostas desenvolvido para transformar conteúdos de diferentes áreas em uma experiência divertida, competitiva e interativa.

**PHP • MySQL • JavaScript • HTML • CSS**

</div>

---

## 🎮 Sobre o projeto

O **Desafio do Milhão** é uma plataforma web educativa inspirada em jogos de perguntas e respostas por premiação.

O jogador escolhe uma área de conhecimento, configura sua partida e começa o desafio tentando avançar pergunta por pergunta até alcançar o prêmio máximo de:

# 💰 R$ 1.000.000

Mas cuidado... 👀

Uma resposta errada pode encerrar a partida!

---

## 🧠 Como funciona?

Cada partida possui uma sequência de perguntas organizadas por nível de dificuldade.

O jogador deve responder corretamente para subir na escala de premiação:

| Pergunta | Prêmio |
|---:|---:|
| 1 | R$ 1.000 |
| 2 | R$ 2.000 |
| 3 | R$ 5.000 |
| 4 | R$ 10.000 |
| 5 | R$ 20.000 |
| 6 | R$ 50.000 |
| 7 | R$ 100.000 |
| 8 | R$ 200.000 |
| 9 | R$ 500.000 |
| 10 | 🏆 **R$ 1.000.000** |

✅ Acertou? Avança para a próxima pergunta.

❌ Errou? **GAME OVER!**

⏱️ O tempo acabou? Também é **GAME OVER!**

O prêmio final corresponde ao último nível conquistado antes do erro.

---

## 🆘 Ajudas disponíveis

Durante a partida, o jogador possui recursos especiais que podem ser utilizados uma única vez.

### ✂️ 50/50

Remove duas alternativas incorretas da pergunta.

### 👨‍🔧 Consultar o Técnico

Exibe uma orientação para ajudar o jogador a analisar a questão.

### 👥 Consultar a Turma

Apresenta uma votação simulada mostrando a opinião da turma para cada alternativa.

### 🔄 Trocar Pergunta

Substitui a pergunta atual por outra do mesmo nível de dificuldade.

> As ajudas utilizadas permanecem registradas durante toda a partida e não são recuperadas ao trocar de pergunta.

---

## 🎯 Modos de jogo

### 👤 1 Player

Modo tradicional.

Um jogador responde às perguntas e tenta chegar ao prêmio máximo.

### 👥 2 Players

Modo competitivo preparado para partidas entre dois jogadores.

### 🎓 Modo Professor

Modo pensado para utilização em sala de aula.

O professor controla o andamento das perguntas sem necessidade de cronômetro, permitindo discutir alternativas, revelar respostas e utilizar o jogo como ferramenta pedagógica.

---

## 📚 Áreas de conhecimento

A plataforma foi desenvolvida para trabalhar com múltiplas áreas.

Entre as áreas atualmente previstas estão:

- 🔧 Manutenção de Computadores
- 🌐 Redes de Computadores
- 💻 Software
- 💊 Farmácia
- 📊 Administração
- ❤️ Saúde

A arquitetura permite adicionar novas áreas futuramente.

---

## 🗃️ Banco de Questões

O projeto possui uma área administrativa para gerenciamento das perguntas.

Cada usuário autorizado pode acessar somente as áreas liberadas para sua conta.

É possível trabalhar com:

- cadastro de perguntas;
- alternativas A, B, C e D;
- definição da resposta correta;
- explicação da resposta;
- nível de dificuldade;
- área de conhecimento;
- ativação e desativação de questões;
- filtros por área;
- filtros por dificuldade;
- filtros por status;
- edição das perguntas.

---

## 🔐 Controle de acesso

O **Banco de Questões** possui acesso restrito.

O usuário pode solicitar um cadastro, mas a conta é criada inicialmente como:

```text
INATIVA
```

O acesso somente é permitido após autorização do administrador.

O sistema utiliza:

- 🔒 senhas com hash;
- 🛡️ sessões PHP;
- 🗄️ PDO;
- ✅ prepared statements;
- 🔐 validações no backend;
- 🎯 controle de áreas autorizadas por usuário.

---

## ⚡ Níveis de dificuldade

As perguntas podem ser classificadas como:

```text
🟢 Fácil
🟡 Médio
🔴 Difícil
```

A configuração da partida também poderá utilizar uma combinação de dificuldades.

---

## ⏱️ Cronômetro

Cada nível pode trabalhar com um tempo específico para resposta.

Exemplo:

| Dificuldade | Tempo |
|---|---:|
| 🟢 Fácil | 20 segundos |
| 🟡 Mista | 30 segundos |
| 🔴 Difícil | 45 segundos |

Nos últimos segundos o cronômetro entra em estado crítico. 🚨

Se chegar a **0**:

# 💀 TEMPO ESGOTADO!

---

## 🎨 Interface

A interface foi desenvolvida com uma identidade visual inspirada em:

🎮 games  
💰 programas de perguntas e respostas  
🔵 tecnologia  
🟡 premiação  
🌌 ambientes digitais  

O jogo possui:

- interface responsiva;
- painel lateral;
- escala visual de premiação;
- animações e efeitos;
- indicadores de respostas;
- telas de acerto e erro;
- painel GAME OVER;
- cards de ajuda;
- identidade visual por área.

---

## 🛠️ Tecnologias utilizadas

### Backend

```text
PHP 8+
MySQL
PDO
Sessions
```

### Frontend

```text
HTML5
CSS3
JavaScript
Bootstrap
Font Awesome
```

### Ambiente de desenvolvimento

```text
XAMPP
Apache
MySQL
Visual Studio Code
```

---

## 🏗️ Estrutura do projeto

```text
desafiodomilhao/
│
├── admin/
│   ├── editar-pergunta.php
│   ├── index.php
│   ├── nova-pergunta.php
│   ├── pergunta-cadastrar.php
│   ├── perguntas.php
│   └── resultados.php
│
├── api/
│   └── jogo.php
│
├── assets/
│   ├── audio/
│   ├── css/
│   ├── img/
│   └── js/
│
├── database/
│
├── includes/
│   ├── repositories/
│   │   ├── AreaRepository.php
│   │   ├── PerguntaRepository.php
│   │   └── UsuarioRepository.php
│   │
│   ├── autenticacao.php
│   ├── conexao.php
│   ├── footer.php
│   ├── funcoes.php
│   ├── header.php
│   └── menu.php
│
├── areas.php
├── cadastro.php
├── configuracoes.php
├── configurar-partida.php
├── index.php
├── iniciar-partida.php
├── jogo.php
├── login.php
├── processar-cadastro.php
├── processar-login.php
├── professor.php
├── proxima-pergunta.php
├── ranking.php
└── selecionar-area.php
```

---

## 🗄️ Arquitetura

O acesso aos dados segue uma separação simples entre interface, repositórios e banco de dados:

```text
Interface / Página
        ↓
    Repository
        ↓
    conexao.php
        ↓
 Configuração externa
        ↓
      MySQL
```

Essa estrutura facilita a manutenção e reduz a presença de consultas SQL diretamente nas páginas da aplicação.

---

## 🚀 Executando localmente

### 1️⃣ Clone o repositório

```bash
git clone URL_DO_REPOSITORIO
```

### 2️⃣ Coloque o projeto no XAMPP

Exemplo:

```text
D:\xampp\htdocs\desafiodomilhao
```

### 3️⃣ Inicie

No painel do XAMPP:

```text
Apache  → Start
MySQL   → Start
```

### 4️⃣ Configure o banco

Crie o banco:

```text
desafiodomilhao
```

e importe a estrutura SQL utilizada pelo projeto.

### 5️⃣ Configure a conexão

As credenciais do banco devem permanecer **fora da pasta pública `htdocs`**.

Exemplo:

```text
config/database.php

> Em produção, a aplicação deve ser acessada por
> `https://www.guilhermeshorane.com.br/github/desafiodomilhao/public/`.
> Somente a pasta `public/` contém recursos acessíveis pelo navegador.
> Copie `config/database.example.php` para `config/database.php`, preencha
> as credenciais do servidor e mantenha esse arquivo fora do Git.

O arquivo aceita duas conexões: `local` e `production`. A escolha é
automática: `localhost` usa `local` e o domínio publicado usa `production`.
Se necessário, defina a variável `APP_ENV` como `local` ou `production` para
forçar um ambiente.
```

### 6️⃣ Abra no navegador

```text
http://localhost/desafiodomilhao/public/
```

---

## 🔒 Atenção com dados sensíveis

Nunca envie para o GitHub:

```text
senhas
credenciais de banco
arquivos .env
chaves de API
configurações privadas
backups contendo dados reais
```

Utilize o `.gitignore` para impedir que arquivos sensíveis sejam versionados.

---

## 🚧 Projeto em desenvolvimento

O **Desafio do Milhão** continua evoluindo.

Entre os recursos que podem ser ampliados estão:

- 🏆 ranking completo;
- 👥 competição entre jogadores;
- 📊 estatísticas das partidas;
- 🧑‍🏫 ferramentas adicionais para professores;
- 🖼️ perguntas com imagens;
- 🔊 efeitos sonoros;
- 🎵 trilhas de áudio;
- 📈 histórico de desempenho;
- 🏅 conquistas;
- 📱 melhorias para dispositivos móveis;
- 🌎 novas áreas de conhecimento.

---

## 🎓 Objetivo educacional

Mais do que um jogo, o projeto foi pensado como uma ferramenta para apoiar o processo de ensino e aprendizagem.

A proposta é transformar atividades tradicionais de perguntas e respostas em uma experiência mais:

**🎮 divertida • 🧠 desafiadora • 🤝 participativa • 🏆 competitiva**

---

<div align="center">

# ❓ DESAFIO DO MILHÃO

### Você consegue chegar ao milhão? 💰🏆

**Escolha sua área. Responda. Avance. Vença.**

---

Desenvolvido por **Guilherme Salomão Shorane**

💻 Tecnologia • 🎓 Educação • 🎮 Gamificação

</div>
