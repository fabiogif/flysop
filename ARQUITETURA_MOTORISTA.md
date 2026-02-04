# Arquitetura – Área do Motorista

**Objetivo:** Implementar área específica para motoristas com acesso diferenciado, visualização apenas de ocorrências vinculadas (a ele ou ao seu órgão) e fluxo de aceitação de ocorrências com acompanhamento em tempo real pelo painel admin.

---

## 1. Visão geral

### 1.1 Fluxo principal

```
1. Admin atribui ocorrência ao motorista (ou ao órgão do motorista)
2. Ocorrência fica com status "Aguardando aceitação" (novo status)
3. Motorista recebe notificação (e-mail/push)
4. Motorista acessa área específica e vê ocorrências disponíveis
5. Motorista aceita ou recusa a ocorrência
6. Se aceita: status muda para "Aceita" → motorista pode iniciar deslocamento → em atendimento → finalizada
7. Admin acompanha em tempo real no painel (mapa, timeline, status)
```

### 1.2 Diferenças do acesso admin

| Aspecto | Admin | Motorista |
|---------|-------|-----------|
| **Autenticação** | Guard `web` (User model) | Guard `web` (User model) + role/perfil "motorista" |
| **Menu** | Completo (ocorrências, motoristas, usuários, etc.) | Reduzido (ocorrências, perfil, sair) |
| **Ocorrências visíveis** | Todas do tenant | Apenas vinculadas a ele (`driver_id`) ou ao seu órgão (`issuings_id`) |
| **Ações permitidas** | CRUD completo | Ver detalhes, aceitar/recusar, atualizar status da própria ocorrência |
| **Dashboard** | Estatísticas gerais + mapa | Lista de ocorrências atribuídas + mapa das suas ocorrências |

---

## 2. Estrutura de dados

### 2.1 Modelos e relacionamentos

#### **Driver** (já existe)
```php
// app/Models/Driver.php
- id
- name
- email
- phone
- cpf
- status (disponivel, em_deslocamento, em_atendimento)
- tenant_id
- user_id (NOVO: relacionamento com User)
```

**Novo relacionamento:**
```php
public function user()
{
    return $this->belongsTo(User::class);
}
```

#### **User** (atualizar)
```php
// app/Models/User.php
- Adicionar relacionamento com Driver:
public function driver()
{
    return $this->hasOne(Driver::class);
}
```

#### **Occurrences** (atualizar)
```php
// app/Models/Occurrences.php
- Adicionar novo status: 'aguardando_aceitacao'
- Campo 'driver_id' já existe
- Campo 'issuings_id' já existe (órgão)
```

### 2.2 Novos status de ocorrência

| Status | Descrição | Quem pode ver |
|--------|-----------|---------------|
| `aguardando_aceitacao` | Ocorrência atribuída ao motorista, aguardando aceitação | Motorista atribuído + Admin |
| `aceita` | Motorista aceitou a ocorrência | Motorista + Admin |
| `recusada` | Motorista recusou a ocorrência | Motorista + Admin |
| `em_deslocamento` | Motorista iniciou deslocamento | Motorista + Admin |
| `em_atendimento` | Motorista chegou e está atendendo | Motorista + Admin |
| `finalizada` | Atendimento concluído | Motorista + Admin |

**Nota:** Status existentes (`aberta`, `em_atendimento`, `finalizada`) podem ser mantidos ou migrados para o novo fluxo.

### 2.3 Migrações necessárias

```php
// database/migrations/YYYY_MM_DD_HHMMSS_add_user_id_to_drivers_table.php
Schema::table('drivers', function (Blueprint $table) {
    $table->unsignedBigInteger('user_id')->nullable()->after('tenant_id');
    $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
    $table->index('user_id');
});

// database/migrations/YYYY_MM_DD_HHMMSS_add_status_aguardando_aceitacao_to_status_occurrences.php
// Inserir novo status na tabela status_occurrences (ou criar seeder)
DB::table('status_occurrences')->insert([
    ['name' => 'Aguardando aceitação'],
    ['name' => 'Aceita'],
    ['name' => 'Recusada'],
    ['name' => 'Em deslocamento'],
]);
```

---

## 3. Autenticação e autorização

### 3.1 Guard e provider

**Manter guard `web`** (não criar guard separado). Usar **perfis/permissões** para diferenciar acesso.

### 3.2 Perfil "Motorista"

```php
// database/seeders/ProfileSeeder.php ou PermissionMenuSeeder.php
// Criar perfil "Motorista" com permissões específicas:
- 'driver.occurrences.index' (ver ocorrências atribuídas)
- 'driver.occurrences.show' (ver detalhes)
- 'driver.occurrences.accept' (aceitar ocorrência)
- 'driver.occurrences.reject' (recusar ocorrência)
- 'driver.occurrences.update-status' (atualizar status da própria ocorrência)
```

### 3.3 Middleware

```php
// app/Http/Middleware/EnsureUserIsDriver.php
// Verifica se o usuário autenticado tem perfil "Motorista" ou está vinculado a um Driver
```

