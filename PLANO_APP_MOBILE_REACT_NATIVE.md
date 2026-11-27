# Plano Arquitetural e de Implementação — Aplicativo Mobile React Native (CIOP / flySOP)

Este documento estabelece o plano técnico completo para o desenvolvimento do aplicativo móvel em **React Native** integrado ao ecossistema **CIOP (Central Inteligente de Ocorrências Públicas)** / **flySOP**.

O aplicativo tem como objetivo permitir que o cidadão registre ocorrências públicas georreferenciadas com fotos/evidências, acompanhe o atendimento dos chamados e responda a pesquisas de opinião pública municipal.

---

## 1. Diagnóstico do Projeto Web (Laravel)

### 1.1. Estado Atual das Camadas e Domínios
- **Ocorrências (`Occurrence`)**: Segue o padrão de *camadas completas* (`Controller -> FormRequest -> Service -> Repository -> Model`). Possui controle de protocolos (`OC-YYYY-NNNNN`), SLA dinâmico por prioridade/tipo (`due_at`), histórico sequencial de status (`occurrence_status_history`), detecção geográfica de duplicidades em 300m/48h e anexos com metadados (`occurrences_imagens`).
- **Autenticação do Cidadão (`Client` / `Auth`)**: Login leve via celular e nome (`POST /api/auth/citizen`) emitindo token Sanctum.
- **Pesquisas (`Survey`)**: Suporte a pesquisas ativas (`GET /api/surveys`), perguntas com tipos dinâmicos (`text`, `single_choice`, `scale 1-5`) e submissão atômica de respostas (`POST /api/surveys/{token}/responses`).

### 1.2. Adaptações Necessárias no Backend
1. **Desacoplamento do Órgão Público (`issuings_id`)**: Tornar `issuings_id` opcional no request da API pública, atribuindo automaticamente o órgão padrão do tenant no `OccurrenceService`.
2. **Auto-atribuição de Cidadão Autenticado**: Resolver `$data['clients_id'] = auth('sanctum')->id()` quando a requisição contiver Bearer Token, impedindo manipulação de ID.
3. **Validação de Coordenadas e Endereço**: Incluir validações de `latitude`, `longitude`, `address`, `neighborhood`, `city`, `state`, `zip` e `description` no `StoreUpdateOccurrences`.
4. **Endpoint Semântico `GET /api/my-occurrences`**: Listagem paginada e segura das ocorrências do usuário logado.
5. **Evolução do Schema de Usuários (`clients`)**:
   - Campos para Perfil Público: `username`, `avatar_url`, `bio`, `website`.
   - Campos para Sign-up e Social Auth: `google_id`, `apple_id`, `email_verified_at`, `marketing_opt_in`, `terms_accepted_at`.
6. **Endpoints de Perfil e Cadastro**:
   - `GET /api/profile/{username}` (Público, via `PublicProfileResource` sem vazar CPF/telefone).
   - `PUT /api/profile` (Atualização de perfil).
   - `POST /api/profile/avatar` (Upload de avatar com validação).
   - `POST /api/auth/register` (Cadastro com validação de força de senha).
   - `POST /api/auth/social` (Login em 1 toque Google/Apple).

---

## 2. Checklists de Componentes Especializados do App Mobile

### 🚀 2.1. Splash Screen Checklist
*Primeira tela exibida no lançamento do aplicativo enquanto ele inicializa serviços essenciais antes de transicionar para a interface principal:*

- [ ] **Logo or wordmark** — Marca oficial da aplicação centralizada sobre fundo limpo e sem poluição visual.
- [ ] **Brand background** — Cor de fundo sólida ou sutilmente tematizada, garantindo transição natural a partir da tela inicial do sistema operacional (Android/iOS).
- [ ] **Launch duration** — A Splash permanece visível estritamente pelo tempo necessário para inicialização técnica (leitura do token de sessão no SecureStore, carregamento de fontes e checagem de onboarding). Nunca usada como padding decorativo ou delay artificial.
- [ ] **Transition to first screen** — Transição suave e intencional via fade-out (`opacity: 1 -> 0`), evitando cortes bruscos (*hard cuts*) ou piscamentos na tela.
- [ ] **No interactive elements** — A tela de Splash é 100% livre de botões, inputs ou áreas clicáveis; tem função puramente transitória.
- [ ] **Loading indicator** — Indicador sutil de carregamento (animação suave ou pulso da marca) ativado automaticamente se a inicialização ultrapassar 1 segundo, informando ao usuário que o app está em execução.

