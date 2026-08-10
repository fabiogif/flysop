# flySOP Rules

Antes de implementar qualquer feature:

1. Identifique se o domínio já segue o padrão "camadas completas" (Service/Repository) ou "CRUD simples" (Model direto) — ver `docs/specs/architecture.md` e `docs/specs/modules.md`.
2. Procure uma implementação equivalente já existente (Controller, Service, Repository, FormRequest, componente Vue, módulo JS) antes de criar uma nova.
3. Produza uma análise de impacto quando a mudança afetar autenticação, autorização, schema do banco, endpoints públicos ou múltiplos domínios.
4. Só então implemente.

---

Sempre preserve:

Rota

↓

Controller

↓

FormRequest

↓

Service (quando o domínio já tem)

↓

Repository (quando o domínio já tem)

↓

Model

↓

Resource (API) ou View Blade (web)

Nunca pule essa arquitetura nos domínios que já a seguem. Nos domínios de CRUD simples, não force as camadas que não existem — siga o padrão real do domínio vizinho.

---

Antes de criar:

- Controller
- Service
- Repository
- FormRequest
- Componente Vue
- Módulo JS

Procure uma implementação existente. Prefira estender módulos existentes. Nunca duplique lógica de negócio — atenção especial ao ponto de duplicação ainda existente no projeto: `StoreUpdateOccurrences` (dois namespaces, `App\Http\Requests` e `App\Http\Requests\Api`). A duplicação da gravação de posição do motorista foi corrigida (Fase 0): hoje só existe `Driver\DriverPositionController`, delegando para `App\Services\DriverPositionService`.

Se a mudança afetar:

- autenticação ou autorização
- schema do banco
- endpoints públicos de API
- múltiplos domínios

Pare após a análise de impacto e explique os riscos antes de continuar.

---

## Gaps de segurança conhecidos

Vários endpoints em `routes/api.php` não têm middleware de autenticação hoje (listados em `docs/specs/security.md`). Isso é uma lacuna real do projeto, não um padrão a seguir. Ao criar endpoint de API novo, use `auth:sanctum` por padrão.

---

Definition of Done:

✓ Padrão do domínio (camadas completas ou CRUD simples) preservado

✓ Sem código duplicado novo

✓ Padrões existentes reutilizados

✓ Sem endpoint de API novo desprotegido sem justificativa

✓ Teste cobrindo o fluxo alterado
