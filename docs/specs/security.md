# Security — práticas atuais e gaps conhecidos

## Autenticação

- Web (admin/driver): sessão Laravel padrão (`laravel/ui`, `Auth::routes()`), middleware `auth`
- API: Sanctum — `auth:sanctum` na maioria das rotas protegidas; `GET /api/user` usa `auth:api` (guard diferente, herdado do scaffold padrão do Laravel) — inconsistência conhecida, não copiar `auth:api` em rota nova, usar `auth:sanctum`
- Painel motorista: `auth` + `ensure.driver`

## Autorização

- ACL própria via models `Permission`, `Role` (não é um pacote de terceiros tipo spatie/laravel-permission). `Profile` existe mas é **legado morto** — não ligado a `User`, não reativar (ver comentário em `app/Models/Profile.php`)
- `app/Providers/AuthServiceProvider.php` registra dinamicamente um `Gate::define($permission->name, ...)` para cada linha de `permissions` (checa `$user->hasPermission()`) — é isso que faz `can:{permission}` (middleware) e `@can` (Blade) funcionarem para qualquer permissão cadastrada, sem precisar declarar Gate por Gate
- `Gate::before` nesse mesmo provider dá acesso irrestrito a quem `isAdmin()` é `true` (exceto a ability `driver.panel`, que tem regra própria) — qualquer Policy nova é automaticamente ignorada para admin
- Alguns controllers usam `$this->middleware(['can:{permission}'])` no construtor (ex.: `ProductController`) — nem todo controller admin tem essa checagem hoje; ao criar CRUD novo no admin, adicione a permissão seguindo esse padrão
- Policies nativas do Laravel (`app/Policies/`, registradas em `AuthServiceProvider::$policies`) para autorização por registro (não só por módulo) — hoje só `OccurrencePolicy` (Fase 2 do plano de execução), cobrindo `viewAny/view/create/update/delete`. Antes da Fase 2, `OccurrencesController::destroy` não tinha **nenhuma** checagem além de `auth` — qualquer usuário logado podia excluir qualquer ocorrência
- `App\Models\Traits\UserACLTrait::isAdmin()` (usado em ~10 pontos do código: Gate::before, `EnsureUserIsDriver`, controllers do painel motorista, `routes/channels.php`) hoje checa a role "Administrador" (`hasRole()`) **ou**, como fallback transitório, `config('acl.admin')` (allowlist de e-mail) — não remover o fallback sem confirmar que todo admin de produção já tem a role atribuída (`database/seeders/PermissionMenuSeeder.php` sincroniza role a partir da mesma config). **Atenção**: existe um segundo método `isAdmin()`, em `App\Tenant\ManagerTenant`, que checa `config('tenant.admins')` — esse é código morto (nenhum call site fora da própria classe); não confundir os dois nem "consertar" o errado

## Multi-tenant

- `tenant_id` nos models de negócio; `TenantScope` (global scope) + `TenantTrait` aplicam o filtro automaticamente
- `TenantScope` depende de usuário autenticado (`ManagerTenant::getTenantIdentify()` chama `auth()->user()->tenant_id`) — **em rota sem autenticação, o scope não tem como funcionar corretamente**
- Uploads por tenant: `tenants/{tenant->uuid}/...`

## Gaps corrigidos em `routes/api.php` (Fase 0 do plano de execução)

Os endpoints abaixo não tinham middleware de autenticação; validado com o time que nenhum client externo dependia do acesso sem login, e foram movidos para o grupo `auth:sanctum`:

- `GET /occurrences`, `GET /occurrences/{uuid}`, `GET /occurrences/getOccurrenceByClientId/{clientId}`
- `GET /tenants`, `GET /tenants/{uuid}`
- `GET /typeOccurrence`

`POST /driver/position` (`Api/Driver/DriverLocationController`) foi **removido**: aceitava `driver_id`/`latitude`/`longitude` de qualquer origem sem autenticação, era uma duplicata de `Driver\DriverPositionController` (ver `docs/specs/modules.md`), e não havia nenhum client externo dependendo dele. A gravação de posição do motorista agora tem um único ponto de verdade em `App\Services\DriverPositionService`, usado pela rota web autenticada (`POST /driver/position`, guard `web` + `ensure.driver`).

Toda rota de API nova deve ter `auth:sanctum` a menos que exista uma razão documentada para ser pública (ex.: `/sanctum/token`, `/client`, `/clientConsumer`, `POST /occurrences` de cadastro público).

## Validação e input

- Validar via `FormRequest` (padrão) ou `validate()` inline (aceitável em endpoints pequenos como `DriverLocationController`)
- Upload: checar `hasFile()` + `isValid()` antes de mover o arquivo; tipo/tamanho quando o `FormRequest` já define regras de `image`/`mimes`/`max`

## Secrets

- Credenciais apenas em `.env` / secrets de CI-CD (Fly.io/Vercel/Docker)
- Nunca commitar `.env` real (há `.env.docker.example` como template)
- `config/tenant.php` com e-mails em texto claro não é um secret técnico, mas é dado versionado que idealmente viria de `.env`/banco — não adicionar mais dados sensíveis desse jeito

## Throttling

- `throttle:api` no grupo `api` (Kernel)
- `throttle:20,1` explícito na rota `driver/position` (web)

## Checklist rápido antes de merge

- [ ] Endpoint de API novo tem `auth:sanctum` (ou justificativa documentada para ser público)
- [ ] Escopo por tenant garantido (via `TenantScope`/model, ou filtro explícito se o contexto não é autenticado)
- [ ] Permissão (`can:`) quando o módulo admin equivalente já exige
- [ ] Validação de input via FormRequest
- [ ] Sem secrets/dados sensíveis versionados no código
- [ ] Upload validado (tipo/tamanho) antes de mover o arquivo