---

### 👋 2.2. Onboarding Checklist
*Experiência de primeira execução (First-run Experience) que orienta o cidadão, coleta informações básicas e entrega o valor central do app:*

- [ ] **Steps** — Fluxo enxuto limitado a **3 etapas essenciais**, evitando sobrecarga cognitiva:
  1. *Valor e Propósito*: Como reportar problemas urbanos da sua rua em segundos.
  2. *Localização Contextual*: Explicação de que a posição GPS serve para apontar o problema no mapa com exatidão.
  3. *Personalização Inicial*: Nome do cidadão e bairro de interesse (opcionais).
  - 💡 *Tudo o que pode ser configurado depois sem impedir o uso imediato do app foi postergado para fora do onboarding.*
- [ ] **Progress indicator** — Indicador visual de progresso (pontos/dots ou barra horizontal `Passo X de 3`), mostrando claramente quantas etapas restam.
- [ ] **Step navigation** — Mecanismo intuitivo de avanço via botão "Continuar / Próximo" e gesto de deslize horizontal (*swipe*).
- [ ] **Contextual permissions** — Permissão de GPS solicitada estritamente na etapa que aborda o mapa e localização, nunca agrupada indiscriminadamente no início.
- [ ] **Skip option** — Botão visível de "Pular" no canto superior direito, permitindo explorar o aplicativo imediatamente (o cadastro pode ser completado posteriormente ao abrir a primeira ocorrência).
- [ ] **Personalisation step** — Coleta rápida de 1 ou 2 escolhas que tornam o app imediatamente relevante (nome e seleção do bairro/região da cidade).
- [ ] **Keyboard handling** — Ajuste fluido de tela (`KeyboardAvoidingView`), teclado numérico para telefone (`keyboardType="phone-pad"`), botão de ação do teclado avançando para o próximo campo (`returnKeyType="next"`).

---

### 📝 2.3. Sign up Checklist
*Fluxo de registro de baixo atrito que coleta o estritamente necessário e gera uma primeira impressão positiva:*

- [ ] **Third party sign-up** — Criação de conta em um toque via **Apple** (`expo-apple-authentication`) e **Google** (`@react-native-google-signin/google-signin`), dispensando definição manual de senha.
- [ ] **Required fields** — Mínimo absoluto de informações para cadastro tradicional: Nome, E-mail e Senha (com telefone opcional/integrado).
  - 💡 *Cada campo adicional reduz a taxa de conclusão do cadastro.*
- [ ] **Password strength** — Feedback visual em tempo real da força da senha enquanto o usuário digita (barra de força + critérios inline: mínimo 8 caracteres, letra maiúscula, número).
  - 💡 *Exibir requisitos em tempo real evita que o usuário descubra restrições apenas no erro de submissão.*
- [ ] **Terms and privacy** — Caixa de seleção/reconhecimento explícito de concordância com os Termos de Serviço e Política de Privacidade (LGPD) antes de criar a conta.
- [ ] **Email verification** — Etapa de confirmação de e-mail disparada pós-cadastro, **sem bloquear o acesso inicial** ao aplicativo (o cidadão explora o app normalmente e um banner sutil no topo lembra de confirmar o e-mail).
  - 💡 *Permitir acesso prévio evita abandono de novos usuários.*
- [ ] **Welcome state** — Tela de boas-vindas exibida imediatamente após a criação da conta, confirmando o sucesso e apontando para a primeira ação (*"Criar Primeira Ocorrência"* ou *"Explorar o Mapa"*).
- [ ] **Already have an account** — Link destacado *"Já tem uma conta? Conectar-se"* para redirecionamento imediato caso o usuário já seja cadastrado.
- [ ] **Marketing opt-in** — Consentimento separado e opcional para recebimento de informativos e novidades da prefeitura (desmarcado por padrão em conformidade com a LGPD).

---

### 🧭 2.4. Tab Bar Navigation Checklist
*Barra de navegação inferior persistente que dá acesso às seções de nível superior do aplicativo:*

- [ ] **Tab count** — Limitado aos destinos mais importantes — exatamente **4 a 5 abas** para manter legibilidade e alcance ergonômico:
  1. 🗺️ **Mapa** (Exploração de ocorrências ao redor)
  2. 📋 **Minhas Ocorrências** (Histórico e acompanhamento)
  3. ➕ **Nova** (Botão central em destaque para abertura rápida de chamado)
  4. 📊 **Pesquisas** (Pesquisas públicas e enquetes do município)
  5. 👤 **Perfil** (Identidade pública e configurações)
