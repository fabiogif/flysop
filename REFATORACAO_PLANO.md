# Plano de Refatoração e Evolução – CIOP (Central Inteligente de Ocorrências Públicas)

Documento mestre para guiar a refatoração e evolução do sistema, com foco em **monitoramento em tempo real**, **UX/UI moderna** e **arquitetura sólida**.

---

## 1. Layout e Experiência do Usuário

**Objetivos:**
- Página inicial mais moderna, limpa e intuitiva
- Alinhada aos padrões atuais de UX/UI
- Totalmente responsiva (mobile-first)
- Boas práticas de usabilidade e acessibilidade
- Tipografia, cores, espaçamentos e hierarquia visual padronizados
- Textos claros, objetivos e orientados ao usuário

**Entregas (Fase 2 concluída):**
- [x] Layout refatorado: `site/layouts/app.blade.php` (semântico, main, sections com aria-labelledby)
- [x] Skip link "Ir para o conteúdo", foco visível em botões e links
- [x] CSS do tema: `public/css/site-theme.css` (variáveis, espaçamento, cards, a11y)
- [x] Hero com título correto (CIOP - Central Inteligente de Ocorrências Públicas), CTA Acessar/Cadastrar
- [x] Seção Sobre com texto orientado ao usuário (ocorrências, geolocalização, tempo real)
- [x] Serviços: 3 cards (Geolocalização, Status em tempo real, Gestão integrada) com copy do SOP
- [x] Stats: labels orientados a monitoramento (Ocorrências, Mapa, Status)
- [x] Footer simplificado, copyright dinâmico (ano atual)
- [x] Scripts reduzidos (removidos owl carousel, particles, lightbox do layout)
- [x] Home `site/pages/home/index`: bloco "Comece a usar o SOP" com CTA

---

## 2. JavaScript e Frontend

**Objetivos:**
- Eliminar código legado desnecessário
- Organização, legibilidade e reutilização
- Evitar duplicação
- Padrões modernos (ES6+)
- Performance e manutenibilidade
- Preparar para atualizações em tempo real

**Entregas (concluídas):**
- [x] Site: módulos ES6 em `resources/js/site/` (headerScroll, navbarToggler, movetop) e bundle `resources/js/site.js`; layout site usa `@vite(['resources/js/site.js'])` sem jQuery
- [x] Vite: entrada `resources/js/site.js` em vite.config; scripts npm `dev`/`build` com Vite
- [x] Formulário ocorrências: script do mapa sem jQuery (lat/lng via getElementById); módulo opcional `resources/js/admin/occurrenceMap.js`

---

## 3. Cadastro e Gestão de Ocorrências

**Objetivos:**
- Validações claras (frontend e backend)
- Feedback visual adequado
- Campos bem organizados
- Clareza no status (aberta, em atendimento, finalizada, etc.)

**Entregas (concluídas):**
- [x] StoreUpdateOccurrences: regras para title, name, description, address, email, phone, latitude, longitude, status/type/issuings, driver_id, anexos; mensagens em português
- [x] Controller: store/update com `$request->validated()` e flash messageSuccess
- [x] Listagem: coluna Status com badges (aberta, em atendimento, finalizada, etc.); getPaginatedList usa scope com joins (nameStatus)
- [x] Formulário: @error/is-invalid em name, title, email, status_occurrences_id; cliente padrão no service quando clients_id ausente
- [x] Campo motorista no formulário de ocorrências (driver_id, lista do tenant)

---

## 4. Dashboard em Tempo Real

**Objetivos:**
- Ocorrências em tempo real
- Acompanhamento de status e evolução
- Visualização de deslocamento (mapa, status ou timeline)
- WebSockets, polling ou Laravel Broadcasting
- Dashboard intuitivo e orientado à decisão

**Entregas (concluídas):**
- [x] Polling: rota GET `admin/dashboard/occurrences-recent` retorna JSON (ocorrências recentes com status e tipo)
- [x] Dashboard: card "Ocorrências recentes" com lista atualizada a cada 15s via fetch(); indicador "Atualizado agora"
- [ ] Opcional: WebSockets/Broadcasting para push em tempo real
- [ ] Opcional: mapa ou timeline de deslocamento

---

