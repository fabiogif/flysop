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
| Category, Product, Table, User, Role, Profile, Permission, Issuing, StatusOccurrence, Driver (CRUD), Priority (CRUD) | `Admin\*Controller` | — | — (Model injetado direto) | — |

Fase 1 (fundação de dados de ocorrência): `occurrences` ganhou `protocol` (gerado em `OccurrenceService::createOccurrenceWithDefaults`, formato `OC-{ano}-{sequencial}`, sem lock dedicado — simplificação aceitável para o volume atual), `priority_id`/`due_at` (SLA: prioridade tem precedência sobre `type_occurrences.sla_hours`), `neighborhood`/`city`/`state`/`zip` (preenchidos pelo mapa, mesmo padrão de `address`). `type_occurrences` ganhou `sla_hours` e `parent_id` (hierarquia tipo/subtipo, sem ciclos profundos validados). Nova tabela `occurrence_status_history` (append-only) — todo ponto que muda `status_occurrences_id` passa por `OccurrenceService::recordStatusChange()` (privado); não escrever nessa coluna fora do Service (ver `Driver\DriverOccurrenceController::updateStatus`, que usa `updateStatusByAdmin`/`updateStatusByDriver`).

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
