# Guia de Funcionalidades — CIOP - Central Inteligente de Ocorrências Públicas

**Versão:** 1.0  
**Data:** 10/08/2026  
**Público:** banca examinadora, gestores e equipe de produto  
**Escopo:** catálogo funcional completo da Central Inteligente de Ocorrências Públicas

---

## 1. Sumário

1. [Visão geral das funcionalidades](#2-visão-geral-das-funcionalidades)
2. [Acessos, perfis e autenticação](#3-acessos-perfis-e-autenticação)
3. [Ocorrências](#4-ocorrências)
4. [Status, timeline e evidências](#5-status-timeline-e-evidências)
5. [Despacho e equipes](#6-despacho-e-equipes)
6. [GPS e tempo real](#7-gps-e-tempo-real)
7. [Dashboard, mapa e busca](#8-dashboard-mapa-e-busca)
8. [Cadastros e administração](#9-cadastros-e-administração)
9. [API e canais externos](#10-api-e-canais-externos)
10. [Relatórios e duplicidade](#11-relatórios-e-duplicidade)
11. [Segurança, privacidade e LGPD](#12-segurança-privacidade-e-lgpd)
12. [Site público](#13-site-público)
13. [Matriz completa de funcionalidades](#14-matriz-completa-de-funcionalidades)

---

## 2. Visão geral das funcionalidades

O **CIOP - Central Inteligente de Ocorrências Públicas** reúne, em uma única plataforma, a operação completa de ocorrências públicas: do registro ao fechamento, com protocolo, prioridade, SLA, mapa, despacho inteligente, acompanhamento em tempo real, evidências, notificações, relatórios e proteção de dados.

**Capacidades principais**

- Protocolo e SLA
- Perfis e permissões
- Painel do agente
- GPS ao vivo
- Mapa operacional
- Despacho inteligente
- Dashboard e KPIs
- Busca global
- Relatórios PDF/Excel
- LGPD

**Fluxo ponta a ponta**

> Abertura → Triagem → Despacho → Aceite → Deslocamento (GPS) → Atendimento → Evidências → Conclusão → Relatório

---

## 3. Acessos, perfis e autenticação

### Login e sessão web

Acesso seguro ao painel administrativo e à área do agente, com login, logout, registro e recuperação de senha.

### Autenticação de clientes via API

Clientes externos autenticam-se por token, consultam dados autorizados e encerram a sessão de forma segura.

### Perfis de usuário

| Perfil | Responsabilidade |
|--------|------------------|
| Administrador | Configuração, usuários, prioridades, relatórios e LGPD |
| Supervisor / Secretaria | Triagem, despacho, SLA e mapa |
| Atendente / Operador | Registro e atualização de ocorrências |
| Agente de Campo / Motorista | Aceite, deslocamento, evidências e GPS |
| Cidadão | Abertura e acompanhamento de ocorrências |

### Controle de acesso por papéis e permissões

Menus e ações filtrados conforme o perfil. Policies garantem que cada usuário só execute o que compete à sua função. O administrador é reconhecido pelo papel Administrador.

### Autenticação em dois fatores (Administrador)

Camada adicional de proteção para contas administrativas, elevando a segurança do acesso privilegiado.

---

## 4. Ocorrências

### Cadastro completo de ocorrências

Criação, edição, consulta, exclusão e busca no painel. Inclusão de título, descrição, dados do solicitante, órgão, tipo, status, endereço e anexos.

### Protocolo único

Cada ocorrência recebe um protocolo gerado automaticamente na criação, usado para rastreio interno e comunicação com o cidadão.

### Prioridade e SLA

Prioridades configuráveis (nome, peso, cor e prazo padrão). O sistema calcula o prazo (`due_at`) a partir do tipo e/ou da prioridade e destaca casos em risco ou estourados.

### Tipos e subtipos

Tipos de ocorrência com hierarquia (tipo/subtipo), prazo próprio de SLA e vínculo às ocorrências.

### Endereço estruturado + mapa

Endereço livre com apoio de autocomplete, além de bairro, cidade, UF e CEP. Geolocalização no formulário para posicionar a ocorrência no mapa.

### Atribuição a agente

Vinculação da ocorrência a um agente/motorista, com reflexo imediato no painel de campo e nas notificações.

### Abertura por API / canal cidadão

Clientes e canais externos podem registrar e consultar ocorrências por API autenticada, com consentimento quando aplicável.

---

## 5. Status, timeline e evidências

### Fluxo de status expandido

Recebida → Triagem → Aguardando aceitação → Aceita/Recusada → Em deslocamento → Em atendimento → Concluída → Finalizada / Cancelada / Duplicada, com ordenação e marcação de status terminais.

### Validação de transições

O sistema só permite mudanças de status válidas (de-para), evitando saltos indevidos no fluxo operacional.

### Timeline / histórico de status

Cada mudança grava quem alterou, de qual status para qual, nota e horário. A tela de detalhe exibe a linha do tempo completa.

### Aceite e recusa pelo agente

No painel do motorista, o agente aceita ou recusa o chamado atribuído e atualiza o andamento do atendimento.

### Evidências antes e depois

Fotos e anexos classificados como “antes” ou “depois”, com data/hora, usuário e geolocalização quando disponível.

### Notificações internas

Avisos para ocorrência atribuída, mudança de status e SLA em risco, processados de forma assíncrona.

### Auditoria de alterações

Registro de quem editou quais campos, reforçando rastreabilidade e prestação de contas.

---

## 6. Despacho e equipes

### Departamentos e equipes

Organização da força de trabalho por departamento/equipe, com vínculo opcional do agente à especialidade.

### Despacho inteligente

Sugestão ranqueada dos agentes mais próximos e disponíveis (incluindo filtro por equipe). A confirmação final é humana.

### Sincronização agente ↔ ocorrência

O status do agente acompanha o andamento da ocorrência (disponível, em deslocamento, em atendimento), mantendo o mapa operacional coerente.

---

## 7. GPS e tempo real

### Envio de posição do agente

O painel do motorista envia latitude/longitude com autenticação e limite de frequência, vinculado à ocorrência em atendimento.

### Rastreamento ao vivo

A central acompanha a posição em tempo real via WebSocket. O componente de acompanhamento atualiza o mapa sem recarregar a página.

### Rota recente do agente

Consulta da trilha de posições da ocorrência, com limite de pontos para leitura eficiente.

### Retenção de posições

Política automática de limpeza de posições antigas, alinhada a desempenho e privacidade.

### Canal privado por ocorrência

Somente perfis autorizados ou o agente dono da ocorrência escutam o canal de atualização daquela ocorrência.

---

## 8. Dashboard, mapa e busca

### KPIs operacionais

Indicadores de ocorrências abertas, em atendimento, finalizadas no dia, SLA em risco e SLA estourado.

### Gráficos

Séries e distribuição visual para apoiar reuniões e acompanhamento de tendência.

### Mapa operacional

- Markers de ocorrências e agentes
- Clustering para regiões densas
- Mapa de calor da demanda
- Filtros por status, tipo, prioridade, data, agente/equipe

### Atualização em tempo quase real

Push via broadcasting no dashboard, com polling como contingência.

### Busca global

Pesquisa unificada por protocolo, endereço, descrição e campos correlatos (busca textual avançada no banco).

---

## 9. Cadastros e administração

### Cadastros de domínio

- Prioridades
- Tipos de ocorrência (com subtipos)
- Status de ocorrência
- Órgãos (issuings)
- Motoristas / agentes
- Departamentos e equipes
- Usuários, papéis e permissões
- Tenants / organizações

### Gestão de imagens e anexos

Upload e listagem de anexos da ocorrência, inclusive evidências classificadas por fase.

### Módulos auxiliares de apoio

Cadastros complementares do ecossistema (categorias, produtos, mesas e estruturas de perfil/permissão) disponíveis no painel administrativo.

---

## 10. API e canais externos

### API autenticada

- Emissão e revogação de token
- Consulta de ocorrências e detalhe
- Consulta por cliente
- Consulta de tenants e tipos
- Criação de ocorrência por canal autorizado

### Cadastro de client / consumidor

Registro de clientes e consumidores para integração com aplicativos e canais digitais.

---

## 11. Relatórios e duplicidade

### Exportação PDF

Ficha da ocorrência e relatórios de fechamento gerados sob demanda.

### Exportação Excel/CSV

Listagens e indicadores de tempo médio por etapa, com base no histórico de status.

### Geração assíncrona

Relatórios processados em fila: a solicitação não trava a tela; o usuário é notificado quando o arquivo está pronto.

### Detecção de possível duplicidade

Alerta quando há casos do mesmo tipo, próximos geograficamente e na mesma janela de tempo. A revisão e o tratamento permanecem humanos (sem merge automático).

---

## 12. Segurança, privacidade e LGPD

### Proteção de endpoints e canais

Autenticação web/API, autorização por perfil, throttle no envio de GPS e canal privado por ocorrência.

### Ponto único de gravação de posição

Toda posição do agente passa por um serviço central autenticado, sem endpoints públicos inseguros.

### Filas e processamento assíncrono

Notificações, relatórios e rotinas de retenção executam em worker supervisionado, sem bloquear a interação do usuário.

### LGPD

- Consentimento no formulário público
- Retenção e anonimização de trajetos e dados antigos
- Endpoint de esquecimento
- Log de acesso a dados sensíveis

### Isolamento por organização

Contexto multi-tenant preservado: cada organização opera seus próprios dados sem misturar informações.

---

## 13. Site público

### Landing institucional

Página pública de apresentação do SOP, com navegação para acesso ao sistema e informações do produto.

---

## 14. Matriz completa de funcionalidades

| # | Funcionalidade | Módulo | Benefício |
|---|----------------|--------|-----------|
| 1 | Login / logout / recuperação de senha | Acesso | Entrada segura ao sistema |
| 2 | Auth API por token | Acesso | Integração com apps/canais |
| 3 | Perfis (Admin, Supervisor, Atendente, Agente, Cidadão) | RBAC | Responsabilidades claras |
| 4 | Permissões e policies | RBAC | Controle fino de ações |
| 5 | 2FA para administrador | Segurança | Proteção reforçada |
| 6 | CRUD de ocorrências | Ocorrências | Operação do dia a dia |
| 7 | Protocolo único | Ocorrências | Rastreabilidade |
| 8 | Prioridades configuráveis | SLA | Urgência explícita |
| 9 | Cálculo de prazo (SLA) | SLA | Gestão de tempo |
| 10 | Tipos e subtipos | Cadastros | Classificação padronizada |
| 11 | Endereço estruturado + mapa | Ocorrências | Localização precisa |
| 12 | Anexos / evidências antes-depois | Evidências | Comprovação do atendimento |
| 13 | Fluxo de status expandido | Workflow | Padronização operacional |
| 14 | Validação de transições | Workflow | Integridade do processo |
| 15 | Timeline de status | Auditoria | Histórico completo |
| 16 | Aceite/recusa pelo agente | Campo | Compromisso claro |
| 17 | Painel do motorista/agente | Campo | Operação em campo |
| 18 | Departamentos e equipes | Organização | Especialização |
| 19 | Despacho inteligente (ranking) | Despacho | Melhor alocação |
| 20 | Confirmação humana do despacho | Despacho | Controle gerencial |
| 21 | Sync status agente ↔ ocorrência | Operação | Visão coerente |
| 22 | Envio de GPS autenticado | Realtime | Localização segura |
| 23 | Broadcast em tempo real | Realtime | Acompanhamento ao vivo |
| 24 | Canal privado por ocorrência | Segurança | Privacidade do rastreio |
| 25 | Consulta de rota / trilha | Realtime | Histórico de deslocamento |
| 26 | Retenção de posições | LGPD/Perf. | Limpeza automática |
| 27 | Dashboard com KPIs | Gestão | Decisão rápida |
| 28 | Gráficos operacionais | Gestão | Análise visual |
| 29 | Mapa com clustering | Mapa | Leitura em escala |
| 30 | Mapa de calor | Mapa | Hotspots de demanda |
| 31 | Filtros avançados | Mapa/Lista | Foco operacional |
| 32 | Busca global | Produtividade | Achado rápido |
| 33 | Push no dashboard (+ fallback) | Realtime | Atualização contínua |
| 34 | Notificações internas | Comunicação | Alertas oportunos |
| 35 | Auditoria de campos | Compliance | Rastreio de mudanças |
| 36 | Exportação PDF | Relatórios | Documentação formal |
| 37 | Exportação Excel/CSV | Relatórios | Análise tabular |
| 38 | Geração via fila | Infra | Performance da UI |
| 39 | Detecção de duplicidade | Qualidade | Menos retrabalho |
| 40 | Consentimento / esquecimento / anonimização | LGPD | Conformidade |
| 41 | Log de acesso sensível | LGPD | Governança |
| 42 | API de tenants/tipos/ocorrências | Integração | Ecossistema digital |
| 43 | Cadastro client/consumer | Integração | Onboarding de canais |
| 44 | Site institucional | Comunicação | Apresentação pública |
| 45 | Multi-tenant (isolamento) | Arquitetura | Separação por órgão |
| 46 | Observabilidade (Telescope/logs) | Operação TI | Diagnóstico |

**Total:** 46 funcionalidades de produto.

---

*Guia de Funcionalidades — CIOP - Central Inteligente de Ocorrências Públicas — Versão 1.0*