- [ ] **Icon and label** — Cada aba emparelhada com um ícone representativo e um rótulo textual legível em português.
- [ ] **Active and default states** — Estado ativo visualmente contrastante via preenchimento de ícone sólido (`solid`), cor primária do tema e rótulo em negrito; estado inativo em linha de contorno (`outline`) com cor neutra atenuada.
- [ ] **Badge counts** — Badges atualizados em tempo real:
  - Na aba **Minhas Ocorrências**: contador numérico ou ponto indicador (*dot*) para notificações de atualização de status.
  - Na aba **Pesquisas**: ponto indicador (*dot*) indicando novas pesquisas disponíveis para resposta.
- [ ] **Fixed presence** — A Tab Bar permanece visível e fixa nas telas principais das 5 seções; é automaticamente ocultada em fluxos profundos (ex: Visor da Câmera em tela cheia, formulário de perguntas de pesquisa e modal de fotos).
- [ ] **Tap target size** — Cada item da barra possui área de toque mínima de **44×44 pt** (`minHeight: 48, minWidth: 48`), garantindo acionamento confortável pelo polegar em qualquer pegada do aparelho.
- [ ] **Haptic feedback** — Retorno tátil sutil de impacto leve (`expo-haptics`: `Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light)`) ao alternar de aba, confirmando a ação ao usuário.

---

### 🗺️ 2.5. Map View Checklist
*Tela nativa de mapa com conteúdo geoespacial, posição do usuário e sobreposições contextuais:*

- [ ] **Pin and marker designs** — Marcadores customizados distinguíveis entre si e do mapa base; área de toque mínima de `44x44 dp` para cliques precisos em telas touch.
  - 💡 *O marcador da posição atual do usuário é o elemento mais destacado visualmente (com anel pulsante), sendo o centro das interações espaciais.*
- [ ] **Marker clustering** — Agrupamento automático de marcadores próximos (`react-native-map-clustering`) para evitar sobreposição em áreas com alta densidade de ocorrências.
- [ ] **Native map components** — Uso de componentes nativos via `react-native-maps`: **MapKit** no iOS e **Google Maps** no Android, garantindo aceleração gráfica de hardware.
- [ ] **Location permission** — Solicitação de permissão de GPS no momento oportuno com diálogo explicativo prévio.
  - 💡 *Em caso de recusa, exibe banner informativo com botão direto para "Abrir Configurações do Sistema" (`Linking.openSettings()`).*
- [ ] **Current location re-centre** — Botão flutuante para recentralizar o mapa instantaneamente na posição atual do cidadão (`animateToRegion`).
- [ ] **Selected item bottom sheet** — O toque em um marcador abre um modal inferior expansível (**Bottom Sheet** via `@gorhom/bottom-sheet`) com resumo da ocorrência (protocolo, tipo, status, foto e botão de detalhes), sem sair do mapa.
- [ ] **Search or filter actions** — Filtros por tipo/status em overlay flutuante.
  - 💡 *Filtros ativos permanecem visíveis em chips horizontais no topo do mapa.*
- [ ] **Offline handling** — Exibição dos blocos de mapa já em cache quando sem conexão, com indicador visual de aviso (*"Modo Offline: dados podem não estar atualizados"*).

---

### 👤 2.6. Profile Checklist
*Identidade pública do cidadão (como outros usuários e a comunidade o visualizam na plataforma), separada das configurações privadas da conta:*

- [ ] **Profile photo** — Opção de definir foto de avatar a partir da galeria ou capturando uma nova foto pela câmera.
  - 💡 *Disponibiliza ambas as opções e inclui etapa de corte circular 1:1 (`allowsEditing: true, aspect: [1, 1]`) para garantir enquadramento adequado do avatar.*
- [ ] **Display name and/or username** — Nome de exibição público e handle único (`@username`) para identificação e menções.
  - 💡 *Interface deixa explícito qual é o nome visível a todos e qual é o identificador único (`@handle`).*
- [ ] **Bio** — Campo de texto curto para descrição pessoal ou comunitária.
  - 💡 *Contador dinâmico de caracteres regressivo (limite de 160 caracteres) para evitar cortes abruptos.*
