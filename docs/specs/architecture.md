# Arquitetura do CIOP (Central Inteligente de Ocorrências Públicas)

Documento descritivo da arquitetura **atual**. Não introduz padrões novos além dos já em adoção no projeto (ver `REFATORACAO_PLANO.md`, Fase 3).

## Visão geral

Aplicação monolítica Laravel (não é monorepo):

| Camada | Path | Stack |
|--------|------|-------|
| Backend | raiz do repo (`app/`, `routes/`, `database/`) | Laravel 8, PHP 7.3/8.0, Sanctum, Telescope, AdminLTE (jeroennoten/laravel-adminlte) |
| Admin (painel) | `resources/views/admin/`, `resources/js/admin/`, `resources/js/components/*.vue` | Blade + AdminLTE (Bootstrap 4 + jQuery) + Vue 2 (componentes pontuais) |
| Site público | `resources/views/site/`, `resources/js/site/` | Blade + ES6 modules via Vite (sem jQuery) |
| Painel motorista | `resources/views/driver/` | Blade + JS vanilla (geolocalização) |
| API | `routes/api.php` | Sanctum (SPA/token) + Resources |

Realtime: Pusher (`pusher-js` + `pusher/pusher-php-server`) + Laravel Echo, evento `DriverPositionUpdated`, consumido pelo componente `DriverTracker.vue` e por polling simples (`admin/dashboard/occurrences-recent`, a cada 15s).

Deploy: Docker (`Dockerfile`, `Dockerfile.fly`, `Dockerfile.nginx`) + Fly.io (`fly.toml`) + Vercel (`vercel.json`) — múltiplos alvos de deploy coexistem, não remover nenhum sem confirmar com o time.

## Fluxo típico (padrão em consolidação)

```
Browser (admin/site/driver)
  → routes/web.php (middleware: web, auth, ensure.driver conforme grupo)
  → Controller
  → FormRequest (StoreUpdate{Entity})
  → Service (quando o domínio já tem)
  → RepositoryInterface → Repository → Model (TenantScope aplicado via TenantTrait)
  → View Blade (admin/site/driver) ou redirect com flash message

App externo / SPA
  → routes/api.php (middleware: auth:sanctum quando presente — nem todo endpoint tem)
  → Controller (Api/*)
  → Service (quando existir) → Repository → Model
  → API Resource (*Resource) ou response()->json(...)
```

## Camadas (backend)

### Controller (`app/Http/Controllers/`)

- `Admin/` — CRUD do painel AdminLTE (Blade), protegido por `auth` (e `can:{permission}` em alguns)
- `Api/` — endpoints JSON; `Api/Auth/`, `Api/Driver/` para sub-fluxos
- `Auth/` — controllers padrão `laravel/ui` (login/registro/senha)
- `Driver/` — painel do motorista (Blade), protegido por `auth` + `ensure.driver`
- `Site/` — página pública institucional

### Service (`app/Services/`)

Existe para os domínios: Client, ClientConsumer, Dashboard, Mail, Occurrence, Tenant, TypeOccurrence.
Nem todo domínio tem Service — ver `docs/specs/modules.md` para o mapa exato.

### Repository (`app/Repositories/` + `Contracts/`)

Existe para: Client, ClientConsumer, Mail, Occurrence, Tenant, TypeOccurrence.
Bind central em `app/Providers/RepositoryServiceProvider.php` (um único provider, não por domínio como em projetos maiores).

### Model (`app/Models/`)

- Eloquent; multi-tenant via `App\Tenant\Traits\TenantTrait` (aplica `TenantScope` no `boot()` do model) nos models que pertencem a um tenant
- `App\Tenant\ManagerTenant` resolve o tenant do usuário autenticado (`auth()->user()->tenant_id`) — portanto só funciona em contexto autenticado

### Duas realidades coexistindo (documentar, não "corrigir" sem tarefa dedicada)

1. **Domínios com camadas completas** (Controller → FormRequest → Service → RepositoryInterface → Repository → Model): Tenant, Occurrence, Client, ClientConsumer, TypeOccurrence, Mail, Dashboard.
2. **CRUD simples direto no controller** (Controller injeta o **Model** — não um Repository — e chama Eloquent diretamente: `latest()`, `paginate()`, `where()`, `find()`, `create()`, `update()`, `delete()`): Category, Product, Table, User, Role, Profile, Permission, Issuing, StatusOccurrence, Driver.

Ao tocar um domínio do grupo 2, siga o padrão do grupo 2 (não force Service/Repository só porque outro domínio tem). Ao criar um domínio novo com regra de negócio real (múltiplos passos, side effects, integrações), siga o padrão do grupo 1 espelhando `OccurrenceController`/`OccurrenceService`/`OccurrenceRepository`.

## Multi-tenant

- Coluna `tenant_id` nos models de negócio
- `TenantScope` (global scope) filtra automaticamente por `app(ManagerTenant::class)->getTenantIdentify()`
- `ManagerTenant::isAdmin()` usa uma allowlist de e-mails em `config('tenant.admins')` (hardcoded em `config/tenant.php`) — não é um mecanismo de role; ver `docs/specs/security.md`
- Endpoints de API sem `auth:sanctum`/`auth:api` **não** têm o scope de tenant aplicado de forma segura (o scope depende de usuário autenticado) — ver gaps conhecidos em `docs/specs/security.md`

## Autenticação (mista, documentar como está)

- Web (admin/driver): sessão, `Auth::routes()` do `laravel/ui`, middleware `auth`
- API: Sanctum — mas alguns endpoints usam `auth:sanctum` e um usa `auth:api` (`GET /api/user`); não há padronização hoje. Novas rotas de API devem usar `auth:sanctum`, que é o guard efetivamente configurado para o front (ver `config/sanctum.php` e `EnsureFrontendRequestsAreStateful` no grupo `api`)
- `ensure.driver` middleware restringe o painel do motorista a usuários do tipo driver

## O que este documento não cobre

Specs de React, app mobile nativo e da ferramenta "graphify" (usadas em outros projetos do grupo, como o DistribTec) não se aplicam ao flySOP hoje — não existem essas superfícies neste repositório.
