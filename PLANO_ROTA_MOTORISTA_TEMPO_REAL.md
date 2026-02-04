# Plano de Implementação – Acompanhamento da Rota do Motorista em Tempo Real

**Objetivo:** Permitir que o painel admin acompanhe em tempo real a rota do motorista (posição GPS) durante o deslocamento para uma ocorrência, exibindo trajetória no mapa e posição atual.

---

## 1. Visão geral

### 1.1 Cenário de uso

1. Admin atribui ocorrência ao motorista; motorista aceita e inicia deslocamento.
2. Motorista abre o painel do motorista (web ou app) e inicia o “compartilhamento de rota” para aquela ocorrência.
3. O dispositivo do motorista envia periodicamente a posição (lat/lng) para o backend.
4. O backend armazena os pontos e/ou envia em tempo real via WebSocket para o painel.
5. No painel admin, na tela da ocorrência ou no mapa do dashboard, o admin vê:
   - Posição atual do motorista (marcador móvel).
   - Rota percorrida (polyline no mapa).
   - Opcional: linha até o endereço da ocorrência.

### 1.2 Componentes principais

| Componente | Descrição |
|------------|-----------|
| **Captura de posição** | No lado do motorista: Geolocation API (navegador) ou sensor GPS (app). |
| **Envio para o backend** | API REST (POST posição) e/ou WebSocket (canal do motorista). |
| **Armazenamento** | Tabela de posições (driver_id, occurrence_id, lat, lng, created_at). |
| **Transmissão em tempo real** | Laravel Broadcasting (Pusher/Soketi) ou polling curto (ex.: 5–10 s). |
| **Visualização no admin** | Mapa (Google Maps) com marcador do motorista, polyline da rota e marcador da ocorrência. |

---

## 2. Modelo de dados

### 2.1 Nova tabela: `driver_positions`

Armazenar histórico de posições para rota e auditoria.

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| `id` | bigint PK | |
| `driver_id` | bigint FK | Motorista |
| `occurrence_id` | bigint FK nullable | Ocorrência em deslocamento (quando aplicável) |
| `latitude` | decimal(10,8) | |
| `longitude` | decimal(11,8) | |
| `accuracy` | float nullable | Precisão em metros (Geolocation API) |
| `created_at` | timestamp | Momento da captura |

- Índices: `(driver_id, created_at)`, `(occurrence_id, created_at)`.
- Política de retenção: manter apenas posições das últimas 24–48 h para a rota em tempo real; opcional job para limpar dados antigos.

### 2.2 Model `DriverPosition`

```php
// app/Models/DriverPosition.php
- belongsTo(Driver::class)
- belongsTo(Occurrences::class, 'occurrence_id') // nullable
- scope para última posição por driver, por ocorrência, por período
```

### 2.3 Uso opcional em `drivers` ou em sessão

- **Opção A:** Não alterar tabela `drivers`; última posição sempre obtida da última linha de `driver_positions` (por driver e, se quiser, por occurrence_id).
- **Opção B:** Adicionar em `drivers` os campos `last_latitude`, `last_longitude`, `last_position_at` para leitura rápida da “posição atual” sem consultar histórico.

Recomendação inicial: **Opção A** (só `driver_positions`); depois otimizar com **Opção B** se necessário.

---

## 3. Backend (Laravel)

### 3.1 API para o motorista enviar posição

| Método | Rota | Descrição | Autenticação |
|--------|------|-----------|--------------|
| POST | `/driver/position` ou `/api/driver/position` | Recebe `latitude`, `longitude`, `occurrence_id` (opcional), `accuracy` (opcional). Cria registro em `driver_positions`. Dispara evento para Broadcasting. | Auth + middleware motorista (ou token API para app) |

- Validação: lat/lng obrigatórios e numéricos; `occurrence_id` existe e pertence ao motorista (ou omitido para “rota livre”).
- Frequência: o cliente (web/app) envia a cada X segundos (ex.: 5–15 s); no backend pode-se limitar por rate limit (ex.: 1 req/3 s por motorista) para evitar spam.

