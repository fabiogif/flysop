# Módulos — mapa atual e checklist para módulo novo

## Mapa atual (evidência)

| Domínio | Controllers | Service | Repository/Interface | Resource (API) |
|---------|-------------|---------|----------------------|-----------------|
| Occurrence | `Admin\OccurrencesController`, `Api\OccurrenceApiController`, `Driver\DriverOccurrenceController` | `OccurrenceService` | `OccurrenceRepository` / `OccurrenceRepositoryInterface` | `OccurrenceResource` |
| Tenant | `Admin\TenantController`, `Api\TenantApiController` | `TenantService` | `TenantRepository` / `TenantRepositoryInterface` | `TenantResource` |
| Client | `Api\Auth\RegisterController` | `ClientService` | `ClientRepository` / `ClientRepositoryInterface` | `ClientResource` |
| ClientConsumer | `Api\Auth\RegisterController` | `ClientConsumerService` | `ClientConsumerRepository` / `ClientConsumerRepositoryInterface` | `ClientConsumerResource` |
| TypeOccurrence | `Admin\TypeOccurrenceController`, `Api\TypeOccurrenceApiController` | `TypeOccurrenceService` | `TypeOccurrenceRepository` / `TypeOccurrenceRepositoryInterface` | `TypeOccurrenceResource` |
| Mail | `Admin\MailController` | `MailService` | `MailRepository` / `MailRespositoryInterface` (typo existente) | — |
| Dashboard | `Admin\DashboardController` | `DashboardService` (+ `DashboardServiceInterface`) | — | — |
| Driver (posição) | `Driver\DriverPositionController` | `DriverPositionService` | — | — |
| Dispatch (despacho) | `Admin\DispatchController` | `DispatchService` | `DriverRepository` / `DriverRepositoryInterface` | — |
| Category, Product, Table, User, Role, Profile, Permission, Issuing, StatusOccurrence, Driver (CRUD), Priority (CRUD), Department (CRUD), Team (CRUD) | `Admin\*Controller` | — | — (Model injetado direto) | — |

Fase 1 (fundação de dados de ocorrência): `occurrences` ganhou `protocol` (gerado em `OccurrenceService::createOccurrenceWithDefaults`, formato `OC-{ano}-{sequencial}`, sem lock dedicado — simplificação aceitável para o volume atual), `priority_id`/`due_at` (SLA: prioridade tem precedência sobre `type_occurrences.sla_hours`), `neighborhood`/`city`/`state`/`zip` (preenchidos pelo mapa, mesmo padrão de `address`). `type_occurrences` ganhou `sla_hours` e `parent_id` (hierarquia tipo/subtipo, sem ciclos profundos validados). Nova tabela `occurrence_status_history` (append-only) — todo ponto que muda `status_occurrences_id` passa por `OccurrenceService::recordStatusChange()` (privado); não escrever nessa coluna fora do Service (ver `Driver\DriverOccurrenceController::updateStatus`, que usa `updateStatusByAdmin`/`updateStatusByDriver`).

Fase 2 (RBAC expandido + organização): roles novas `Atendente` e `Supervisor` (`database/seeders/RoleSeeder.php`) — "Motorista" continua sendo o "Agente de Campo" da spec original, sem role separada. Novas tabelas `departments`/`teams` (CRUD simples, ver `Admin\DepartmentController`/`Admin\TeamController`); `drivers` ganhou `team_id` nullable. `App\Policies\OccurrencePolicy` registrada em `AuthServiceProvider` — autorização por registro (não só por módulo/menu) em `Admin\OccurrencesController` via `$this->authorize(...)`. `User::isAdmin()` (não confundir com `ManagerTenant::isAdmin()`, código morto) passou a checar a role "Administrador" com fallback em `config('acl.admin')` — ver `docs/specs/security.md`.

