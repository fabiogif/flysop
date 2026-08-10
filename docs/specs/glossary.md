# Glossário

Termos como usados neste repositório (CIOP — Central Inteligente de Ocorrências Públicas).

| Termo | Significado |
|-------|-------------|
| Tenant | Empresa/cliente da plataforma; isolamento por `tenant_id` via `TenantScope` |
| ManagerTenant | Serviço que resolve o tenant do usuário autenticado (`getTenantIdentify`, `getTenant`, `isAdmin`) |
| Ocorrência (Occurrence) | Registro de uma ocorrência pública reportada (título, descrição, geolocalização, status, tipo, órgão, motorista) |
| Órgão (Issuing) | Entidade emissora/responsável vinculada a uma ocorrência |
| Status da ocorrência (StatusOccurrence) | Estado do fluxo: aberta, aguardando aceitação, aceita, recusada, em deslocamento, em atendimento, finalizada, etc. |
| Tipo de ocorrência (TypeOccurrence) | Categoria da ocorrência |
| Motorista (Driver) | Usuário responsável por atender ocorrências em campo; tem status (disponível, em deslocamento, em atendimento) |
| DriverPosition | Registro de latitude/longitude de um motorista, opcionalmente vinculado a uma ocorrência |
| DriverPositionUpdated | Evento broadcast via Pusher quando a posição de um motorista é atualizada com ocorrência vinculada |
| Cliente (Client) / ClientConsumer | Entidades de cadastro do lado do consumidor/cliente da plataforma (distintas de Tenant) |
| ACL (Permission/Role/Profile) | Autorização própria do projeto (não é pacote de terceiros) |
| AdminLTE | Tema admin (Bootstrap 4 + jQuery) usado no painel `/admin` |
| Sanctum | Guard de autenticação da API (`auth:sanctum`) |
| ensure.driver | Middleware que restringe rotas ao usuário do tipo motorista |
| FormRequest `StoreUpdate{Entity}` | Validação única para criar e atualizar a entidade |

Ver também `docs/specs/modules.md` para o mapa de domínios e camadas.
