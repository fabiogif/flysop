# Performance — práticas atuais

## Backend

- Paginação em listagens admin (`paginate(15)` / `paginate()` default) — manter em toda listagem nova
- Eager loading pontual (`->load('occurrences')` em `DriverController::show`, `->with([...])` em `OccurrenceService::getOccurrencesForDriver`) — aplicar em qualquer listagem/detalhe que exiba relacionamento, para evitar N+1
- Sem `CacheService`/cache de aplicação hoje — não introduzir camada de cache sem necessidade medida (dataset pequeno por tenant)
- Broadcasting (Pusher) usado apenas para posição do motorista — evento leve, não usar broadcasting para dados que já são cobertos pelo polling de 15s do dashboard

## Frontend

- Polling do dashboard (`fetch()` a cada 15s) é a estratégia aceita para "tempo real" hoje — não trocar por WebSocket/Echo sem necessidade real validada com o time (ver `REFATORACAO_PLANO.md`, item 4, marcado como opcional)
- Site público migrado para ES6 sem jQuery — não reintroduzir jQuery/plugins pesados (owl carousel, particles, lightbox foram removidos de propósito, ver `REFATORACAO_PLANO.md`, item 1)
- Vue 2 usado só em componentes pontuais — não carregar o bundle Vue em páginas que não precisam dele

## Banco

- Consultas escopadas por `tenant_id` (via `TenantScope` ou filtro explícito) — sempre confirmar que uma query nova em domínio multi-tenant está de fato filtrada, especialmente se bypassar o Model (`DB::` direto)
- Sem índices customizados documentados além dos gerados pelas migrations padrão — ao adicionar filtro/order novo em listagem grande, considere se a coluna precisa de índice

## Checklist

- [ ] Listagem paginada
- [ ] `with()`/`load()` nas relações exibidas na resposta/view
- [ ] Query nova filtrada por `tenant_id` (direta ou via scope)
- [ ] Sem N+1 óbvio (loop chamando relação sem eager load)
- [ ] Upload de arquivo não bloqueia a resposta desnecessariamente (streaming/`store()` já é assíncrono o suficiente para o volume atual)