## 5. Cadastro de Motoristas

**Objetivos:**
- Cadastro completo (dados pessoais essenciais)
- Status: disponível, em deslocamento, em atendimento
- Relacionamento com ocorrências
- Base para rastreamento

**Entregas (concluídas):**
- [x] Migration `create_drivers_table` (name, email, phone, cpf, status, tenant_id); migration `add_driver_id_to_occurrences_table`
- [x] Model Driver (statusLabels, tenant, occurrences); Occurrences.driver_id e relação driver()
- [x] DriverController: CRUD com escopo por tenant; search; StoreUpdateDriver
- [x] Views: index (com badges de status), create, edit, show (com lista de ocorrências vinculadas), _partials/form
- [x] Rotas e menu AdminLTE "Motoristas"; permissão `drivers` em PermissionMenuSeeder
- [x] Formulário de ocorrências: select motorista (driver_id); getFormData inclui drivers do tenant

---

## 6. Remoção da Funcionalidade de Planos

**Objetivos:**
- Remover completamente Planos (frontend, backend e banco)

**Entregas (Fase 1 concluída):**
- [x] Nova home sem listagem/assinatura de planos (SiteController, view home)
- [x] Remoção de rotas de planos (admin e site) e rota plan.subscription
- [x] TenantService::make(array $data) sem Plan; cadastro direto de tenant
- [x] RegisterController sem session('plan')
- [x] Migração: plan_id nullable em tenants (foreign key removida)
- [x] Tenant, Profile, Occurrences: remoção de relação com Plan
- [x] DashboardService sem totalPlans; AppServiceProvider sem PlanObserver
- [x] RepositoryServiceProvider sem PlanRepository/PlanService
- [x] Menu admin: Planos já estava comentado em config/adminlte.php
- [x] Opcional: remover arquivos órfãos (PlanController, DetailPlanController, PlanProfileController, Plan model, PlanRepository, PlanService, PlanObserver, factories, seeders, testes de Plan)
- [ ] Opcional: migração para dropar tabelas plans, detail_plans, plan_profile (após remover código que as referencie)

---

## 7. Qualidade e Manutenção

**Objetivos:**
- Código limpo e documentado
- SOLID, PSR-12, boas práticas Laravel
- Controllers enxutos; Services, Repositories e DTOs quando fizer sentido
- Legibilidade, testabilidade e manutenção
- Consistência frontend/backend

**Entregas (Fase 3 em andamento):**
- [x] Refatorar TenantController: uso de TenantService (getPaginatedList, findOrFail, createByAdmin, updateByAdmin, deleteByAdmin, search); TenantRepository com find, create, update, delete, search
- [x] Refatorar OccurrencesController: uso de OccurrenceService (getPaginatedList, getFormData, storeForAdmin, updateForAdmin, deleteForAdmin, search); OccurrenceRepository com getPaginatedList, find, update, delete, search; relação Occurrences hasMany OccurrencesImagens corrigida
- [x] OccurrenceService sem dependência de Plan; API OccurrenceApiController usa createNewOccurrence e anexos na ocorrência criada
- [x] Remoção de órfãos de Plan (controllers, services, repositories, models, observers, factories, seeders, testes)
- [ ] Validar e documentar seeders (DatabaseSeeder já documentado na execução)
- [ ] Comentários/README onde decisões técnicas forem relevantes

---

## Ordem de Execução Sugerida

| Fase | Descrição |
|------|-----------|
| **Fase 1** | Remover Planos (home nova, rotas, backend, migrações) |
| **Fase 2** | Layout/UX da página inicial (nova home, layout site) |
| **Fase 3** | Backend: SOLID, seeders, limpeza pós-planos |
| **Fase 4** | Cadastro de Motoristas (model, CRUD, relação com ocorrências) |
| **Fase 5** | Ocorrências: validações, feedback, status claros |
| **Fase 6** | JavaScript: refatoração ES6+, Vite, módulos |
| **Fase 7** | Dashboard tempo real (Broadcasting/polling + UI) |

---

## Resultado Esperado

- Sistema mais moderno, escalável e intuitivo
- Foco em monitoramento em tempo real e excelente UX
- Arquitetura sólida para evolução contínua
- Código fácil de manter e evoluir
