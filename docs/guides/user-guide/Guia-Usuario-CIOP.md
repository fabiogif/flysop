# Guia do Usuário — CIOP

**Central Inteligente de Ocorrências Públicas**

| | |
|---|---|
| **Versão** | 1.0 |
| **Data** | 11/08/2026 |
| **Público** | Operadores, supervisores, administradores e agentes de campo |
| **Ambiente local de referência** | http://127.0.0.1:8010 |

Este guia ensina a **navegar na aplicação**: site público, login, painel administrativo e área do motorista. As imagens foram capturadas da interface real.

---

## Sumário

1. [O que é o CIOP](#1-o-que-é-o-ciop)
2. [Stacks utilizadas](#2-stacks-utilizadas)
3. [Perfis de acesso](#3-perfis-de-acesso)
4. [Acesso e login](#4-acesso-e-login)
5. [Mapa do menu](#5-mapa-do-menu)
6. [Painel de Controle](#6-painel-de-controle)
7. [Notificações](#7-notificações)
8. [Ocorrências](#8-ocorrências)
9. [Central de Despacho](#9-central-de-despacho)
10. [Usuários e acesso](#10-usuários-e-acesso)
11. [Pesquisas](#11-pesquisas)
12. [Painel do Motorista](#12-painel-do-motorista)
13. [Fluxo operacional recomendado](#13-fluxo-operacional-recomendado)
14. [Dicas e atalhos](#14-dicas-e-atalhos)

---

## 1. O que é o CIOP

O CIOP é uma plataforma web para **registrar, priorizar, despachar e acompanhar ocorrências públicas** (buracos, alagamentos, reparos de vias e outras demandas de órgãos públicos), com:

- mapa e geolocalização;
- protocolo e prioridades;
- painel do agente de campo com GPS;
- notificações, auditoria e multi-organização (tenant).

---

## 2. Stacks utilizadas

### Backend

| Tecnologia | Uso |
|---|---|
| **PHP 8.1** + **Laravel 8** | Aplicação monolítica (web + API) |
| **PostgreSQL** | Banco principal |
| **Redis** + fila | Jobs assíncronos |
| **Laravel Sanctum** | Autenticação da API |
| **laravel/ui** | Login web |
| **Spatie Activity Log** | Auditoria |
| **Pusher PHP** + **Soketi** | Broadcast / tempo real |
| **DomPDF** / **Maatwebsite Excel** | Relatórios exportáveis |
| **AWS S3 / MinIO** | Arquivos e evidências |

### Frontend / UI

| Tecnologia | Uso |
|---|---|
| **AdminLTE 3** + Bootstrap 4 | Shell do painel |
| **Blade** | Templates server-side |
| **Vue 2** | Componentes pontuais (ex.: tracker) |
| **Leaflet** + OpenStreetMap | Mapas (sem Google billing) |
| **Chart.js** | Gráficos do dashboard |
| **Laravel Echo** + **pusher-js** | Atualização em tempo real |
| CSS custom **ciop-admin** / **site-theme** | Identidade visual teal CIOP |

### Infra

| Tecnologia | Uso |
|---|---|
| **Docker Compose** | Ambiente local |
| **Fly.io** | Deploy |
| **PHPUnit** | Testes automatizados |

---

## 3. Perfis de acesso

| Perfil | O que faz no sistema |
|---|---|
| **Administrador** | Configura organização, usuários, cargos, permissões e parâmetros |
| **Supervisor / Central** | Triagem, despacho, SLA, mapa e acompanhamento |
| **Atendente** | Abertura e acompanhamento de ocorrências |
| **Motorista / Agente** | Aceita ocorrências, atualiza status e envia GPS |

O menu lateral **só mostra itens permitidos** para o seu cargo.

---

## 4. Acesso e login

### 4.1 Site público (landing)

1. Abra a URL da aplicação (ex.: `http://127.0.0.1:8010/`).
2. Use **Acessar** ou **Acessar o sistema** para ir ao login.
3. Navegue pelas âncoras: Obras, Recursos, Mapa, Como funciona.

![Landing page CIOP](screenshots/01-landing.png)

### 4.2 Tela de login

1. Informe **e-mail** e **senha**.
2. Opcional: marque **Lembrar-me**.
3. Clique em **Entrar**.
4. Após o login, você é direcionado ao **Painel de Controle** (admin) ou ao **Painel do Motorista**, conforme permissões.

![Tela de login](screenshots/02-login.png)

> Esqueceu a senha? Use o link **Esqueci minha senha** na própria tela de login.

---

## 5. Mapa do menu

Com a sessão autenticada, o menu esquerdo organiza as áreas:

| Grupo | Itens | Para quê |
|---|---|---|
| **Topo** | Painel de Controle, Notificações, Busca, Relatórios | Visão geral e consulta |
| **Ocorrências** | Ocorrências, Central de Despacho, Parâmetros (Status, Tipos, Prioridades, Órgãos) | Operação do dia a dia |
| **Equipes** | Motoristas, Departamentos, Equipes | Estrutura de campo |
| **Acesso** | Usuários, Perfis, Cargos, Permissões, Auditoria | Segurança e ACL |
| **Engajamento** | Pesquisas | Formulários / feedback |
| **Sistema** | Empresas, Organização | Tenant e configurações |
| **Área do Motorista** | Painel Motorista, Minhas Ocorrências | Operação em campo |

No topo direito, o nome do usuário permite **Sair**.

---

## 6. Painel de Controle

**URL:** `/admin`

É a home operacional da central.

### O que você encontra

1. **Indicadores — Cadastros:** usuários, órgãos, tipos, total de ocorrências (com atalho **Abrir**).
2. **Indicadores — Operação:** abertas, em atendimento, finalizadas hoje, SLA estourado.
3. **Filtros:** status, tipo, prioridade, motorista e período.
4. **Ocorrências recentes** + **mapa** (Leaflet) lado a lado no desktop.
5. **Análises:** gráficos por dia, status e prioridade.
6. **Exportar uso:** CSV com métricas da organização.

![Painel de Controle](screenshots/03-dashboard.png)

### Como usar

1. Leia os cards de operação para priorizar o dia.
2. Aplique filtros e clique em **Aplicar filtros**.
3. Clique em uma ocorrência recente para abrir o detalhe.
4. No mapa, use **Mapa de calor** se quiser densidade espacial.
5. Role até **Análises** para ver tendência dos últimos 14 dias.

---

## 7. Notificações

**URL:** `/admin/notifications`

### O que você encontra

- Cards de resumo: **Não lidas**, **Total nesta página**, **Todas**.
- Lista em cards com protocolo, mensagem, horário e ações.
- Botão **Marcar todas como lidas**.

![Notificações](screenshots/04-notifications.png)

### Como usar

1. Identifique alertas de SLA (ícone de alerta).
2. Clique em **Marcar como lida** (ou **Abrir**, se já lida) para seguir o link da ocorrência.
3. Use **Marcar todas como lidas** ao limpar a caixa.

---

## 8. Ocorrências

**URL:** `/admin/occurrences`

### O que você encontra

- Busca por título, protocolo ou solicitante.
- Tabela com protocolo, tipo, prioridade, órgão, status e data.
- Ações: **Ver**, **Editar**, painel do motorista.
- Botão **+ Adicionar** para nova ocorrência.

![Lista de ocorrências](screenshots/05-occurrences.png)

### Como cadastrar (resumo)

1. Clique em **Adicionar**.
2. Preencha título, endereço/coordenadas, tipo, prioridade, órgão e status.
3. Anexe evidências, se necessário.
4. Salve e, se for o caso, atribua motorista/equipe.

---

## 9. Central de Despacho

**URL:** `/admin/dispatch`

Console para **triagem e despacho** com lista e mapa.

### Recursos

- Chips rápidos: Todas, Críticas, Últimas 24h, Em aberto, Sem atendimento, Próximas.
- Filtros avançados (status, tipo, prioridade, motorista, datas).
- Lista + mapa operacional.

![Central de Despacho](screenshots/06-dispatch.png)

### Como despachar

1. Filtre ocorrências abertas / sem atendimento.
2. Selecione a ocorrência na lista.
3. Atribua o motorista mais adequado (sugestão por proximidade, quando disponível).
4. Acompanhe o deslocamento no mapa.

---

## 10. Usuários e acesso

**URL:** `/admin/users`

### O que você encontra

- Lista de usuários (nome e e-mail).
- **Convidar** (e-mail) e **+ Adicionar**.
- Ações: ver, editar, cargos, remover.

![Usuários](screenshots/07-users.png)

### Boas práticas

1. Convide por e-mail com o **cargo inicial** correto.
2. Ajuste cargos em **Cargos** / permissões em **Permissões**.
3. Consulte **Auditoria** para logins e mudanças sensíveis.
4. Em **Organização**, revise dados do tenant atual.

---

## 11. Pesquisas

**URL:** `/admin/surveys`

Crie pesquisas com perguntas e acompanhe respostas.

![Pesquisas](screenshots/09-surveys.png)

1. Clique em **Adicionar**.
2. Defina título, status e perguntas.
3. Publique (**Ativa**) e compartilhe o link/formulario conforme o fluxo da organização.
4. Use **Respostas** para analisar retornos.

---

## 12. Painel do Motorista

**URL:** `/driver/dashboard`

Área do agente de campo.

### O que você encontra

- Cards: aguardando aceitação, em andamento, total atribuídas.
- Lista de ocorrências recentes com status e botão **Ver**.

![Painel do Motorista](screenshots/08-driver.png)

### Fluxo em campo

1. Abra **Minhas Ocorrências**.
2. **Aceite** ou **recuse** a atribuição.
3. Atualize o status (ex.: em deslocamento, no local, finalizada).
4. Mantenha o GPS ativo para a central acompanhar no mapa.

---

## 13. Fluxo operacional recomendado

```text
Abertura  →  Triagem  →  Despacho  →  Campo (GPS/status)  →  Conclusão
   |            |            |                |                 |
Ocorrências  Notificações  Despacho     Painel Motorista   Histórico/SLA
```

1. Atendente registra a ocorrência.
2. Supervisor prioriza e despacha.
3. Motorista aceita e atualiza status/GPS.
4. Central acompanha no Painel e nas Notificações.
5. Encerramento com evidências e histórico.

---

## 14. Dicas e atalhos

- Use o **menu hambúrguer** no mobile para abrir/fechar a sidebar.
- No Painel, os indicadores **Atualizam** a lista/mapa periodicamente (cerca de 1 minuto).
- Prefira filtros antes de varrer tabelas grandes.
- Em caso de erro ao salvar (ex.: pesquisas), atualize a página e tente novamente; se persistir, contate o administrador técnico.
- Faça **logout** ao usar computador compartilhado.

---

## Glossário rápido

| Termo | Significado |
|---|---|
| **Ocorrência** | Registro de demanda/atendimento público |
| **Protocolo** | Identificador único (ex.: OC-2026-00001) |
| **SLA** | Prazo-alvo de atendimento |
| **Tenant / Organização** | Prefeitura ou órgão isolado no multi-tenant |
| **Despacho** | Atribuição da ocorrência a um motorista/equipe |
| **Leaflet** | Biblioteca de mapas usada no painel |

---

## Suporte

Para dúvidas de operação, fale com o administrador da organização.  
Para questões técnicas de infraestrutura, consulte a equipe responsável pelo deploy (Docker / Fly.io).

---

*Documento gerado a partir da navegação real da aplicação CIOP. Não contém senhas, tokens ou chaves privadas.*
