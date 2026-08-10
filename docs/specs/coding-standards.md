# Coding standards

Convenções observadas no código do flySOP. Nomenclatura detalhada em `naming.md` (evitar duplicar aqui).

## Princípios aplicados no projeto

- **SRP**: controller fino quando o domínio já tem Service; Service orquestra; Repository persiste — nos domínios que ainda fazem CRUD simples, o controller concentra tudo (ver `architecture.md`, "duas realidades")
- **DRY**: reutilizar `FormRequest`, Service e Repository existentes antes de criar novos; atenção especial à duplicidade já conhecida entre `Http/Requests/StoreUpdateOccurrences` e `Http/Requests/Api/StoreUpdateOccurrences`, e entre os dois endpoints de posição do motorista (web e API)
- **KISS / YAGNI**: seguir o domínio vizinho; não introduzir Service/Repository/Interface para um CRUD simples só por "consistência arquitetural"

> Nota: o projeto está em transição (ver `REFATORACAO_PLANO.md`, Fase 3/7) de "Eloquent direto no controller" para "Controller → Service → Repository". Migre um domínio para o padrão em camadas quando for mexer nele por outro motivo (nova regra de negócio), não como refatoração isolada sem necessidade.

## Backend

- Namespace `App\` PSR-4
- PHP 8 property promotion (`protected readonly` ou `protected` direto no construtor) em código novo — código mais antigo ainda usa `protected $x` + atribuição manual; não reescreva arquivo inteiro só para modernizar a sintaxe
- Migrations em `database/migrations/`
- Upload: `Storage::` (local ou S3 via `flysystem-aws-s3-v3` conforme `.env`); sempre validar `hasFile()`/`isValid()`
- Sem secrets no código (`.env`); `config/tenant.php` hoje tem e-mails hardcoded — não replique esse padrão em config novo (ver `security.md`)
- PHPDoc nos métodos de controllers "CRUD simples" (padrão observado em `ProductController`) é bem-vindo mas não obrigatório em Services/Repositories novos, que já usam type hints PHP 8

## Frontend

- Blade como padrão; ver `frontend.md` para quando usar Vue 2 vs Blade puro
- Vue 2 (não Vue 3) — não introduzir Composition API/Vue 3 patterns
- Site público: ES6 puro, sem jQuery
- Admin: jQuery/Bootstrap 4 (AdminLTE) ainda é o padrão das telas de CRUD — não é dívida técnica a "corrigir" isoladamente

## Organização

- Backend "camadas completas": `Services/{Entity}Service.php` + `Repositories/{Entity}Repository.php` + `Repositories/Contracts/{Entity}RepositoryInterface.php`, sem subpastas por domínio (projeto pequeno o suficiente para não precisar)
- Backend "CRUD simples": Controller em `Http/Controllers/Admin/` injeta o Model direto, sem Service/Repository

## Exceções conhecidas (não copiar em código novo)

- `response()->json(['message', 'Ocorrência não cadastrada'], 404)` em `OccurrenceApiController::createNewOccurrence` — array com vírgula em vez de `'message' => '...'` (bug de sintaxe, não um formato válido de resposta)
- Código comentado deixado no controller (`//$this->sendMail($occurrence)`, rota `//Route::post('/occurrences', ...)`) — remover código morto ao tocar no arquivo, não deixar acumular
- Nome de model no plural (`Occurrences`, `OccurrencesImagens`) — mantido por compatibilidade, não use como referência para nomear models novos (singular é o padrão Eloquent e o resto do projeto segue isso: `Client`, `Driver`, `Tenant`)
- Typo em `MailRespositoryInterface` (falta o "o" de Repository) — não corrija o nome do arquivo existente sem avaliar impacto (autoload/bind), mas não repita o typo em interfaces novas
