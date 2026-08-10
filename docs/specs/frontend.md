# Frontend — especificações atuais

O flySOP não usa React/Next.js. O frontend é servido pelo próprio Laravel via Blade, com três sub-stacks JS distintas por área. Baseado exclusivamente no código em `resources/`.

## Stack

- Build: Vite (`vite.config.mjs`) para as entradas novas; Laravel Mix (`webpack.mix.js`) ainda presente para o legado do admin — os dois coexistem hoje
- Admin (AdminLTE): Blade + Bootstrap 4 + jQuery (tema `jeroennoten/laravel-adminlte`) + Vue 2.6 pontual para widgets que precisam de estado/reatividade (mapa de rastreamento)
- Site público: Blade + módulos ES6 puros via Vite, **sem jQuery** (migração feita — ver `REFATORACAO_PLANO.md`, item 2)
- Painel motorista: Blade + JS vanilla (geolocalização do navegador)
- Realtime: `laravel-echo` + `pusher-js`

## Estrutura

| Path | Papel |
|------|--------|
| `resources/views/admin/pages/{domínio}/` | CRUD Blade do painel (index/create/edit/show + `_partials/form`) |
| `resources/views/admin/includes/` | Partials compartilhados do layout AdminLTE |
| `resources/views/site/layouts/app.blade.php` | Layout semântico do site público (skip link, aria-labelledby) |
| `resources/views/site/pages/` | Páginas do site |
| `resources/views/driver/` | Views do painel do motorista |
| `resources/js/site.js` + `resources/js/site/*.js` | Módulos ES6 do site (`headerScroll`, `navbarToggler`, `movetop`) |
| `resources/js/admin/occurrenceMap.js` | Módulo vanilla (sem jQuery) para o mapa do formulário de ocorrência |
| `resources/js/components/*.vue` | Componentes Vue 2 (ex.: `DriverTracker.vue`) |
| `resources/js/app.js`, `bootstrap.js` | Entradas legadas (admin/jQuery/Bootstrap) |
| `resources/sass/`, `resources/css/` | Estilos (SCSS legado + `site-theme.css` com variáveis para o site novo) |

## Qual sub-stack usar

- **Novo CRUD do admin**: Blade + AdminLTE (tabela, formulário, badges de status), seguindo `resources/views/admin/pages/{domínio_vizinho}/`. Só introduza Vue se a tela precisar de estado reativo real (mapa, atualização em tempo real) — não converta telas de CRUD simples para Vue sem necessidade
- **Nova página do site público**: Blade + módulo ES6 em `resources/js/site/`, registrado em `site.js`; não adicionar jQuery de volta
- **Widget com dado em tempo real** (ex.: mapa, status ao vivo): componente Vue 2 em `resources/js/components/`, seguindo `DriverTracker.vue` como referência (props explícitas, sem novo store global)

## Fluxo de página admin (padrão)

```
Blade view (admin.pages.{domínio}.index)
  → link/form aponta para rota nomeada (`route('{recurso}.{ação}')`)
  → Controller Admin\* (auth + can:{permission} quando aplicável)
  → Service (se existir) ou Eloquent direto
  → view(...)->with(...) ou redirect()->route(...)->with('messageSuccess...', ...)
```

Feedback de sucesso via flash session (`with('messageSuccessDriver', ...)`, `with('messageSuccess', ...)`) — nome da chave varia por domínio; siga o padrão do domínio vizinho ao adicionar uma tela nova (não invente uma quarta convenção de nome de flash).

## Formulários

1. Blade com `@error`/`is-invalid` nos campos (ver formulário de ocorrências)
2. Submit para rota `store`/`update` do resource
3. Validação via `FormRequest StoreUpdate{Entity}`, mensagens em português
4. Upload de arquivo: `enctype="multipart/form-data"` + `$request->hasFile(...)` no controller/service

## Realtime (dashboard)

- Posição do motorista: evento `DriverPositionUpdated` via Pusher, consumido por `DriverTracker.vue`
- Ocorrências recentes: polling simples via `fetch()` a cada 15s no dashboard (`admin/dashboard/occurrences-recent`), não WebSocket — não migrar para WebSocket sem necessidade real (ver `REFATORACAO_PLANO.md`, item 4, que marca isso como opcional)

## Naming

- Views Blade: `snake_case`/kebab conforme a pasta já usa (`admin.pages.drivers.index`)
- Componentes Vue: arquivo e export em PascalCase (`DriverTracker.vue`)
- Módulos JS vanilla/ES6: camelCase (`occurrenceMap.js`, `headerScroll.js`)

## O que não é padrão do projeto

- React, Next.js, TypeScript no frontend
- App mobile nativo
- State manager global (Vuex/Redux) — Vue 2 usado apenas com estado local do componente
- Introduzir jQuery em código novo do site público (removido de propósito)