- [ ] **Public fields only** — A tela contém **estritamente** dados públicos visíveis a outros usuários. Dados sensíveis (CPF, RG, e-mail, telefone, termos de LGPD) ficam restritos à tela de *Configurações de Conta*.
- [ ] **Profile preview** — Visualização somente-leitura prévia de como o perfil aparecerá para o público antes de salvar as alterações.
- [ ] **External links** — Campos opcionais para links externos (site pessoal, portfólio ou redes sociais).

---

### 📷 2.7. Camera Checklist
*Captura de evidências fotográficas para ocorrências e fotos de perfil:*

- [ ] **Permission request** — Permissão de câmera solicitada estritamente no momento do disparo da captura (nunca na inicialização ou no onboarding do app).
  - 💡 *Solicitar permissão sem contexto imediato quebra a confiança do usuário.*
- [ ] **Viewfinder and capture button** — Viewfinder em tela cheia com botão de captura grande, posicionado na parte inferior para alcance confortável pelo polegar.
- [ ] **Flash controls** — Seletor de flash acessível diretamente na tela de captura (ciclando entre Auto, Ligado e Desligado com ícones claros).
- [ ] **Camera flip** — Botão de inversão de câmera (frontal/traseira) posicionado de modo a evitar toques acidentais durante o clique.
- [ ] **Post-capture preview** — Tela de revisão imediata pós-captura, permitindo ao usuário "Repetir Foto" ou "Confirmar e Usar".
- [ ] **Gallery picker of captures** — Carrossel/rolo inferior exibindo em miniatura apenas as fotos capturadas durante a sessão atual antes de sair da câmera.

---

### 📄 2.8. Action Sheet Checklist
*Modal deslizante inferior (*slide-up*) para opções e confirmações contextuais:*

- [ ] **Heading and actions** — Cabeçalho conciso e contextual indicando o propósito da folha, seguido pelas ações selecionáveis.
- [ ] **Swipe or backdrop dismiss** — Fechamento rápido arrastando o modal para baixo (*drag handle* superior) ou tocando na área escura de fundo (*backdrop*).
- [ ] **Destructive action styling** — Qualquer ação destrutiva (*Excluir rascunho*, *Remover foto*, *Cancelar ocorrência*) estilizada em vermelho (`#E53E3E`) e posicionada por último, isolada das ações seguras.
  - 💡 *Usuários leem de cima para baixo; isolar a ação destrutiva no fim evita toques acidentais.*
- [ ] **Cancel action** — Botão *"Cancelar"* destacado e separado na base do modal para dispensar a ação sem nenhum efeito colateral.
- [ ] **Snap points** — Pontos de parada pré-definidos (`['35%', '60%']`) via `@gorhom/bottom-sheet`, evitando alturas livres ou saltos visuais.
- [ ] **Content scrollability** — Cabeçalho fixo com rolagem restrita ao corpo do conteúdo apenas quando estritamente inevitável.
- [ ] **Keyboard relation** — Redimensionamento automático caso um campo de entrada seja acionado dentro do sheet (`keyboardBlurBehavior: 'restore'`).
- [ ] **Backdrop dimming** — Fundo escurecido e desfocado com opacidade suave (`0.5`), focando a atenção no Action Sheet.

---

## 3. Arquitetura da Aplicação React Native

### 3.1. Stack Tecnológica
- **Plataforma / Build**: React Native 0.74+ com **Expo SDK 51** (Bare workflow / prebuild).
- **Linguagem**: TypeScript estrito (`strict: true`).
- **Navegação**: React Navigation v6 (`@react-navigation/native`, `stack`, `bottom-tabs`).
- **Mapas e GPS**: `expo-location` + `react-native-maps` + `react-native-map-clustering`.
- **Câmera e Anexos**: `expo-camera` + `expo-image-picker` + `react-native-compressor`.
- **Armazenamento e Rede**: Axios + `@tanstack/react-query` + `expo-secure-store` + `@react-native-async-storage/async-storage`.
- **Retorno Tátil**: `expo-haptics`.
- **Modais e Sheets**: `@gorhom/bottom-sheet`.

