# Testing — práticas atuais

## Estado real

`tests/Feature/` (com subpasta `Api/` já criada, ainda vazia) e `tests/Unit/` só contêm os testes de exemplo padrão do Laravel (`ExampleTest.php`). **Não há cobertura de teste real hoje.** Não trate a ausência de testes como "seguir o padrão do projeto" — é uma lacuna conhecida, não uma decisão.

## O que fazer ao tocar um domínio

- Ao adicionar/alterar um endpoint de API, adicione um teste Feature em `tests/Feature/Api/` cobrindo pelo menos:
  1. Happy path (status 200/201, dado persistido — `assertDatabaseHas`)
  2. Validação (422 quando o `FormRequest` rejeita)
  3. Autenticação/tenant (401/403/404 quando aplicável — especialmente relevante dado os gaps listados em `security.md`)
- Ao adicionar/alterar uma tela admin (CRUD), teste Feature via rota web (`get`/`post` + `assertRedirect`/`assertSee`) é suficiente; não é necessário Dusk/browser test
- Ao adicionar lógica de negócio em um Service (ex.: `OccurrenceService::acceptOccurrence`), um teste Unit isolando o Service (com Repository mockado ou `RefreshDatabase`) é preferível a só testar via HTTP

## Base disponível

- `Tests\TestCase` (`tests/TestCase.php`) — ainda sem helpers de autenticação prontos; se o primeiro teste de um domínio precisar de usuário autenticado, pode adicionar um helper (`actingAsUser`, por exemplo) ao `TestCase` e documentar a decisão no PR
- PHPUnit 9 (`phpunit.xml` já configurado)
- Factories em `database/factories/` — checar se existe factory do model antes de criar dado de teste manualmente

## O que não exigir sem precedente

- Coverage mínimo obrigatório (não há baseline hoje)
- E2E (Dusk/Playwright)
- Teste de Blade pixel-perfect

## Checklist pós-implementação

- [ ] Pelo menos um teste Feature cobrindo o fluxo principal alterado
- [ ] Teste de validação (422) se mudou um FormRequest
- [ ] Teste de autorização/tenant se o endpoint é sensível (ver `security.md`)
- [ ] Suite existente ainda passa (`php artisan test` / `vendor/bin/phpunit`)
