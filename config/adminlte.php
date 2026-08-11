<?php

return [

    /*
     |--------------------------------------------------------------------------
     | Title
     |--------------------------------------------------------------------------
     |
     | Here you can change the default title of your admin panel.
     |
     | For detailed instructions you can look the title section here:
     | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
     |
     */

    'title' => 'CIOP - Central Inteligente de Ocorrências Públicas',
    'title_prefix' => '',
    'title_postfix' => '',

    /*
     |--------------------------------------------------------------------------
     | Favicon
     |--------------------------------------------------------------------------
     |
     | Here you can activate the favicon.
     |
     | For detailed instructions you can look the favicon section here:
     | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
     |
     */

    'use_ico_only' => false,
    'use_full_favicon' => false,

    /*
     |--------------------------------------------------------------------------
     | Logo
     |--------------------------------------------------------------------------
     |
     | Here you can change the logo of your admin panel.
     |
     | For detailed instructions you can look the logo section here:
     | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
     |
     */

    'logo' => '<b>CIOP</b>',
    'logo_img' => 'images/ciop-mark.svg',
    'logo_img_class' => 'brand-image ciop-brand-mark',
    'logo_img_xl' => null,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt' => 'CIOP - Central Inteligente de Ocorrências Públicas',

    /*
     |--------------------------------------------------------------------------
     | User Menu
     |--------------------------------------------------------------------------
     |
     | Here you can activate and change the user menu.
     |
     | For detailed instructions you can look the user menu section here:
     | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
     |
     */

    'usermenu_enabled' => true,
    'usermenu_header' => false,
    'usermenu_header_class' => 'bg-primary',
    'usermenu_image' => false,
    'usermenu_desc' => false,
    'usermenu_profile_url' => false,

    /*
     |--------------------------------------------------------------------------
     | Layout
     |--------------------------------------------------------------------------
     |
     | Here we change the layout of your admin panel.
     |
     | For detailed instructions you can look the layout section here:
     | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
     |
     */

    'layout_topnav' => null,
    'layout_boxed' => null,
    'layout_fixed_sidebar' => true,
    'layout_fixed_navbar' => true,
    'layout_fixed_footer' => null,

    /*
     |--------------------------------------------------------------------------
     | Authentication Views Classes
     |--------------------------------------------------------------------------
     |
     | Here you can change the look and behavior of the authentication views.
     |
     | For detailed instructions you can look the auth classes section here:
     | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
     |
     */

    'classes_auth_card' => 'ciop-auth-card-inner',
    'classes_auth_header' => '',
    'classes_auth_body' => '',
    'classes_auth_footer' => '',
    'classes_auth_icon' => '',
    'classes_auth_btn' => 'ciop-btn-login',

    /*
     |--------------------------------------------------------------------------
     | Admin Panel Classes
     |--------------------------------------------------------------------------
     |
     | Here you can change the look and behavior of the admin panel.
     |
     | For detailed instructions you can look the admin panel classes here:
     | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
     |
     */

    'classes_body' => 'ciop-panel',
    'classes_brand' => '',
    'classes_brand_text' => '',
    'classes_content_wrapper' => '',
    'classes_content_header' => '',
    'classes_content' => '',
    'classes_sidebar' => 'sidebar-dark-primary elevation-2 ciop-sidebar',
    'classes_sidebar_nav' => '',
    'classes_topnav' => 'navbar-white navbar-light ciop-topnav',
    'classes_topnav_nav' => 'navbar-expand',
    'classes_topnav_container' => 'container',

    /*
     |--------------------------------------------------------------------------
     | Sidebar
     |--------------------------------------------------------------------------
     |
     | Here we can modify the sidebar of the admin panel.
     |
     | For detailed instructions you can look the sidebar section here:
     | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
     |
     */

    'sidebar_mini' => true,
    'sidebar_collapse' => false,
    'sidebar_collapse_auto_size' => false,
    'sidebar_collapse_remember' => false,
    'sidebar_collapse_remember_no_transition' => true,
    'sidebar_scrollbar_theme' => 'os-theme-light',
    'sidebar_scrollbar_auto_hide' => 'l',
    'sidebar_nav_accordion' => true,
    'sidebar_nav_animation_speed' => 300,

    /*
     |--------------------------------------------------------------------------
     | Control Sidebar (Right Sidebar)
     |--------------------------------------------------------------------------
     |
     | Here we can modify the right sidebar aka control sidebar of the admin panel.
     |
     | For detailed instructions you can look the right sidebar section here:
     | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
     |
     */

    'right_sidebar' => false,
    'right_sidebar_icon' => 'fas fa-cogs',
    'right_sidebar_theme' => 'dark',
    'right_sidebar_slide' => true,
    'right_sidebar_push' => true,
    'right_sidebar_scrollbar_theme' => 'os-theme-light',
    'right_sidebar_scrollbar_auto_hide' => 'l',

    /*
     |--------------------------------------------------------------------------
     | URLs
     |--------------------------------------------------------------------------
     |
     | Here we can modify the url settings of the admin panel.
     |
     | For detailed instructions you can look the urls section here:
     | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
     |
     */

    'use_route_url' => false,
    'dashboard_url' => 'admin',
    'logout_url' => 'logout',
    'login_url' => 'login',
    'register_url' => 'register',
    'password_reset_url' => 'password/reset',
    'password_email_url' => 'password/email',
    'profile_url' => false,

    /*
     |--------------------------------------------------------------------------
     | Laravel Mix
     |--------------------------------------------------------------------------
     |
     | Here we can enable the Laravel Mix option for the admin panel.
     |
     | For detailed instructions you can look the laravel mix section here:
     | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
     |
     */

    'enabled_laravel_mix' => false,
    'laravel_mix_css_path' => 'css/app.css',
    'laravel_mix_js_path' => 'js/app.js',

    /*
     |--------------------------------------------------------------------------
     | Menu Items
     |--------------------------------------------------------------------------
     |
     | Here we can modify the sidebar/top navigation of the admin panel.
     |
     | For detailed instructions you can look here:
     | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
     |
     */

    'menu' => [
        // Principal
        [
            'text' => 'Painel de Controle',
            'url' => 'admin/',
            'icon' => 'fas fa-tachometer-alt',
        ],
        [
            'text' => 'Notificações',
            'url' => 'admin/notifications',
            'icon' => 'fas fa-bell',
        ],
        [
            'text' => 'Busca',
            'url' => 'admin/search',
            'icon' => 'fas fa-search',
        ],
        [
            'text' => 'Relatórios',
            'url' => 'admin/reports',
            'icon' => 'fas fa-file-export',
        ],

        // Operação de ocorrências
        ['header' => 'OCORRÊNCIAS'],
        [
            'text' => 'Ocorrências',
            'url' => 'admin/occurrences',
            'icon' => 'fas fa-clipboard-list',
            'can' => 'occurrences',
        ],
        [
            'text' => 'Parâmetros',
            'icon' => 'fas fa-sliders-h',
            'can' => ['statusOccurrences', 'typeOccurrences', 'priorities', 'issuings'],
            'submenu' => [
                [
                    'text' => 'Status',
                    'url' => 'admin/statusOccurrences',
                    'icon' => 'fas fa-flag',
                    'can' => 'statusOccurrences',
                ],
                [
                    'text' => 'Tipos',
                    'url' => 'admin/typeOccurrences',
                    'icon' => 'fas fa-tags',
                    'can' => 'typeOccurrences',
                ],
                [
                    'text' => 'Prioridades',
                    'url' => 'admin/priorities',
                    'icon' => 'fas fa-exclamation-circle',
                    'can' => 'priorities',
                ],
                [
                    'text' => 'Órgãos',
                    'url' => 'admin/issuings',
                    'icon' => 'fas fa-landmark',
                    'can' => 'issuings',
                ],
            ],
        ],

        // Pessoas e estrutura operacional
        ['header' => 'EQUIPES'],
        [
            'text' => 'Motoristas',
            'url' => 'admin/drivers',
            'icon' => 'fas fa-truck',
            'can' => 'drivers',
        ],
        [
            'text' => 'Departamentos',
            'url' => 'admin/departments',
            'icon' => 'fas fa-sitemap',
            'can' => 'departments',
        ],
        [
            'text' => 'Equipes',
            'url' => 'admin/teams',
            'icon' => 'fas fa-people-group',
            'can' => 'teams',
        ],

        // Contas e autorização
        ['header' => 'ACESSO'],
        [
            'text' => 'Usuários',
            'url' => 'admin/users',
            'icon' => 'fas fa-users',
            'can' => 'users',
        ],
        [
            'text' => 'Perfis',
            'url' => 'admin/profiles',
            'icon' => 'fas fa-address-book',
            'can' => 'profiles',
        ],
        [
            'text' => 'Cargos',
            'url' => 'admin/roles',
            'icon' => 'fas fa-address-card',
            'can' => 'roles',
        ],
        [
            'text' => 'Permissões',
            'url' => 'admin/permission',
            'icon' => 'fas fa-lock',
            'can' => 'permissions',
        ],
        [
            'text' => 'Auditoria',
            'url' => 'admin/audit',
            'icon' => 'fas fa-history',
            'can' => 'audit',
        ],

        // Engajamento
        ['header' => 'ENGAJAMENTO'],
        [
            'text' => 'Pesquisas',
            'url' => 'admin/surveys',
            'icon' => 'fas fa-poll',
            'can' => 'surveys',
        ],

        // Configuração do tenant / SaaS
        ['header' => 'SISTEMA'],
        [
            'text' => 'Empresas',
            'url' => 'admin/tenants',
            'icon' => 'fas fa-building',
            'can' => 'tenants',
        ],
        /*[
         'text' => 'Planos',
         'url'  => 'admin/plans',
         'icon' => 'fas fa-list-ul',
         'can'  => 'plans'
         ],*/
        [
            'text' => 'Organização',
            'url' => 'admin/settings/organisation',
            'icon' => 'fas fa-cog',
            'can' => 'settings',
        ],

        // Visão do motorista (mesmo sidebar AdminLTE)
        ['header' => 'ÁREA DO MOTORISTA'],
        [
            'text' => 'Painel Motorista',
            'url' => 'driver/dashboard',
            'icon' => 'fas fa-id-card',
            'can' => 'driver.panel',
        ],
        [
            'text' => 'Minhas Ocorrências',
            'url' => 'driver/occurrences',
            'icon' => 'fas fa-list',
            'can' => 'driver.panel',
        ],
    ],

    /*
     |--------------------------------------------------------------------------
     | Menu Filters
     |--------------------------------------------------------------------------
     |
     | Here we can modify the menu filters of the admin panel.
     |
     | For detailed instructions you can look the menu filters section here:
     | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
     |
     */

    'filters' => [
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class ,
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class ,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter::class ,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class ,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class ,
        JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter::class ,
        JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter::class ,
    ],

    /*
     |--------------------------------------------------------------------------
     | Plugins Initialization
     |--------------------------------------------------------------------------
     |
     | Here we can modify the plugins used inside the admin panel.
     |
     | For detailed instructions you can look the plugins section here:
     | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
     |
     */

    'plugins' => [
        'Datatables' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css',
                ],
            ],
        ],
        'Select2' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.css',
                ],
            ],
        ],
        'Chartjs' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.bundle.min.js',
                ],
            ],
        ],
        'Sweetalert2' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.jsdelivr.net/npm/sweetalert2@8',
                ],
            ],
        ],
        'Pace' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/themes/blue/pace-theme-center-radar.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.min.js',
                ],
            ],
        ],
        'inputmask' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => 'https://cdn.jsdelivr.net/npm/inputmask@5.0.8/dist/jquery.inputmask.min.js',
                ],
            ],
        ],
        'custom' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '/js/custom.js',
                ],
            ],
        ],

    ],

    /*
     |--------------------------------------------------------------------------
     | Livewire
     |--------------------------------------------------------------------------
     |
     | Here we can enable the Livewire support.
     |
     | For detailed instructions you can look the livewire here:
     | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
     */

    'livewire' => false,
];