### 3.2. Estrutura de Diretórios (`mobile/`)
```
mobile/
├── assets/                  # Ícones, splash screens, ilustrações
├── src/
│   ├── api/                 # Axios client, interceptors de Bearer token e endpoints
│   ├── components/          # ActionSheet, MapPicker, PhotoPicker, Camera, Badges
│   ├── context/             # AuthContext, OfflineQueueContext
│   ├── hooks/               # useLocation, useCameraControls, useOccurrences, useSurveys
│   ├── navigation/          # AppNavigator, TabNavigator
│   ├── screens/
│   │   ├── Splash/          # SplashScreen com transição suave
│   │   ├── Onboarding/      # 3 passos, contextual permissions, skip
│   │   ├── Auth/            # Login leve, SignUpScreen, SocialAuth
│   │   ├── Occurrences/     # MapScreen, NewOccurrenceScreen, MyOccurrencesScreen, Details
│   │   ├── Surveys/         # SurveyListScreen, SurveyDetailScreen (widgets dinâmicos)
│   │   └── Profile/         # EditProfileScreen, PublicProfilePreviewModal
│   ├── services/            # offlineQueue, secureStore, geolocation
│   ├── types/               # Modelos TypeScript de API e estado
│   └── utils/               # Máscaras de telefone, validadores, formatadores
├── __tests__/               # Testes unitários e de integração (Jest + RNTL)
├── App.tsx
├── package.json
└── tsconfig.json
```

---

## 4. Plano de Execução Faseado com Checklists e Testes

```
┌────────────────────────────────────────────────────────────────────────┐
│                        Matriz de Qualidade                             │
│                                                                        │
│  [Atividade] ──> [Checklist de Validação] ──> [Testes Unitários]       │
│                                           ──> [Testes de Integração]   │
│                                           ──> [Portão Green 100%]     │
└────────────────────────────────────────────────────────────────────────┘
```

### Fase 1: Backend Laravel (APIs, Schemas e Ocorrências)

#### Atividade 1.1: Endpoints de Cadastro Completo e Social Auth
- **Arquivos**: `database/migrations/xxxx_add_enhanced_auth_and_public_profile_to_clients_table.php`, `app/Http/Controllers/Api/Auth/CitizenRegisterController.php`, `app/Http/Requests/Api/StoreCitizenRegister.php`.
- **Checklist de Validação**:
  - [ ] Senha < 8 caracteres ou sem letras/números rejeitada com status `422`.
  - [ ] Cadastro sem aceite de termos/LGPD rejeitado com status `422`.
  - [ ] Emissão de token Sanctum válido após cadastro.
  - [ ] Social auth vincula `google_id`/`apple_id` sem senha.
- **Testes Obrigatórios**:
  - `tests/Feature/Api/CitizenRegisterApiTest.php` (happy path, senhas fracas, social auth).

#### Atividade 1.2: Validação de Coordenadas, Endereço e Órgão Default
- **Arquivos**: `app/Http/Requests/Api/StoreUpdateOccurrences.php`, `app/Http/Controllers/Api/OccurrenceApiController.php`, `app/Services/OccurrenceService.php`.
- **Checklist de Validação**:
  - [ ] Latitude fora de `-90` a `90` ou longitude fora de `-180` a `180` rejeitada (`422`).
  - [ ] `issuings_id` omitido adota o órgão default do tenant sem erro de banco.
  - [ ] Usuário logado tem `clients_id` resolvido via `auth('sanctum')->id()`.
- **Testes Obrigatórios**:
  - `tests/Feature/Api/OccurrenceValidationTest.php` e `OccurrenceCreationApiTest.php`.

#### Atividade 1.3: Endpoint Semântico `GET /api/my-occurrences`
- **Arquivos**: `routes/api.php`, `app/Http/Controllers/Api/OccurrenceApiController.php`.
- **Checklist de Validação**:
  - [ ] Acesso sem token retorna `401 Unauthorized`.
  - [ ] Retorna exclusivamente ocorrências do cliente autenticado com paginação e fotos.
- **Testes Obrigatórios**:
  - `tests/Feature/Api/MyOccurrencesApiTest.php`.

#### Atividade 1.4: Módulo de Perfil Público com Isolamento LGPD
- **Arquivos**: `app/Http/Controllers/Api/ProfileApiController.php`, `app/Http/Resources/PublicProfileResource.php`, `app/Http/Requests/Api/UpdateProfileRequest.php`.
- **Checklist de Validação**:
  - [ ] `GET /api/profile/{username}` não expõe CPF, e-mail ou telefone.
  - [ ] `@username` duplicado rejeitado com erro `422`.
  - [ ] Bio com mais de 160 caracteres rejeitada na validação.
  - [ ] Upload de avatar aceita apenas imagens até 2MB.
- **Testes Obrigatórios**:
  - `tests/Feature/Api/ProfileApiTest.php`.

