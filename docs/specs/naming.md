# Naming conventions — atuais

## Backend

| Tipo | Convenção | Exemplo |
|------|-----------|---------|
| Controller admin | `*Controller` | `ProductController`, `OccurrencesController` |
| Controller API (domínio CRUD) | `*ApiController` | `OccurrenceApiController`, `TenantApiController`, `TypeOccurrenceApiController` |
| Controller API (subpasta já indica) | `*Controller` dentro de `Api/Auth/` | `AuthClientController` |
| Service | `*Service` | `OccurrenceService` |
| Service com interface (raro) | `*Service` + `*ServiceInterface` | `DashboardService` / `DashboardServiceInterface` |
| Repository | `*Repository` | `OccurrenceRepository` |
| Interface repo | `*RepositoryInterface` em `Contracts/` | `OccurrenceRepositoryInterface` |
| Form Request | `StoreUpdate{Entity}` (create e update na mesma classe) | `StoreUpdateOccurrences`, `StoreUpdateDriver` |
| Resource | `*Resource` | `OccurrenceResource` |
| Middleware | PascalCase descritivo | `EnsureUserIsDriver` |
| Observer | `*Observer` | `TenantObserver` |
| Event | Verbo no particípio | `DriverPositionUpdated`, `TenantCreated` |
| Migration | `YYYY_MM_DD_HHMMSS_snake_description.php` (padrão Laravel) | — |
| Model | Singular (padrão) — exceção legada `Occurrences`/`OccurrencesImagens` (plural, não replicar) | `Client`, `Driver`, `Tenant` |
| Test | `*Test` | `ExampleTest` |

## Rotas

- Padrão: kebab/lowercase plural em `Route::resource` (`products`, `categories`, `tenants`, `drivers`)
- Exceção existente: `typeOccurrences`, `statusOccurrences` em camelCase — inconsistente com o resto; **não copiar** esse estilo em recurso novo, usar kebab/lowercase (`type-occurrences` só se for recurso novo sem vínculo direto; se for extensão do recurso existente, manter `typeOccurrences` para não quebrar rotas nomeadas)
- Rotas de busca: `Route::any('{recurso}/search', ...)` nomeada `{recurso}.search` — padrão repetido em quase todo domínio admin, seguir para consistência

## Banco

- Tabelas: `snake_case` (majoritariamente singular no schema deste projeto — ex.: `driver`/`drivers`, confirmar no migration do domínio antes de assumir)
- Colunas: `snake_case`, `tenant_id` nos models multi-tenant
- Soft delete: usar `deleted_at` apenas nos models que já usam `SoftDeletes` — não adicionar a models que não têm sem necessidade

## Frontend

| Tipo | Convenção | Exemplo |
|------|-----------|---------|
| View Blade | dot notation por pasta | `admin.pages.drivers.index` |
| Componente Vue | PascalCase, arquivo `.vue` | `DriverTracker.vue` |
| Módulo JS (site/admin) | camelCase | `occurrenceMap.js`, `headerScroll.js` |
| Flash message key | `messageSuccess{Entity}` ou `messageSuccess` (varia por domínio — seguir o vizinho) | `messageSuccessDriver` |

## Idioma

Código e identifiers em inglês; mensagens de validação, flash e UI para o usuário final em português (padrão observado em `StoreUpdateOccurrences`, `StoreUpdateDriver`, mensagens de redirect).
