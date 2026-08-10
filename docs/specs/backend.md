# Backend — especificações atuais

Baseado exclusivamente no código deste repositório (raiz, sem `backend_distribtec` — o flySOP não é monorepo).

## Stack

- Laravel 8, PHP `^7.3|^8.0`
- Auth: Sanctum (API/SPA) + `laravel/ui` (sessão web) — guards mistos, ver `architecture.md`
- Telescope (debug/observabilidade local)
- AdminLTE (`jeroennoten/laravel-adminlte`) para o painel admin
- `league/flysystem-aws-s3-v3` — uploads (produtos, anexos de ocorrência) em disco local ou S3 conforme `.env`
- Pusher (`pusher/pusher-php-server`) — broadcasting de eventos (posição do motorista)
- `ramsey/uuid` — usado em Tenant (`uuid`) e recursos que expõem identificador público

## Fluxo preferido (domínio com regra de negócio)

```
Route (web.php ou api.php)
  → Controller (auth via middleware, nunca checagem manual de sessão)
  → FormRequest StoreUpdate{Entity} (validated())
  → Service (orquestra regra de negócio, side effects, upload)
  → RepositoryInterface → Repository → Model
  → View Blade (web) / Resource ou response()->json() (api)
```

Referência limpa: `OccurrencesController` (admin) → `OccurrenceService` → `OccurrenceRepositoryInterface` → `OccurrenceRepository`. Também `TenantController` → `TenantService` → `TenantRepository`.

## Fluxo aceito para CRUD simples (sem regra de negócio relevante)

```
Route (Route::resource)
  → Controller (Model injetado no construtor como "$this->repository")
  → FormRequest StoreUpdate{Entity}
  → Eloquent direto (create/update/delete/where)
  → View Blade
```

Referência: `ProductController`, `DriverController`, `CategoryController`. Não promova esse controller para Service/Repository sem uma razão concreta (nova regra de negócio, reuso em outro lugar, teste que exige isolar a persistência).

## Controllers

- Middleware de auth no grupo de rotas (`web.php`), não checagem manual dentro do método
- Alguns controllers usam `$this->middleware(['can:{permission}'])` no construtor (ex.: `ProductController`) — siga esse padrão ao adicionar permissão a um CRUD do admin
- Escopo de tenant explícito quando o controller acessa o Model direto: `where('tenant_id', auth()->user()->tenant_id)` (ver `DriverController::scopeTenant()`) — não confiar apenas no `TenantScope` global em queries fora de model direto (ex.: joins, `DB::`)
- Sem HTML inline; views sempre em `resources/views/`

## Services

- Classe concreta `*Service` (sem interface) para a maioria; `DashboardServiceInterface` é a única exceção — se criar interface para um Service novo, tenha um motivo (múltiplas implementações, teste com mock), não é o padrão default do projeto
- Injeção via `protected` no construtor (property promotion do PHP 8 onde já usado — `OccurrenceService`, `OccurrenceRepository`); em código mais antigo/PHP 7.3-compatível, `protected $x` + atribuição manual (`OccurrenceApiController`) — siga o estilo do arquivo vizinho que está editando
- Sem Request/Response HTTP dentro do Service

## Repositories

```
app/Repositories/Contracts/FooRepositoryInterface.php
app/Repositories/FooRepository.php
```

Bind único em `app/Providers/RepositoryServiceProvider.php` (não há provider por domínio neste projeto — diferente de projetos maiores do grupo). Ao adicionar um novo par Repository/Interface, registre o bind ali.

## Validação

- `FormRequest` é o padrão para todo endpoint que já tem um (`StoreUpdate{Entity}` cobre create **e** update na mesma classe — não crie `Store*`/`Update*` separados)
- **Atenção a duplicidade real**: existem `App\Http\Requests\StoreUpdateOccurrences` e `App\Http\Requests\Api\StoreUpdateOccurrences` (mesmo nome, namespaces diferentes). Antes de criar uma FormRequest nova, procure em `app/Http/Requests/` **e** `app/Http/Requests/Api/` se já existe equivalente — não crie uma terceira variante

