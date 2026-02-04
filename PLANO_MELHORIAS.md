# Plano de Melhorias - Projeto Laravel SOP

## 1. SOLID

### 1.1 Single Responsibility (SRP)
- **Controllers**: Extrair lógica de negócio para Services (DashboardService, PlanService).
- **Models**: Manter apenas relacionamentos e atributos; queries complexas em Repositories.

### 1.2 Open/Closed (OCP)
- Uso de interfaces (Repository/Service) para extensão sem alterar código existente.
- Repositories já utilizados: Tenant, Occurrence, Client, TypeOccurrence.

### 1.3 Liskov Substitution (LSP)
- Contratos (interfaces) para Repositories e Services garantem substituição por implementações.

### 1.4 Interface Segregation (ISP)
- Interfaces específicas por domínio (ex: PlanRepositoryInterface, DashboardServiceInterface).

### 1.5 Dependency Inversion (DIP)
- Controllers dependem de abstrações (Services/Repositories) injetadas, não de implementações concretas.

## 2. Frontend

### 2.1 Usabilidade
- Formulários com labels claros, feedback de erro e estados de foco.
- Navegação consistente e breadcrumbs onde fizer sentido.
- Mensagens de sucesso/erro visíveis (toast ou alert).

### 2.2 Responsividade
- Layout fluido com breakpoints (mobile, tablet, desktop).
- Tabelas e cards que se adaptam em telas pequenas.
- Menu colapsável no mobile (navbar toggler funcional).

### 2.3 Layout
- Seção de planos integrada ao layout do site com @yield('content').
- Tipografia e espaçamento padronizados.
- Cores e contraste acessíveis.

## 3. Seeds de Acesso

- **fabio@fabio.com** / senha: 123456ab
- **robson@robson.com** / senha: 123456ab

Tenant e plano serão criados/utilizados pelo seeder para vincular os usuários.

### Como rodar apenas os usuários de acesso

```bash
# Com Docker
docker-compose -f docker-compose.dev.yml exec app php artisan db:seed --class=AdminUserSeeder

# Ou rodar todos os seeds (plano, tenant, usuários)
php artisan db:seed
```