### 3.2 API para o admin obter rota / posição

| Método | Rota | Descrição |
|--------|------|-----------|
| GET | `/admin/occurrences/{id}/driver-route` | Retorna posições da rota do motorista para a ocorrência (lista de {lat, lng, created_at}) e última posição. Usado para desenhar polyline e marcador. |
| GET | `/admin/drivers/{id}/last-position` | Retorna última posição do motorista (para mapa geral). |

- Escopo: apenas ocorrências/drivers do tenant do usuário logado.

### 3.3 Evento em tempo real (Broadcasting)

- **Evento:** `DriverPositionUpdated` (ou `DriverLocationUpdated`).
- **Payload:** `driver_id`, `occurrence_id`, `latitude`, `longitude`, `created_at`.
- **Canal:** privado por tenant (ex.: `tenant.{tenant_id}`) ou por ocorrência (ex.: `occurrence.{occurrence_id}`) para que apenas quem está vendo aquela ocorrência receba atualizações.
- **Listeners:** nenhum listener obrigatório no backend; o frontend (admin) que se inscreve no canal e atualiza o mapa.

Se Broadcasting não estiver disponível no primeiro momento, usar **polling** no admin (ex.: a cada 5–10 s) chamando `GET .../driver-route` ou `GET .../last-position`.

---

## 4. Lado do motorista (envio da posição)

### 4.1 Web (painel do motorista)

- Na tela de detalhe da ocorrência (ou em “Em deslocamento”), botão **“Compartilhar minha rota”**.
- Ao ativar:
  - Usar `navigator.geolocation.watchPosition()` para obter posição contínua.
  - A cada X segundos (ex.: 10 s), enviar `POST /driver/position` com `latitude`, `longitude`, `occurrence_id`.
- Ao desativar ou ao sair da tela, parar `watchPosition` e opcionalmente enviar uma última posição.
- Tratamento de erros: permissão negada, timeout, sem rede (enfileirar para enviar depois, se desejar).

### 4.2 App mobile (fase posterior)

- Uso do GPS do dispositivo em background (com permissões e políticas do SO).
- Mesma API `POST /driver/position`; frequência configurável (ex.: 5–15 s).
- Opcional: enviar apenas quando houver deslocamento significativo (ex.: > 10 m) para economizar bateria e dados.

---

## 5. Painel admin (visualização)

### 5.1 Tela de detalhe da ocorrência

- **Mapa** com:
  - Marcador do endereço da ocorrência (já pode existir).
  - Marcador da **última posição do motorista** (se houver e se a ocorrência estiver com motorista em deslocamento/em atendimento).
  - **Polyline** ligando os pontos da rota (ordenados por `created_at`) desde o início do compartilhamento até a última posição.
- Atualização:
  - **Com WebSocket:** ao receber evento `DriverPositionUpdated` para essa ocorrência, atualizar marcador e adicionar ponto à polyline.
  - **Com polling:** a cada 5–10 s, chamar `GET .../driver-route` e redesenhar polyline + marcador.

### 5.2 Dashboard / mapa geral

- No mapa de ocorrências, opção de exibir **marcadores dos motoristas** (última posição) quando em deslocamento.
- Cores ou ícones diferentes: ocorrência, motorista em deslocamento, motorista no local.
- Clique no marcador do motorista: tooltip ou sidebar com nome do motorista e ocorrência vinculada.

### 5.3 Performance e UX

- Limitar quantidade de pontos da polyline (ex.: últimos 100 pontos ou últimos 30 min) para não sobrecarregar o mapa.
- Animação suave do marcador do motorista ao atualizar posição (opcional).
- Indicador “Ao vivo” quando estiver recebendo posições em tempo real.

---

## 6. Segurança e privacidade

