# Plano de Melhorias e Novas Implementações – SOP

**Foco principal:** criação de ocorrências e monitoramento em tempo real do atendimento.

Este documento descreve melhorias e novas funcionalidades organizadas em dois eixos: **criação de ocorrências** e **monitoramento em tempo real do atendimento**, com priorização e ordem sugerida de implementação.

---

## Visão geral

| Eixo | Objetivo |
|------|----------|
| **1. Criação de ocorrências** | Tornar o registro de ocorrências mais rápido, confiável e acessível (web, mobile, canais alternativos). |
| **2. Monitoramento em tempo real** | Permitir acompanhar o status e o deslocamento do atendimento em tempo real (mapa, timeline, notificações). |

---

## Eixo 1 – Criação de ocorrências

### 1.1 Melhorias no fluxo atual (admin)

| Item | Descrição | Prioridade |
|------|-----------|------------|
| **Prioridade / urgência** | Campo de prioridade (baixa, média, alta, urgente) na ocorrência para ordenação e filtros no monitoramento. | Alta |
| **Número único** | Identificador único legível (ex.: `OC-2025-00042`) para comunicação com cidadão e equipe. | Alta |
| **Validação de endereço** | Garantir que ao menos um de: endereço preenchido ou lat/lng presente; feedback claro quando faltar. | Média |
| **Rascunho** | Salvar ocorrência como rascunho (status “Rascunho”) e permitir concluir depois. | Média |
| **Atalho no dashboard** | Botão “Nova ocorrência” em destaque no dashboard. | Baixa |

### 1.2 Criação por outros canais

| Item | Descrição | Prioridade |
|------|-----------|------------|
| **API pública** | Endpoint (ex.: POST `/api/occurrences`) para criação de ocorrência com token/API key, permitindo integração com apps e portais. | Alta |
| **Formulário público** | Página no site (sem login) para o cidadão registrar ocorrência; captura de dados + geolocalização; envio para fila do tenant. | Alta |
| **Webhook / integração** | Opção de notificar URL externa ao criar/atualizar ocorrência (integração com outros sistemas). | Média |

### 1.3 Experiência do formulário

| Item | Descrição | Prioridade |
|------|-----------|------------|
| **Localização inicial** | Manter mapa iniciando na localização atual do usuário (já implementado). | — |
| **Autocomplete de endereço** | Manter Places Autocomplete e reverse geocode (já implementado). | — |
| **Fotos no cadastro** | Upload múltiplo de fotos no create (já existe em edição); preview e ordem. | Média |
| **Campos condicionais** | Exibir campos adicionais conforme tipo de ocorrência (configurável por tipo). | Baixa |
| **Acessibilidade** | Labels, foco e mensagens de erro para leitores de tela; contraste e teclado. | Média |

### 1.4 Dados e consistência

| Item | Descrição | Prioridade |
|------|-----------|------------|
| **Escopo por tenant** | Garantir que listagens e APIs de ocorrências respeitem sempre o tenant do usuário (onde ainda não houver). | Alta |
| **Histórico de alterações** | Registrar quem alterou e quando (status, motorista, endereço); exibir na tela de detalhe. | Média |
| **Cliente/órgão** | Deixar explícito cliente/órgão (issuings) na ocorrência e em filtros. | Média |

---

## Eixo 2 – Monitoramento em tempo real do atendimento

### 2.1 Atualização em tempo real

| Item | Descrição | Prioridade |
|------|-----------|------------|
| **WebSockets / Laravel Echo** | Substituir ou complementar polling por push (Broadcasting) para lista e mapa atualizarem assim que uma ocorrência mudar. | Alta |
| **Eventos** | Disparar evento ao criar/atualizar ocorrência (ex.: `OccurrenceUpdated`); canal por tenant. | Alta |
| **Polling híbrido** | Manter polling como fallback quando WebSocket não estiver disponível. | Média |

### 2.2 Mapa ao vivo

| Item | Descrição | Prioridade |
|------|-----------|------------|
| **Mapa no dashboard** | Manter card “Mapa das ocorrências” com marcadores e atualização periódica (já implementado). | — |
| **Filtros no mapa** | Filtrar por status, tipo, data e motorista; atualizar marcadores e lista juntos. | Alta |
| **Agrupamento (clustering)** | Quando houver muitos marcadores, agrupar por proximidade (ex.: MarkerClusterer). | Média |
| **Posição do motorista** | Se houver app/API do motorista, exibir posição em tempo real no mapa e linha até a ocorrência. | Alta (fase posterior) |

### 2.3 Status e timeline

| Item | Descrição | Prioridade |
|------|-----------|------------|
| **Fluxo de status** | Definir fluxo claro: Aberta → Em atendimento → Em deslocamento (opcional) → Finalizada; transições validadas no backend. | Alta |
| **Timeline na ocorrência** | Na tela de detalhe da ocorrência, exibir linha do tempo: criação, mudanças de status, atribuição de motorista, observações. | Alta |
| **Ação rápida de status** | Na listagem ou no card, botão para alterar status (ex.: “Iniciar atendimento”) sem abrir o formulário completo. | Média |

### 2.4 Motorista e atendimento