**Uso nas rotas:**
```php
Route::middleware(['auth', 'ensure.driver'])->prefix('driver')->group(function() {
    Route::get('/dashboard', [DriverDashboardController::class, 'index']);
    Route::get('/occurrences', [DriverOccurrenceController::class, 'index']);
    // ...
});
```

### 3.4 Scope de ocorrências para motorista

```php
// app/Models/Occurrences.php
public function scopeForDriver($query, $driverId, $issuingsId = null)
{
    return $query->where(function($q) use ($driverId, $issuingsId) {
        $q->where('driver_id', $driverId);
        if ($issuingsId) {
            $q->orWhere('issuings_id', $issuingsId);
        }
    });
}
```

---

## 4. Controllers e Services

### 4.1 DriverDashboardController

```php
// app/Http/Controllers/Driver/DriverDashboardController.php
namespace App\Http\Controllers\Driver;

class DriverDashboardController extends Controller
{
    public function index()
    {
        $driver = auth()->user()->driver;
        $occurrences = Occurrences::forDriver($driver->id, $driver->tenant->issuings_id ?? null)
            ->with(['statusOccurence', 'typeOccurrence'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('driver.pages.dashboard.index', compact('occurrences'));
    }
}
```

### 4.2 DriverOccurrenceController

```php
// app/Http/Controllers/Driver/DriverOccurrenceController.php
namespace App\Http\Controllers\Driver;

class DriverOccurrenceController extends Controller
{
    public function index()
    {
        // Lista de ocorrências atribuídas (aguardando aceitação, aceitas, em andamento)
    }
    
    public function show($id)
    {
        // Detalhes da ocorrência (com mapa, timeline)
    }
    
    public function accept($id)
    {
        // Aceitar ocorrência: status → "Aceita"
        // Disparar evento OccurrenceAccepted
    }
    
    public function reject($id)
    {
        // Recusar ocorrência: status → "Recusada"
        // Disparar evento OccurrenceRejected
    }
    
    public function updateStatus(Request $request, $id)
    {
        // Atualizar status (em_deslocamento, em_atendimento, finalizada)
        // Validar que a ocorrência pertence ao motorista
    }
}
```

### 4.3 OccurrenceService (atualizar)

```php
// app/Services/OccurrenceService.php
public function acceptOccurrence(int $occurrenceId, int $driverId): Occurrences
{
    $occurrence = Occurrences::findOrFail($occurrenceId);
    
    if ($occurrence->driver_id !== $driverId) {
        throw new \Exception('Ocorrência não atribuída a este motorista');
    }
    
    $occurrence->status_occurrences_id = StatusOccurrence::where('name', 'Aceita')->first()->id;
    $occurrence->save();
    
    event(new OccurrenceAccepted($occurrence));
    
    return $occurrence;
}
```

---

## 5. Views e Layout

### 5.1 Layout específico do motorista

```php
// resources/views/driver/layouts/app.blade.php
@extends('adminlte::page')
@section('title', 'Painel do Motorista')

@section('content_header')
    <h1>Painel do Motorista</h1>
@stop

@section('content')
    @yield('driver_content')
@stop

@section('sidebar')
    {{-- Menu reduzido: Ocorrências, Perfil, Sair --}}
@stop
```

### 5.2 Dashboard do motorista

```php
// resources/views/driver/pages/dashboard/index.blade.php
- Cards: Ocorrências aguardando aceitação, Em andamento, Finalizadas hoje
- Lista de ocorrências atribuídas (com filtros: todas, aguardando, aceitas, em andamento)
- Mapa com marcadores das ocorrências atribuídas
```

### 5.3 Lista de ocorrências

```php
// resources/views/driver/pages/occurrences/index.blade.php
- Tabela: Número, Título, Status, Tipo, Data
- Botão "Aceitar" / "Recusar" para ocorrências com status "Aguardando aceitação"
- Link para detalhes
```

### 5.4 Detalhes da ocorrência

```php
// resources/views/driver/pages/occurrences/show.blade.php
- Informações da ocorrência (título, endereço, contato, etc.)
- Mapa com localização
- Timeline (criação, atribuição, aceitação, mudanças de status)
- Botões de ação: Aceitar/Recusar (se aguardando), Iniciar deslocamento, Cheguei ao local, Finalizar atendimento
```

---

## 6. Rotas

### 6.1 Grupo de rotas do motorista

```php
// routes/web.php
Route::middleware(['auth', 'ensure.driver'])->prefix('driver')->name('driver.')->group(function() {
    Route::get('/dashboard', [DriverDashboardController::class, 'index'])->name('dashboard');
    
    Route::prefix('occurrences')->name('occurrences.')->group(function() {
        Route::get('/', [DriverOccurrenceController::class, 'index'])->name('index');
        Route::get('/{id}', [DriverOccurrenceController::class, 'show'])->name('show');
        Route::post('/{id}/accept', [DriverOccurrenceController::class, 'accept'])->name('accept');
        Route::post('/{id}/reject', [DriverOccurrenceController::class, 'reject'])->name('reject');
        Route::put('/{id}/status', [DriverOccurrenceController::class, 'updateStatus'])->name('update-status');
    });
    
    Route::get('/profile', [DriverProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [DriverProfileController::class, 'update'])->name('profile.update');
});
```

