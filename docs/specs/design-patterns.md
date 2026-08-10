# Design patterns — padrões em uso

Somente padrões **já presentes** no flySOP.

## Camadas (Layered / "service–repository") — domínios maduros

`Controller → FormRequest → Service → RepositoryInterface → Eloquent Model`

Presente em: Tenant, Occurrence, Client, ClientConsumer, TypeOccurrence, Mail, Dashboard. Use este fluxo para features novas com regra de negócio real.

## Active Record simples — CRUD sem regra de negócio

`Controller → FormRequest → Eloquent Model direto`

Presente em: Category, Product, Table, User, Role, Profile, Permission, Issuing, StatusOccurrence, Driver. É um padrão válido do projeto, não uma pendência — só migre para o padrão em camadas se o domínio ganhar regra de negócio real.

## Repository + Interface (DIP)

- Contract em `app/Repositories/Contracts/`
- Bind único em `app/Providers/RepositoryServiceProvider.php` (não há provider por domínio)
- Service depende da interface, não da classe concreta

## Global Scope (multi-tenant)

- `App\Tenant\Scopes\TenantScope` (implements `Scope`) filtra por `tenant_id`
- Aplicado via `App\Tenant\Traits\TenantTrait` no `boot()` do model
- `App\Tenant\ManagerTenant` centraliza a resolução do tenant atual — reutilize essa classe em vez de repetir `auth()->user()->tenant_id` espalhado (embora o código atual já misture os dois estilos)

## Observer

- `App\Observers\{Entity}Observer` (Category, Occurrence, Product, Tenant) — usar para side effects em eventos de model (ex.: `TenantObserver` ao criar tenant), registrados em `AppServiceProvider`/`EventServiceProvider`

## Event / Broadcasting

- `App\Events\DriverPositionUpdated`, `App\Events\TenantCreated` — eventos de domínio disparados explicitamente (`event(new ...)`), não via observer, quando o efeito é broadcasting/notificação externa

## API Resource (Transform)

- Usado nos domínios que expõem API: `ClientResource`, `ClientConsumerResource`, `OccurrenceResource`, `StatusOccurenceResource`, `TenantResource`, `TypeOccurrenceResource`
- Domínios sem API pública não precisam de Resource

## Form Request

- Validação declarativa Laravel, um único `StoreUpdate{Entity}` cobrindo create e update (não dividir em `Store*`/`Update*` salvo necessidade real de regras diferentes)

## Middleware de domínio

- `EnsureUserIsDriver` (`ensure.driver`) — restringe rotas ao perfil motorista
- `can:{permission}` no construtor do controller (ex.: `ProductController`) para ACL baseada em `laravel-permission`-like (`Permission`/`Role`/`Profile` models próprios do projeto, não um pacote de terceiros)

## Frontend patterns

| Pattern | Onde |
|---------|------|
| CRUD Blade + AdminLTE | telas admin |
| Componente Vue 2 isolado (props, sem store global) | `DriverTracker.vue` |
| Módulo ES6 sem framework | site público (`resources/js/site/`) |
| Polling simples via `fetch()` | dashboard admin (ocorrências recentes) |

## O que não é padrão do projeto

- CQRS, Event Sourcing
- Clean Architecture estrita / camadas obrigatórias em todo domínio
- `*ServiceInterface` como regra geral (só `DashboardServiceInterface` existe)
- React, Vue 3, state manager global (Vuex/Redux)
- Pacote de multi-tenancy de terceiro (a solução é caseira: `TenantScope` + `ManagerTenant`)