---

### Fase 2: Fundação do App React Native, Navegação e Telas Base

#### Atividade 2.1: Splash Screen Nativa e Transição
- **Arquivos**: `app.json`, `src/screens/Splash/SplashScreen.tsx`, `src/hooks/useAppInitialization.ts`.
- **Checklist de Validação**:
  - [ ] Splash não contém botões ou elementos interativos.
  - [ ] Fade-out suave sem corte brusco.
  - [ ] Inicialização acima de 1s aciona indicador visual.
- **Testes Obrigatórios**:
  - `src/__tests__/unit/useAppInitialization.test.ts`.

#### Atividade 2.2: Fluxo de Onboarding Inicial
- **Arquivos**: `src/screens/Onboarding/OnboardingScreen.tsx`.
- **Checklist de Validação**:
  - [ ] Exibe exatamente 3 etapas com indicador de progresso.
  - [ ] Permissão de GPS solicitada estritamente na etapa 2.
  - [ ] Botão "Pular" encerra o fluxo e navega para a Home salvando flag no storage.
- **Testes Obrigatórios**:
  - `src/__tests__/integration/OnboardingScreen.test.tsx` (RNTL).

#### Atividade 2.3: Tela de Cadastro de Baixo Atrito (Sign up Screen)
- **Arquivos**: `src/screens/Auth/SignUpScreen.tsx`, `src/components/PasswordStrengthMeter.tsx`.
- **Checklist de Validação**:
  - [ ] Medidor de força de senha atualiza em tempo real.
  - [ ] Botão desabilitado até concordância com termos/LGPD.
  - [ ] Cadastro direciona para tela de Boas-Vindas sem travar em confirmação de e-mail.
- **Testes Obrigatórios**:
  - `src/__tests__/integration/SignUpScreen.test.tsx` (RNTL).

#### Atividade 2.4: Componente Reutilizável de Action Sheet
- **Arquivos**: `src/components/ActionSheet/ActionSheetModal.tsx`.
- **Checklist de Validação**:
  - [ ] Fechamento por arraste inferior ou toque no backdrop.
  - [ ] Ação destrutiva renderizada em vermelho na última posição.
  - [ ] Botão Cancelar fecha o modal sem ações colaterais.
- **Testes Obrigatórios**:
  - `src/__tests__/integration/ActionSheetModal.test.tsx` (RNTL).

#### Atividade 2.5: Navegação Inferior Persistente (Tab Bar Navigation)
- **Arquivos**: `src/navigation/TabNavigator.tsx`.
- **Checklist de Validação**:
  - [ ] 5 abas acessíveis com área mínima de 44 pt.
  - [ ] Toque dispara haptic feedback (`Haptics.impactAsync`).
  - [ ] Badges atualizados com notificações de status de ocorrências.
  - [ ] Ocultação automática em telas de câmera e questionários profundos.
- **Testes Obrigatórios**:
  - `src/__tests__/integration/TabNavigator.test.tsx` (RNTL).

#### Atividade 2.6: Tela de Perfil Público e Preview (Profile Screen)
- **Arquivos**: `src/screens/Profile/EditProfileScreen.tsx`, `src/screens/Profile/PublicProfilePreviewModal.tsx`.
- **Checklist de Validação**:
  - [ ] Foto de avatar oferece Câmera e Galeria com recorte circular 1:1.
  - [ ] Contador de bio decrementa em tempo real e trava em 160 caracteres.
  - [ ] Botão "Pré-visualizar Perfil" abre modal somente-leitura fiel.
  - [ ] Campos privados (CPF, telefone) ausentes da interface pública.
- **Testes Obrigatórios**:
  - `src/__tests__/integration/EditProfileScreen.test.tsx` (RNTL).

---

### Fase 3: Módulo de Câmera Nativa e Ocorrências com GPS

#### Atividade 3.1: Componente de Câmera em Tela Cheia (Custom Camera)
- **Arquivos**: `src/components/Camera/AppCameraModal.tsx`, `src/hooks/useCameraControls.ts`.
- **Checklist de Validação**:
  - [ ] Permissão solicitada apenas no momento do disparo.
  - [ ] Botão de captura grande posicionado na base.
  - [ ] Alternância de flash no próprio visor.
  - [ ] Tela de prévia permite Repetir Foto ou Confirmar.
- **Testes Obrigatórios**:
  - `src/__tests__/integration/AppCameraModal.test.tsx` (RNTL).