---

## 7. Eventos e Broadcasting

### 7.1 Eventos

```php
// app/Events/OccurrenceAccepted.php
class OccurrenceAccepted implements ShouldBroadcast
{
    public function __construct(public Occurrences $occurrence) {}
    
    public function broadcastOn()
    {
        return new PrivateChannel('tenant.' . $this->occurrence->tenant_id);
    }
}

// app/Events/OccurrenceRejected.php
// app/Events/OccurrenceStatusUpdated.php (quando motorista atualiza status)
```

### 7.2 Listener no admin

```php
// app/Listeners/UpdateAdminDashboardOnOccurrenceAccepted.php
// Atualizar dashboard admin em tempo real quando motorista aceita/recusa/atualiza status
```

---

## 8. Acompanhamento pelo painel admin

### 8.1 Dashboard admin atualizado

- **Card "Ocorrências aguardando aceitação"**: Contador + link para lista filtrada
- **Card "Ocorrências em atendimento"**: Contador + link para lista filtrada
- **Mapa**: Marcadores diferenciados por status (aguardando = amarelo, aceita = azul, em atendimento = verde, finalizada = cinza)
- **Timeline na tela de detalhe**: Mostrar quando motorista aceitou, iniciou deslocamento, chegou, finalizou

### 8.2 Listagem admin com filtros

- Filtro por status (incluindo "Aguardando aceitação", "Aceita", "Recusada")
- Filtro por motorista
- Coluna "Motorista" com badge de status (aguardando aceitação, aceita, etc.)

---

## 9. Notificações

### 9.1 Notificação ao motorista

```php
// app/Notifications/OccurrenceAssignedToDriver.php
// Quando admin atribui ocorrência ao motorista
// Enviar e-mail: "Nova ocorrência atribuída: [título]"
```

### 9.2 Notificação ao admin

```php
// app/Notifications/OccurrenceAcceptedByDriver.php
// Quando motorista aceita ocorrência
// Enviar e-mail: "Motorista [nome] aceitou a ocorrência [número]"
```

---

## 10. Ordem de implementação

| Etapa | Tarefas | Resultado |
|-------|---------|-----------|
| **1. Dados** | Migração `user_id` em drivers; seeders de novos status; relacionamento User ↔ Driver | Base de dados pronta |
| **2. Autenticação** | Middleware `EnsureUserIsDriver`; perfil/permissões "Motorista"; scope `forDriver` | Controle de acesso |
| **3. Controllers/Services** | `DriverDashboardController`, `DriverOccurrenceController`; métodos `accept`, `reject`, `updateStatus` | Lógica de negócio |
| **4. Views** | Layout motorista; dashboard; lista; detalhes com ações | Interface do motorista |
| **5. Rotas** | Grupo `/driver/*` com middleware | Rotas funcionais |
| **6. Eventos** | `OccurrenceAccepted`, `OccurrenceRejected`; Broadcasting | Tempo real |
| **7. Admin** | Atualizar dashboard admin; filtros; timeline | Acompanhamento |
| **8. Notificações** | E-mail ao motorista (atribuição) e ao admin (aceitação) | Comunicação |

---

## 11. Considerações técnicas

### 11.1 Segurança

- **Validação de propriedade**: Motorista só pode aceitar/atualizar ocorrências atribuídas a ele
- **Escopo por tenant**: Motorista só vê ocorrências do seu tenant
- **Rate limiting**: Limitar ações de aceitar/recusar (evitar spam)

### 11.2 Performance

- **Eager loading**: Carregar relacionamentos (`statusOccurence`, `typeOccurrence`, `driver`) nas listagens
- **Cache**: Cachear contadores do dashboard (ocorrências aguardando, etc.) com TTL curto (ex.: 30s)

### 11.3 UX

- **Feedback visual**: Badges claros para status (aguardando = amarelo, aceita = verde, recusada = vermelho)
- **Confirmação**: Modal de confirmação ao recusar ocorrência (com campo opcional de motivo)
- **Mobile-first**: Layout responsivo para motorista usar no celular

---

## 12. Próximos passos (fase posterior)

- **App mobile nativo** para motorista (React Native / Flutter)
- **Rastreamento GPS** em tempo real do motorista (quando em deslocamento)
- **Chat** entre admin e motorista na ocorrência
- **Assinatura digital** do cidadão ao finalizar atendimento
- **Relatórios** de desempenho do motorista (tempo médio, taxa de aceitação, etc.)

---

Este documento serve como guia técnico para implementação da área do motorista. Cada seção pode ser detalhada em tarefas específicas no backlog do projeto.
