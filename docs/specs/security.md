# Security — práticas atuais e gaps conhecidos

## Autenticação

- Web (admin/driver): sessão Laravel padrão (`laravel/ui`, `Auth::routes()`), middleware `auth`
- API: Sanctum — `auth:sanctum` na maioria das rotas protegidas; `GET /api/user` usa `auth:api` (guard diferente, herdado do scaffold padrão do Laravel) — inconsistência conhecida, não copiar `auth:api` em rota nova, usar `auth:sanctum`
- Painel motorista: `auth` + `ensure.driver`

## Autorização

- ACL própria via models `Permission`, `Role`, `Profile` (não é um pacote de terceiros tipo spatie/laravel-permission)
- Alguns controllers usam `$this->middleware(['can:{permission}'])` no construtor (ex.: `ProductController`) — nem todo controller admin tem essa checagem hoje; ao criar CRUD novo no admin, adicione a permissão seguindo esse padrão
- "Admin" de plataforma (cross-tenant) é resolvido por **allowlist de e-mail hardcoded** em `config/tenant.php` (`ManagerTenant::isAdmin()`) — é um mecanismo frágil (exige deploy para adicionar/remover admin, e-mails ficam versionados no repo). Não expandir esse padrão para novas features de autorização; se precisar de mais um "super admin", prefira migrar para uma coluna/role no banco

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
- `throttle:20,1` explícito na rota `driver/position` (web) — o endpoint equivalente da API (`Api/Driver/DriverLocationController`) não tem throttle dedicado, só o `throttle:api` genérico

## Checklist rápido antes de merge

- [ ] Endpoint de API novo tem `auth:sanctum` (ou justificativa documentada para ser público)
- [ ] Escopo por tenant garantido (via `TenantScope`/model, ou filtro explícito se o contexto não é autenticado)
- [ ] Permissão (`can:`) quando o módulo admin equivalente já exige
- [ ] Validação de input via FormRequest
- [ ] Sem secrets/dados sensíveis versionados no código
- [ ] Upload validado (tipo/tamanho) antes de mover o arquivo