## Serialização (API)

- `*Resource` em `app/Http/Resources/` para os domínios que já expõem API (Client, ClientConsumer, Occurrence, StatusOccurrence, Tenant, TypeOccurrence)
- Não existe um envelope JSON central (tipo `ApiResponseClass`) neste projeto — respostas manuais usam `response()->json([...], $status)` direto no controller; ao adicionar endpoint novo, siga o formato do endpoint vizinho do mesmo domínio em vez de inventar um terceiro formato

## Upload de arquivos

- `Storage::put` / `$file->store(...)` direto no controller ou Service, path por tenant quando aplicável: `tenants/{tenant->uuid}/products`, `occurrence/occurrences`
- Sempre checar `$request->hasFile(...)` e `->isValid()` antes de mover o arquivo
- Ao substituir arquivo em update, apagar o antigo com `Storage::delete()` se existir (ver `ProductController::update`)

## Eventos / broadcasting

- `App\Events\DriverPositionUpdated` disparado ao salvar `DriverPosition` vinculada a uma ocorrência, via `App\Services\DriverPositionService::record()` (`broadcast(new DriverPositionUpdated($position))->toOthers()`)
- Ponto único de gravação de posição do motorista: `Driver\DriverPositionController@store` (painel autenticado, `auth`+`ensure.driver`, throttle `20,1`) delega para `DriverPositionService`. O antigo endpoint público duplicado `Api\Driver\DriverLocationController@store` foi removido (Fase 0 do plano de execução): aceitava `driver_id` no payload sem autenticação e não tinha cliente externo dependente. Ao alterar a lógica de gravação de posição, mexa só no Service

## Fila e broadcasting (dev vs produção)

- Dev (`docker-compose.dev.yml`): `QUEUE_CONNECTION=redis` (serviço `redis`, cliente `predis/predis` porque `ext-redis` não está instalado na imagem — ver `REDIS_CLIENT` no `.env`) + serviço `queue` rodando `php artisan queue:work redis`; `BROADCAST_DRIVER=pusher` apontando para o serviço `soketi` (self-hosted, protocolo Pusher), com `PUSHER_HOST=soketi`/`PUSHER_PORT=6001`/`PUSHER_SCHEME=http` no lado servidor e `MIX_PUSHER_WS_HOST=localhost`/`MIX_PUSHER_WS_PORT=6001`/`MIX_PUSHER_FORCE_TLS=false` no lado navegador (`resources/js/bootstrap.js`)
- Produção (Fly.io): ainda **não** migrada — `fly.toml` continua sem Redis/worker/broadcast configurados (ver comentário no próprio `fly.toml`); é uma decisão de custo/infra em aberto, não replicar a config de dev sem provisionar Redis e um provedor WebSocket de produção primeiro

## Rotas

- Web: `routes/web.php`, grupos `admin` (`middleware('auth')`), `driver` (`middleware(['auth', 'ensure.driver'])`), site público sem middleware de auth
- API: `routes/api.php` — endpoints de leitura de ocorrências/tenants/tipos exigem `auth:sanctum` (corrigido na Fase 0); ao criar endpoint novo, adicione `auth:sanctum` salvo decisão explícita de deixá-lo público (ex.: `/sanctum/token`, `/client`, `/clientConsumer`)

## Testes backend

- `tests/Feature/` (inclui `tests/Feature/Api/`), `tests/Unit/` — hoje só contêm os testes de exemplo padrão do Laravel (`ExampleTest.php`), cobertura real ainda não existe
- `Tests\TestCase` disponível; não há ainda helpers de auth customizados (tipo `actingAsUser`) — ao escrever o primeiro teste de um domínio, pode introduzir esse helper em `TestCase` para reuso, documentando a decisão
