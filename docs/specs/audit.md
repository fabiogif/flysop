# Auditoria pré-conclusão

Antes de considerar uma tarefa concluída, percorra esta checklist mentalmente (e corrija o que falhar).
Baseada na arquitetura **real** do flySOP (`docs/specs/*`), não em Clean Architecture teórica.

## 1. Specs consultadas

- [ ] Li `architecture.md` e `backend.md` e/ou `frontend.md` conforme a área tocada
- [ ] Li `coding-standards.md`, `design-patterns.md`, `modules.md`
- [ ] Li `security.md`, `performance.md`, `testing.md` se aplicável

## 2. Arquitetura

- [ ] Segui o padrão do domínio vizinho — "camadas completas" (Service/Repository) **ou** "CRUD simples" (Model direto), conforme o domínio já usa (ver `architecture.md`)
- [ ] Não inventei Interface/Provider/lib sem precedente
- [ ] Reutilizei Service/Repository/FormRequest/componente existente quando havia equivalente (checar duplicidade conhecida: `StoreUpdateOccurrences` em dois namespaces, lógica de posição do motorista em dois controllers)
- [ ] Controller sem regra de negócio complexa quando o domínio já tem Service
- [ ] Sem HTML/lógica de view dentro de Service/Repository

## 3. SOLID / DRY / KISS / YAGNI

- [ ] Uma responsabilidade clara por classe/componente novo
- [ ] Sem duplicar lógica que já existe em outro Service/Controller
- [ ] Solução no menor escopo que resolve o pedido
- [ ] Sem abstração "para o futuro" (Service/Repository para CRUD sem regra de negócio)

## 4. Segurança

- [ ] Rota de API nova tem `auth:sanctum` (ou justificativa explícita para ser pública)
- [ ] Escopo por tenant garantido (via `TenantScope` ou filtro explícito se o contexto não é autenticado)
- [ ] Permissão (`can:`) quando o módulo admin equivalente já exige
- [ ] Validação de input via FormRequest
- [ ] Sem secrets/dados sensíveis novos no código

## 5. Performance

- [ ] Listagem paginada
- [ ] Eager loading (`with()`/`load()`) nas relações usadas
- [ ] Sem N+1 óbvio na feature

## 6. Frontend (se aplicável)

- [ ] Sub-stack correta para a área (Blade+AdminLTE no admin, ES6 puro no site, Vue 2 só se precisa de estado reativo)
- [ ] Sem jQuery novo no site público
- [ ] Flash message e nomenclatura de rota seguindo o domínio vizinho

## 7. Testes

- [ ] Teste Feature/Unit cobrindo o fluxo alterado (happy path + validação + auth/tenant se sensível)
- [ ] Suite existente ainda passa

## 8. Saída da auditoria

Se encontrar problemas:

1. Liste-os priorizados (bloqueante → menor)
2. Explique o desvio vs specs
3. Corrija antes de encerrar a tarefa, ou documente explicitamente por que não foi corrigido agora

Não declare a tarefa concluída com desvios bloqueantes abertos (ex.: endpoint de API novo sem auth, upload sem validação de tipo).