#### Atividade 3.2: Tela de Mapa Interativo (Map View Screen)
- **Arquivos**: `src/components/Map/MapPicker.tsx`, `src/screens/Occurrences/MapScreen.tsx`.
- **Checklist de Validação**:
  - [ ] Marcador do usuário em destaque pulsante.
  - [ ] Agrupamento por cluster ativado em zoom aberto.
  - [ ] Toque no marcador abre o Bottom Sheet sem sair do mapa.
  - [ ] Botão "Recentralizar" anima para a posição GPS do cidadão.
  - [ ] Sem conexão, exibe blocos em cache e banner de aviso.
- **Testes Obrigatórios**:
  - `src/__tests__/integration/MapScreen.test.tsx` (RNTL).

#### Atividade 3.3: Formulário e Envio da Ocorrência
- **Arquivos**: `src/screens/Occurrences/NewOccurrenceScreen.tsx`.
- **Checklist de Validação**:
  - [ ] Envio multipart contendo imagem compactada e coordenadas.
  - [ ] Tela de sucesso renderiza o protocolo retornado (`OC-YYYY-NNNNN`).
- **Testes Obrigatórios**:
  - `src/__tests__/integration/NewOccurrenceScreen.test.tsx` (RNTL).

---

### Fase 4: Módulo de Pesquisas Públicas (Surveys)

#### Atividade 4.1: Renderização Dinâmica e Submissão
- **Arquivos**: `src/screens/Surveys/SurveyDetailScreen.tsx`, `src/components/SurveyWidgets/*`.
- **Checklist de Validação**:
  - [ ] Perguntas renderizadas na ordem estrita de `sort_order`.
  - [ ] Botão de envio desabilitado enquanto houver pergunta obrigatória em branco.
  - [ ] Submissão bloqueia cliques múltiplos durante o loading.
- **Testes Obrigatórios**:
  - `src/__tests__/integration/SurveyDetailScreen.test.tsx` (RNTL).

---

### Fase 5: Acompanhamento e Linha do Tempo (Minhas Ocorrências)

#### Atividade 5.1: Lista e Linha do Tempo
- **Arquivos**: `src/screens/Occurrences/MyOccurrencesScreen.tsx`, `src/screens/Occurrences/OccurrenceDetailScreen.tsx`.
- **Checklist de Validação**:
  - [ ] Cards exibem protocolo, status colorizado e miniatura.
  - [ ] Detalhes exibem linha do tempo de status e fotos de antes/depois.
- **Testes Obrigatórios**:
  - `src/__tests__/integration/MyOccurrencesScreen.test.tsx` (RNTL).

---

### Fase 6: Resiliência Offline e Sincronização em Segundo Plano

#### Atividade 6.1: Fila Offline de Ocorrências
- **Arquivos**: `src/services/offlineQueue.ts`, `src/context/OfflineContext.tsx`.
- **Checklist de Validação**:
  - [ ] Ocorrência criada offline permanece salva localmente com fotos.
  - [ ] Retorno da rede processa a fila automaticamente e limpa o armazenamento local após confirmação de sucesso (HTTP 201).
- **Testes Obrigatórios**:
  - `src/__tests__/unit/offlineQueue.test.ts`.

---

## 5. Portão de Aceitação Contínua (Definition of Done)

Para qualquer atividade ser considerada concluída:
1. Todos os itens da respectiva checklist de validação e checklists especializadas (**Splash Screen**, **Onboarding**, **Sign up**, **Tab Bar Navigation**, **Map View**, **Profile**, **Camera** e **Action Sheet**) devem estar cumpridos.
2. A suíte de testes automatizados correspondente (Unitário e Integração) deve executar com status **100% Green** (sem regressões).
3. As diretrizes de arquitetura do flySOP (`docs/specs/*`) e o isolamento de privacidade LGPD devem ser estritamente preservados.

---

## 6. Status Atual de Execução e Evidências

