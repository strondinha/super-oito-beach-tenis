# 🎾 LetzPlay Beach Tennis — Super Oito

Sistema profissional de gerenciamento de torneios de beach tennis com fase de grupos (round-robin) e fase eliminatória em duplas.

---

## Sobre o Sistema

O **LetzPlay Beach Tennis** organiza torneios seguindo as normas oficiais da **ITF** (International Tennis Federation) e da **CBT** (Confederação Brasileira de Tênis), com sorteio de grupos, tabela automática de confrontos e classificação individual por saldo de games.

---

## Funcionalidades

- Cadastro de **8, 16, 24 ou 32 atletas**
- Sorteio automático de grupos de 8 (algoritmo Berger round-robin)
- Geração de tabela de confrontos: **7 rodadas × 4 partidas** por grupo
- Lançamento de placares por rodada (1 set de 4 games, sem tiebreak)
- Classificação individual por **saldo de games** com critérios de desempate
- Geração automática de **duplas para a fase eliminatória**
- Design responsivo com header e footer profissionais
- Impressão de tabela de classificação

---

## Regulamento

### 1. Regras Gerais
- Todos os jogos seguem as regras da **ITF** e **CBT**
- Rede a **1,70m** do solo em todos os confrontos

### 2. Número de Inscritos
- Mínimo: **8 atletas** | Máximo: **32 atletas** (grupos de 8)
- A diretoria pode alterar o formato em situações extraordinárias

### 3. Sistema de Disputa

| Regra | Detalhe |
|-------|---------|
| 3.1   | Atletas divididos em grupos de 8 por sorteio |
| 3.2   | Cada atleta joga contra todos do grupo (7 partidas, 1 set de 4 games, sem tiebreak) |
| 3.3   | Pontuação individual pelo saldo de games — cada game = 1 ponto |
| 3.4   | Os 2 melhores de cada grupo avançam; o sistema gera as duplas para as eliminatórias |
| 3.5   | Desempate: **1º** Mais vitórias · **2º** Menos derrotas · **3º** Sorteio |

---

## Requisitos

- PHP **7.4+**
- Servidor web local (Apache via XAMPP, Laragon, etc.)
- Navegador moderno
- **Sem banco de dados** — armazenamento em arquivos JSON

---

## Instalação

```bash
# 1. Clone ou baixe o projeto
git clone <url>

# 2. Copie para a pasta htdocs do XAMPP
cp -r Sistema_Super_Oito-main/ C:/xampp/htdocs/

# 3. Inicie o Apache no XAMPP

# 4. Acesse no navegador
http://localhost/Sistema_Super_Oito-main/
```

> **Atenção:** certifique-se de que a pasta `data/` tem permissão de escrita.

---

## Estrutura do Projeto

```
├── index.php                       # Página inicial (hero + painel + regulamento)
├── css/
│   └── style.css                   # Estilos globais (tema navy + dourado)
├── js/
│   └── ui.js                       # JavaScript (modal, AJAX, zerar)
├── includes/
│   ├── header.php                  # Cabeçalho reutilizável
│   └── footer.php                  # Rodapé reutilizável
├── participantes/
│   ├── cadastro.php                # Cadastro dinâmico de 8–32 atletas
│   └── salvar_participantes.php    # Validação e salvamento
├── configuracao/
│   ├── configuracao.php            # Resumo e botão de sorteio
│   └── gerar_rodadas.php           # Geração dos grupos e rodadas
├── rodadas/
│   ├── rodadas.php                 # Lançamento de placares por rodada
│   └── salvar_placar.php           # Validação e salvamento dos placares
├── classificacao/
│   └── classificacao.php           # Tabela por grupo + duplas eliminatórias
├── utils/
│   ├── json_helper.php             # Leitura/escrita de JSON
│   ├── sorteio.php                 # Algoritmo Berger round-robin
│   ├── pontuacao.php               # Cálculo de classificação individual
│   └── zerar.php                  # Reset completo do torneio
└── data/                           # Dados gerados automaticamente
    ├── participantes.json
    ├── rodadas.json
    └── classificacoes.json
```

---

## Fluxo do Torneio

```
1. Cadastrar Atletas (8/16/24/32)
        ↓
2. Sortear Grupos  →  grupos de 8 por sorteio aleatório
        ↓
3. Rodadas (7 por grupo)  →  lançar 4 placares por rodada
        ↓
4. Classificação  →  ranking individual por saldo de games
        ↓
5. Fase Eliminatória  →  duplas geradas automaticamente
```

---

## Tecnologias

| Camada    | Tecnologia                       |
|-----------|----------------------------------|
| Backend   | PHP 7.4+ (sem framework)         |
| Frontend  | HTML5, CSS3 (Grid/Flexbox/Vars)  |
| Scripts   | JavaScript ES2020 (Vanilla)      |
| Dados     | JSON (file-based)                |

---

*Sistema desenvolvido para o torneio Super Oito de Beach Tennis — LetzPlay.*