| Aspecto | Medida |
|---------|--------|
| **Autenticação** | Apenas motorista autenticado (ou token válido) pode enviar posição; associar sempre a `driver_id` do usuário. |
| **Autorização** | Admin só vê rota/posição de motoristas e ocorrências do seu tenant. |
| **Dados** | Não expor posições a terceiros; considerar política de retenção (ex.: apagar posições após 24–48 h). |
| **Rate limit** | Limitar requisições de posição por motorista (ex.: 1 a cada 3–5 s) para evitar abuso. |
| **HTTPS** | Garantir HTTPS em produção para não expor localização em trânsito. |

---

## 7. Ordem de implementação sugerida

| Fase | Entregas | Resultado |
|------|----------|-----------|
| **Fase 1 – Dados e API de envio** | Migração `driver_positions`; model `DriverPosition`; POST `/driver/position` (auth + middleware motorista); validação e rate limit. | Motorista consegue enviar posição para o backend. |
| **Fase 2 – API de leitura** | GET `/admin/occurrences/{id}/driver-route`; GET (opcional) `/admin/drivers/{id}/last-position`; escopo por tenant. | Admin consegue obter rota e última posição via API. |
| **Fase 3 – Envio no painel do motorista** | Na tela da ocorrência (driver), botão “Compartilhar minha rota”; `watchPosition` + POST a cada 10 s; parar ao desativar. | Rota começa a ser registrada quando o motorista ativa o compartilhamento. |
| **Fase 4 – Mapa na ocorrência (admin)** | Na tela de detalhe da ocorrência (admin), mapa com marcador da ocorrência + polyline da rota + marcador do motorista; atualização por polling (ex.: 10 s). | Admin vê rota e posição atual do motorista na tela da ocorrência. |
| **Fase 5 – Tempo real (opcional)** ✅ | Evento `DriverPositionUpdated` + canal privado; frontend admin inscrito no canal; atualizar mapa via WebSocket. | Atualização sem polling, experiência “ao vivo”. |
| **Fase 6 – Dashboard e refinamentos** | Marcadores dos motoristas no mapa do dashboard; limitar pontos da polyline; indicador “Ao vivo”; política de retenção (job para limpar posições antigas). | Experiência completa e sustentável. |

---

## 8. Tecnologias e dependências

| Item | Sugestão |
|------|----------|
| **Mapa** | Google Maps JavaScript API (já em uso no projeto). |
| **Geolocalização (motorista)** | `navigator.geolocation.watchPosition` (navegador). |
| **Broadcasting** | Laravel Echo + Pusher ou Soketi (se já houver no projeto). |
| **Polling** | `setInterval` + `fetch` no frontend; intervalo 5–10 s. |
| **Armazenamento** | PostgreSQL (já em uso); tabela `driver_positions`. |

---

## 9. Riscos e mitigações

| Risco | Mitigação |
|-------|------------|
| Bateria e dados no celular do motorista | Frequência de envio configurável (ex.: 10–15 s); opcionalmente enviar só quando mudar de posição. |
| Muitos pontos na base | Limite de pontos retornados na API; job para deletar posições antigas (ex.: > 24 h). |
| Motorista esquece de desligar | Mostrar indicador claro “Compartilhando rota”; desligar ao sair da tela ou após X tempo sem movimento (opcional). |
| Falta de Broadcasting | Usar apenas polling no admin; implementar WebSocket depois. |

---

## 10. Critérios de aceite (resumo)

- [ ] Motorista pode ativar “Compartilhar minha rota” na ocorrência em deslocamento.
- [ ] Backend recebe e grava posições (driver_id, occurrence_id, lat, lng, created_at).
- [ ] Admin vê na tela da ocorrência o mapa com endereço da ocorrência, rota percorrida (polyline) e posição atual do motorista.
- [ ] Atualização da posição no mapa em no máximo 10–15 s (polling ou WebSocket).
- [ ] Apenas motorista autenticado envia posição; apenas admin do tenant vê rota/posição.
- [ ] Dados de posição com política de retenção definida (ex.: 24–48 h).

---

Este plano pode ser usado como backlog: cada fase vira um conjunto de tarefas e a implementação pode ser feita incrementalmente (polling primeiro, WebSocket depois).