### 6.1. Backend Laravel
- [x] Migration `database/migrations/2026_10_19_100000_add_enhanced_auth_and_public_profile_to_clients_table.php` criada com colunas para perfil público e autenticação social.
- [x] Model `Client` atualizado com novos campos no `$fillable`, `$casts` e relacionamento `occurrences()`.
- [x] `StoreUpdateOccurrences` atualizado com regras de coordenadas, endereço e `issuings_id` opcional.
- [x] `OccurrenceService` com fallback automático de órgão público (`Issuing::query()->value('id') ?? 1`).
- [x] `OccurrenceApiController` atualizado com auto-atribuição de `clients_id = auth('sanctum')->id()` e endpoint `myOccurrences()`.
- [x] `PublicProfileResource` e `ProfileApiController` implementados com isolamento estrito de dados sensíveis LGPD.
- [x] `CitizenRegisterController` implementado com validação de senha forte e Social Auth (Google/Apple).
- [x] Rotas devidamente registradas em `routes/api.php` e validadas via `php artisan route:list`.
- [x] 4 suítes de testes Feature criadas em `tests/Feature/Api/`.
- [x] 100% dos arquivos PHP auditados com 0 erros de sintaxe (`php -l`).

### 6.2. Frontend React Native (`mobile/`)
- [x] Estrutura do projeto inicializada com Expo SDK 51, TypeScript e dependências nativas.
- [x] 🚀 `SplashScreen.tsx` implementado (*Splash Screen Checklist*).
- [x] 👋 `OnboardingScreen.tsx` implementado (*Onboarding Checklist*).
- [x] 📝 `SignUpScreen.tsx` e `PasswordStrengthMeter.tsx` implementados (*Sign up Checklist*).
- [x] 🧭 `TabNavigator.tsx` com 5 abas, área mínima 44 pt, haptics e badges (*Tab Bar Navigation Checklist*).
- [x] 🗺️ `MapScreen.tsx` com MapKit/Google Maps, pino pulsante, Bottom Sheet e recentralização (*Map View Checklist*).
- [x] 👤 `EditProfileScreen.tsx` com crop circular 1:1, contador de bio e preview (*Profile Checklist*).
- [x] 📷 `AppCameraModal.tsx` com visor em tela cheia, botão thumb na base, flash e review (*Camera Checklist*).
- [x] 📄 `ActionSheetModal.tsx` com snap points, arraste e ação destrutiva em vermelho (*Action Sheet Checklist*).
- [x] 📋 `MyOccurrencesScreen.tsx` e `OccurrenceDetailScreen.tsx` com fotos antes/depois e linha do tempo de status.
- [x] ➕ `NewOccurrenceScreen.tsx` com formulário, GPS, geocodificação reversa e termo LGPD.
- [x] 📊 `SurveyListScreen.tsx` e `SurveyDetailScreen.tsx` com renderização dinâmica de perguntas (*text*, *single_choice*, *scale 1-5*).
- [x] 📶 `offlineQueue.ts` com fila em `AsyncStorage` e auto-sincronização via `NetInfo`.
- [x] `mobile/__tests__/unit/`: 3 suítes de testes unitários (`formatters.test.ts`, `offlineQueue.test.ts`, `surveyValidator.test.ts`) executando com status **100% Green** (10 testes aprovados).
- [x] 📦 **Build Nativo Android (APK)**: Compilado com sucesso via Gradle 8.8 (`gradlew assembleDebug`), gerando o arquivo `mobile/android/app/build/outputs/apk/debug/app-debug.apk` (180.5 MB).

---

## 7. Procedimentos de Execução e Testes na Máquina

### 7.1. Opção Imediata: Execução no Navegador (Expo Web)
Configurado suporte com `react-native-web`, `react-dom` e `@expo/metro-runtime` para permitir testes da interface e consumo da API diretamente no computador:
```bash
cd mobile
npx expo start --web
```
- **Endereço**: `http://localhost:8081`
- **Dica**: Ativar o modo de inspeção de dispositivo móvel no Chrome/Edge (`Ctrl + Shift + M`) para renderizar na proporção exata de smartphone.

### 7.2. Opção Nativa: Emulador Android Studio
- **Diagnóstico de Hardware**:
  - Processador: `AMD Ryzen 7 5700G with Radeon Graphics`.
  - Estado de Virtualização: `VirtualizationFirmwareEnabled: False`.
- **Resolução**:
  1. Reiniciar a máquina e entrar na BIOS (`DEL` ou `F2`).
  2. Em `Advanced` > `CPU Configuration`, alterar **SVM Mode** (ou **AMD-V**) para **Enabled**.
  3. Salvar com `F10` e reiniciar o Windows.
  4. Abrir o Android Studio, clicar em **Play (▶)** no `Pixel 8 API 36` no Device Manager.
  5. Com o emulador ligado, instalar o APK gerado:
     ```bash
     adb install -r mobile/android/app/build/outputs/apk/debug/app-debug.apk
     ```
