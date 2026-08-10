# Instruções do Projeto — CIOP (Central Inteligente de Ocorrências Públicas)

Antes de realizar qualquer alteração no código consulte obrigatoriamente:

- docs/specs/architecture.md
- docs/specs/backend.md
- docs/specs/frontend.md
- docs/specs/coding-standards.md
- docs/specs/design-patterns.md
- docs/specs/security.md
- docs/specs/performance.md
- docs/specs/testing.md
- docs/specs/modules.md
- docs/specs/naming.md
- docs/specs/glossary.md
- docs/specs/audit.md
- docs/specs/flysop.md

## Regras

- Sempre siga a arquitetura existente (ver `docs/specs/architecture.md` para as duas realidades do projeto: domínios "camadas completas" e domínios "CRUD simples").
- Nunca crie novos padrões sem necessidade.
- Sempre reutilize implementações existentes.
- Respeite SOLID, DRY, KISS e YAGNI.
- Antes de concluir qualquer implementação execute mentalmente a auditoria definida em `docs/specs/audit.md`.
- Corrija automaticamente problemas identificados antes de finalizar a implementação, ou documente explicitamente por que não foram corrigidos agora.

## Prioridade em caso de conflito

1. `docs/specs/flysop.md`
2. `docs/specs/backend.md` / `docs/specs/frontend.md`
3. `docs/specs/architecture.md`
4. Demais specs em `docs/specs/`

## Fora de escopo neste projeto

Este projeto não usa React, Next.js, app mobile nativo, nem a ferramenta "graphify" usada em outros projetos do grupo (ex.: DistribTec). Não aplique specs ou instruções voltadas a essas stacks aqui — o flySOP é uma aplicação Laravel monolítica com Blade/AdminLTE/Vue 2 (ver `docs/specs/architecture.md` e `docs/specs/frontend.md`).

Todas as instruções são cumulativas.
