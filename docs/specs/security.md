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

Toda rota de API nova deve ter `auth:sanctum` a menos que exista uma razão documentada para ser pública:
- Rotas públicas documentadas: `/sanctum/token` (login do cidadão no app por e-mail + senha, `throttle:5,1`, mesmo formato de `/auth/staff/login`), `/client`, `/clientConsumer`, `POST /occurrences` (abertura pública de chamados), `POST /auth/register` (cadastro), `GET /profile/{username}` (perfil público), `GET /announcements` (informativos publicados) e `GET /occurrence-stats` (só totais agregados por etapa, sem dado de ocorrência) — esses dois alimentam a página inicial do app, usada também sem conta. Removidas por permitirem entrar na conta de outro cidadão: `POST /auth/citizen` (login só com celular, sem senha) e `POST /auth/social` (aceitava provider_id + e-mail sem validar com o Google/Apple).
- Ocorrência só é visível para o autor e para o gestor do órgão: na API pública o mapa lista só as do cidadão logado; detalhe e consulta por protocolo exigem ser o autor ou, sem conta, o código de acompanhamento (aleatório, só o hash em `occurrences.tracking_code_hash`; o protocolo é sequencial e não basta).
- Rotas protegidas novas (`auth:sanctum`): `GET /my-occurrences`, `PUT /profile`, `POST /profile/avatar`, `GET /surveys`, `POST /surveys/{token}/responses`.
- `POST /auth/staff/login` (login do gestor de órgão no app) é público por ser o login, com `throttle:5,1`; só emite token para `User` com `issuing_id`.
- Públicas para o uso sem cadastro (ocorrência anônima): `GET /typeOccurrence`, `GET /occurrences` (mapa), `GET /occurrences/{id}` e `GET /occurrences/protocol/{protocol}` — sem dados pessoais (`PublicOccurrenceResource`); o autor logado recebe o detalhe completo resolvendo o token com `$request->user('sanctum')`.

### Ocorrência anônima
`occurrences.is_anonymous`: com cidadão logado, a ocorrência continua com o `clients_id` dele (acompanha em "Minhas ocorrências"), mas `OccurrenceResource` oculta nome/CPF/RG/e-mail/`clients_id` para quem não é o autor, o detalhe do gestor devolve `requester: null`, a notificação vai sem autor e o painel/PDF mostram "Anônimo". Sem login, usa o client placeholder "Anônimo" e é sempre anônima. `POST /occurrences` (público) ignora do payload `clients_id`, `users_id`, `protocol`, `status_occurrences_id`, `driver_id`, `priority_id`, `due_at` e `possible_duplicate_of_id` — antes era possível abrir ocorrência em nome de outro cidadão ou já finalizada/atribuída. **Deploy**: notificações são `ShouldQueue`; após publicar mudança nelas, rodar `php artisan queue:restart` (o worker mantém o código antigo em memória).

### Dois tipos de token Sanctum (cidadão x gestor)
`Client` (cidadão) e `User` (gestor de órgão, `users.issuing_id` preenchido) emitem tokens Sanctum na mesma tabela. Rotas do cidadão (`/profile`, `/my-occurrences`) usam `ensure.citizen`; rotas `/api/manager/*` usam `ensure.manager`. Sem esses middlewares, um token de um tipo passaria em rotas que assumem o outro (ex.: id de `User` gravado como `clients_id`). Em `OccurrenceApiController::createNewOccurrence`, só um `Client` vira autor da ocorrência. `OccurrencePolicy` limita o gestor (não admin) às ocorrências do próprio órgão em `view`/`update`, mesmo que ele tenha a permissão `occurrences`.

### LGPD no app do cidadão
`GET /api/occurrences` (mapa) e `GET /api/occurrences/{id}` de ocorrência de outra pessoa usam `PublicOccurrenceResource` (sem nome, CPF, RG, e-mail, telefone, autor ou descrição). O detalhe completo (`OccurrenceResource`) só vai para o autor. `GET /occurrences/getOccurrenceByClientId/{clientId}` só responde para o próprio cliente (403 caso contrário). O detalhe do gestor (`/api/manager/occurrences/{id}`) grava `activity('lgpd_access')`, como o painel.

### Perfil Público e Isolamento LGPD
O endpoint `GET /api/profile/{username}` utiliza `PublicProfileResource`, que restringe os dados retornados a: `id`, `name`, `username`, `avatar_url`, `bio`, `website`, `created_at` e `occurrences_count`. Campos privados (`phone`, `email`, `cpf`, `rg`, `password`) são omitidos para evitar vazamento acidental.
## LGPD (Fase 6 do plano de execução)

- Consentimento explícito: `App\Http\Requests\Api\StoreUpdateOccurrences` (formulário público, sem login) exige `lgpd_consent` (`required|accepted`). O request do admin (`App\Http\Requests\StoreUpdateOccurrences`) não exige — quem preenche é o atendente, não o titular dos dados
- Log de acesso: `Admin\OccurrencesController::show()` grava em `activity('lgpd_access')` — log nomeado separado do log `default` de auditoria de campo (`spatie/laravel-activitylog`), para não misturar "quem viu" com "quem editou"
- Direito ao esquecimento: `OccurrenceService::forgetPersonalData()`, ability `forget` do `OccurrencePolicy` (só Supervisor/Administrador), rota `DELETE /admin/occurrences/{id}/forget` — anonimiza nome/CPF/RG/e-mail/telefone com `disableLogging()`/`enableLogging()` ao redor do update, para o dado não ficar gravado no diff do audit log antes de ser apagado
- Retenção/anonimização em lote: `App\Console\Commands\AnonymizeOldOccurrencesCommand` (`occurrences:anonymize-old --days=730`), agendado mensalmente em `app/Console/Kernel.php` — só atinge ocorrências com status terminal
- PDF (`barryvdh/laravel-dompdf`) tem `enable_remote=false` em `config/dompdf.php` — a descrição da ocorrência é conteúdo enviado pelo cidadão, então renderizar URLs remotas no PDF seria um vetor de SSRF

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