| Item | Descrição | Prioridade |
|------|-----------|------------|
| **Área do motorista** | Painel específico para motoristas (acesso diferente do admin); ver apenas ocorrências vinculadas a ele ou ao seu órgão. | **Alta** |
| **Aceitação de ocorrência** | Motorista pode aceitar ou recusar ocorrência atribuída; status muda para "Aguardando aceitação" → "Aceita" ou "Recusada". | **Alta** |
| **Acompanhamento pelo painel** | Admin acompanha em tempo real quando motorista aceita, inicia deslocamento, chega ao local e finaliza atendimento. | **Alta** |
| **Atribuição em massa** | Na listagem, selecionar várias ocorrências e atribuir o mesmo motorista. | Média |
| **Status do motorista** | Sincronizar status do motorista (disponível, em deslocamento, em atendimento) com a ocorrência em atendimento. | Média |
| **Notificação ao motorista** | Ao atribuir ocorrência, notificar o motorista (e-mail, push ou integração). | Média |

### 2.5 Notificações e alertas

| Item | Descrição | Prioridade |
|------|-----------|------------|
| **Notificação no navegador** | Quando uma ocorrência for criada/atualizada (ex.: nova atribuída ao usuário), usar Notifications API (com permissão). | Média |
| **Som / badge** | Opção de som ou indicador visual ao receber atualização em tempo real. | Baixa |
| **Resumo diário** | E-mail ou relatório com ocorrências do dia por status/tipo (opcional). | Baixa |

### 2.6 Dashboard orientado à operação

| Item | Descrição | Prioridade |
|------|-----------|------------|
| **Cards por status** | Contadores ou cards (Abertas, Em atendimento, Finalizadas hoje) com link para lista filtrada. | Alta |
| **Lista + mapa lado a lado** | Layout que permita ver lista e mapa simultaneamente; clique na lista centraliza/abre marcador no mapa. | Média |
| **Atualização “agora”** | Indicador “Atualizado agora” ou timestamp da última atualização (já existe); manter com WebSocket. | — |

---

## Eixo 3 – Base técnica e suporte

### 3.1 Backend

| Item | Descrição | Prioridade |
|------|-----------|------------|
| **Escopo tenant em ocorrências** | Garantir que `occurrencesRecent` e todas as consultas de ocorrências filtrem por tenant (ou client) quando aplicável. | Alta |
| **API de ocorrências** | Endpoints REST para listar, filtrar e atualizar status (para uso do admin e de integrações). | Média |
| **Filas** | Enfileirar envio de e-mail/notificação e processamento pesado (relatórios, webhooks). | Média |

### 3.2 Frontend

| Item | Descrição | Prioridade |
|------|-----------|------------|
| **Layout motorista** | Layout específico para motoristas (simplificado, focado em ocorrências); menu reduzido (ocorrências, perfil, sair). | **Alta** |
| **PWA / offline** | Possibilidade de abrir o painel como PWA e enfileirar criação de ocorrência offline para enviar quando online. | Baixa |
| **App motorista** | Aplicativo (ou PWA) para o motorista ver ocorrências atribuídas e atualizar status/posição (fase posterior). | — |

### 3.3 Segurança e auditoria

| Item | Descrição | Prioridade |
|------|-----------|------------|
| **Permissões** | Revisar permissões (criar, editar, ver todas, ver só suas) por perfil. | Alta |
| **Rate limit** | Limitar requisições na API pública de criação de ocorrências. | Média |
| **Log de acesso** | Registrar acessos sensíveis (opcional, pode usar Telescope em dev). | Baixa |

---

## Ordem de implementação sugerida

| Fase | Conteúdo | Objetivo |
|------|----------|----------|
| **Fase 1** | Prioridade/urgência na ocorrência; número único (OC-YYYY-NNNNN); escopo por tenant nas ocorrências e no dashboard. | Base para monitoramento e comunicação. |
| **Fase 1.5** | **Área do motorista:** autenticação, layout específico, listagem filtrada (motorista/órgão), aceitação de ocorrência, acompanhamento pelo painel admin. | Acesso diferenciado e fluxo de aceitação. |
| **Fase 2** | WebSockets/Broadcasting (Laravel Echo + Pusher ou Soketi); evento `OccurrenceUpdated`; dashboard e mapa atualizando em tempo real. | Monitoramento verdadeiramente em tempo real. |
| **Fase 3** | Timeline na tela de detalhe da ocorrência; fluxo de status definido; ação rápida de status na listagem. | Clareza do atendimento. |
| **Fase 4** | Filtros no mapa e na lista (status, tipo, data); cards por status no dashboard; lista + mapa integrados. | Operação no dia a dia. |
| **Fase 5** | API pública de criação de ocorrência; formulário público no site; validação de endereço e histórico de alterações. | Canais de entrada e rastreabilidade. |
| **Fase 6** | Notificações (navegador, e-mail ao motorista); clustering no mapa; melhorias de acessibilidade e UX. | Refino e usabilidade. |

---

## Resumo de prioridades

- **Alta:** número único, prioridade, escopo tenant, **área do motorista, aceitação de ocorrência, acompanhamento pelo painel**, WebSockets, filtros no mapa, timeline, fluxo de status, cards por status, API pública, formulário público, permissões.
- **Média:** rascunho, webhook, fotos no create, histórico de alterações, clustering, atribuição em massa, status do motorista, notificações ao motorista, notificação no navegador, API REST, filas.
- **Baixa:** atalho “Nova ocorrência”, campos condicionais, som/badge, resumo diário, PWA, log de acesso.

Este plano pode ser usado como backlog: cada item pode virar tarefa em quadro (Kanban/Issues) e as fases servem como releases ou marcos de entrega.