Fase 3 (fluxo de status + auditoria + evidências + notificações): `status_occurrences` ganhou `sort_order`/`is_terminal` e o seeder (`StatusOccurrenceDriverSeeder`) passou a criar o fluxo completo (Recebida→...→Finalizada/Cancelada/Duplicada/Reaberta). `OccurrenceService::guardStatusTransition()` bloqueia sair de um status terminal exceto para "Reaberta", e só com a ability `reopen` do `OccurrencePolicy` (Supervisor/Administrador). Auditoria de campos via `spatie/laravel-activitylog` (`LogsActivity` em `Occurrences::getActivitylogOptions()`, `logOnlyDirty()`) — não loga `status_occurrences_id` (isso já tem trilha própria em `occurrence_status_history`, não duplicar). `occurrences_imagens` ganhou `uploaded_by_user_id`/`phase`('antes'/'depois')/`latitude`/`longitude`/`captured_at` — e a migration corrigiu a **coluna `url` que nunca existiu na tabela** apesar do model e do `OccurrenceService` sempre terem gravado nela (upload de anexo estava quebrado; achado ao testar esta fase). Ponto único de criação de evidência: `OccurrenceService::storeEvidence()`. Notificações internas via sistema nativo do Laravel (tabela `notifications`, canal `database`, classes em `app/Notifications/`: `OccurrenceAssignedNotification`, `OccurrenceStatusChangedNotification`, `SlaAtRiskNotification`) — todas `ShouldQueue` (rodam no worker da Fase 0). `App\Console\Commands\CheckOccurrenceSlaCommand` (`occurrences:check-sla`) agendado em `app/Console/Kernel.php`; **o agendador do Laravel não rodava em nenhum ambiente** antes desta fase (nem o `driver-positions:clean` pré-existente) — corrigido com o serviço `scheduler` em `docker-compose.dev.yml` (loop de `schedule:run` a cada 1 min); produção (Fly.io) ainda não tem isso, mesma pendência já registrada para fila/broadcast na Fase 0. Também corrigido `config/filesystems.php`: lia a variável antiga `FILESYSTEM_DRIVER` (Laravel 7) em vez de `FILESYSTEM_DISK` (Laravel 8+, é o que o `.env` define), então todo upload ia sempre para o fallback `s3` sem credenciais — achado ao testar upload de evidência.

Fase 4 (dashboard + mapa + busca): `DashboardService::getStats()` ganhou 5 KPIs operacionais (abertas/em atendimento/finalizadas hoje/SLA estourado/SLA em risco) e `getChartsData()` (ocorrências por dia/status/prioridade, para os 3 gráficos Chart.js em `resources/views/admin/pages/home/index.blade.php`). Chart.js é **bundlado via npm/Mix** (não CDN como o Google Maps) — `resources/js/admin/dashboardCharts.js`, importado em `app.js`, lê `window.dashboardChartsData` (setado inline no blade via `@json`). **Atenção**: chart.js v4 não builda neste projeto (usa `static` class fields, o Babel/webpack antigo do Mix não transpila `node_modules`) — fixado em `^3.9`, não fazer upgrade para v4 sem antes ajustar o babel-loader do `webpack.mix.js`. O mapa do dashboard ganhou clustering (`@googlemaps/markerclusterer`, carregado via CDN como o próprio Google Maps) e mapa de calor (`google.maps.visualization.HeatmapLayer`, precisa de `&libraries=visualization` na URL do script) atrás de um toggle; `occurrencesRecent()`/nova `occurrencesHeatmap()` aceitam filtros (`status_occurrences_id`, `type_occurrences_id`, `priority_id`, `driver_id`, `date_from`, `date_to`). Polling de 60s foi mantido como fallback, mas agora existe push: `App\Events\OccurrenceUpdated` (`ShouldBroadcast`, canal privado `occurrences-dashboard`, mesma regra de acesso do módulo occurrences) disparado em `OccurrenceService::createOccurrenceWithDefaults()`/`recordStatusChange()`; o dashboard escuta via Echo e refaz o fetch na hora. Busca global: coluna gerada `occurrences.search_vector` (tsvector, `GENERATED ALWAYS AS ... STORED`, GIN index — mantida automaticamente pelo Postgres, não precisa de código PHP nem trigger) via `Occurrences::scopeFullTextSearch()`, exposta em `Admin\SearchController`/`/admin/search`. A busca da listagem de ocorrências (`Occurrences::Occurrence()`, usada por `occurrences.search`) foi reforçada para combinar ILIKE (substring parcial) com o full-text (stemming) e passou a buscar por `protocol` também — full-text sozinho não pega substring parcial, por isso os dois continuam lado a lado.

Fase 5 (despacho inteligente + GPS robusto): `teams` ganhou `type_occurrences_id` nullable (especialidade — nulo = atende qualquer tipo). `DriverRepository::nearestAvailable()` faz haversine em SQL Postgres puro (sem PostGIS/earthdistance — decisão da Fase 5 do plano, volume atual não justifica) com `JOIN LATERAL` para pegar só a última posição de cada motorista; **atenção a um detalhe de PDO pgsql**: parâmetros usados só em `? IS NULL` (sem outro uso que ancore o tipo) precisam de cast explícito (`?::bigint`), senão dá erro `Indeterminate datatype` — aconteceu e foi corrigido ao testar esta fase. `DispatchService::suggestDrivers()` retorna só um ranking (nunca atribui sozinho) — humano confirma via `Admin\DispatchController@suggest` (`GET /admin/occurrences/{id}/suggest-drivers`) e o botão "Sugerir mais próximo" no formulário de ocorrência preenche o `driver_id` manualmente. `OccurrenceService::syncDriverStatus()` (chamado de dentro de `recordStatusChange()`) reflete o status da ocorrência no status do motorista (Em deslocamento/Em atendimento/Equipe no local → status correspondente; Finalizada/Cancelada/Duplicada/Recusada → disponível) — side effect explícito no Service, não em Model. Os outros dois itens da Fase 5 do plano **já estavam resolvidos por fases anteriores**: retenção de `driver_positions` via `driver-positions:clean` (existia desde antes, mas só passou a rodar de fato depois do `scheduler` da Fase 3) e ponto único de gravação de posição via `DriverPositionService` (Fase 0).

## Checklist — módulo novo com regra de negócio (padrão "camadas completas")

Espelhar Occurrence ou Tenant:

1. Rota em `routes/web.php` (admin) e/ou `routes/api.php`, com middleware de auth adequado (`auth` web / `auth:sanctum` api)
2. Controller fino (Admin/Api conforme o caso)
3. `FormRequest StoreUpdate{Entity}`
4. `{Entity}Service` em `app/Services/`
5. `{Entity}RepositoryInterface` em `Repositories/Contracts/` + `{Entity}Repository` + bind em `RepositoryServiceProvider`
6. `{Entity}Resource` se expor API
7. View Blade em `resources/views/admin/pages/{entity}/` se tiver painel
8. Teste Feature em `tests/Feature/` (ver `testing.md`)

## Checklist — módulo novo simples (CRUD sem regra de negócio)

Espelhar Product ou Driver:

1. Rota `Route::resource` em `routes/web.php` (grupo `admin`)
2. Controller injeta o Model direto, escopo de tenant explícito quando o model é multi-tenant
3. `FormRequest StoreUpdate{Entity}`
4. Views `index`/`create`/`edit`/`show` em `resources/views/admin/pages/{entity}/`
5. Rota `search` (`Route::any('{entity}/search', ...)`) se a listagem precisa de busca — padrão já usado em quase todo domínio admin

## Não misturar

- Não crie Service/Repository para um CRUD que não tem regra de negócio "porque outros domínios têm" — vai divergir do restante do admin sem ganho real
- Não adicione lógica de negócio em Model — a base de código não usa esse padrão (nem nos domínios "camadas completas")
- Duplicidade conhecida a **não repetir**: `StoreUpdateOccurrences` existe em dois namespaces (`App\Http\Requests` e `App\Http\Requests\Api`). Ao criar módulo novo, garanta um único ponto de verdade para a regra de negócio — a gravação de posição do motorista já foi corrigida (Fase 0): hoje só existe `Driver\DriverPositionController`, delegando para `App\Services\DriverPositionService`; o endpoint público duplicado (`Api\Driver\DriverLocationController`) foi removido por não ter autenticação nem cliente externo dependente.
