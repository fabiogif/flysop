<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ACL\PermissionController;
use App\Http\Controllers\Admin\ACL\ProfileController;
use App\Http\Controllers\Admin\ACL\RoleController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\IssuingController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\TableController;
use App\Http\Controllers\Admin\TenantController;
use App\Http\Controllers\Admin\OccurrencesController;
use App\Http\Controllers\Admin\TypeOccurrenceController;
use App\Http\Controllers\Admin\StatusOccurrenceController;
use App\Http\Controllers\Admin\DriverController;
use App\Http\Controllers\Admin\PriorityController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\TeamController;
use App\Http\Controllers\Admin\SurveyController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\PublicSurveyController;
use App\Http\Controllers\Admin\UserInvitationController;
use App\Http\Controllers\Admin\OrganisationSettingsController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Auth\AcceptInvitationController;

use Illuminate\Support\Facades\Auth;

//Auth::routes();

Route::get('/login', [App\Http\Controllers\HomeController::class , 'index'])->name('login');

Route::get('/home', [App\Http\Controllers\HomeController::class , 'index'])->name('home');

Route::get('/invitations/{token}', [AcceptInvitationController::class, 'show'])->name('invitations.accept.show');
Route::post('/invitations/{token}', [AcceptInvitationController::class, 'store'])->name('invitations.accept.store');

Route::prefix('admin')
    ->namespace('')
    ->middleware(['auth', 'admin.panel'])
    ->group(function () {

        //Route::get('/teste', function () {});
    
        //Role X User
        Route::get('/users/{id}/roles/{idRole}/detach', [App\Http\Controllers\Admin\ACL\RoleUserController::class , 'detachRoleUser'])->name('users.role.detach');
        Route::post('/users/{id}/roles', [App\Http\Controllers\Admin\ACL\RoleUserController::class , 'attachRolesUser'])->name('users.roles.attach');
        Route::any('/users/{id}/roles/create', [App\Http\Controllers\Admin\ACL\RoleUserController::class , 'rolesAvailable'])->name('users.roles.available');
        Route::get('/users/{id}/roles', [App\Http\Controllers\Admin\ACL\RoleUserController::class , 'roles'])->name('users.roles');
        Route::get('/roles/{id}/users', [App\Http\Controllers\Admin\ACL\RoleUserController::class , 'users'])->name('roles.users');
        //Teste Mail
        Route::get('/sendMail', [App\Http\Controllers\Admin\MailController::class , 'sendMail']);

        //Table - Mesas
        Route::any('tables/search', [App\Http\Controllers\Admin\TableController::class , 'search'])->name('tables.search');
        Route::resource('tables', TableController::class);


        //Categorias
        Route::any('categories/search', [App\Http\Controllers\Admin\CategoryController::class , 'search'])->name('categories.search');
        Route::resource('categories', CategoryController::class);


        //Produtos
        Route::any('products/search', [App\Http\Controllers\Admin\ProductController::class , 'search'])->name('products.search');
        Route::resource('products', ProductController::class);

        //Usuário
        Route::any('users/search', [App\Http\Controllers\Admin\UserController::class , 'search'])->name('users.search');
        Route::get('users/invite/create', [UserInvitationController::class, 'create'])->name('users.invite.create');
        Route::post('users/invite', [UserInvitationController::class, 'store'])->name('users.invite.store');
        Route::delete('users/invite/{id}', [UserInvitationController::class, 'destroy'])->name('users.invite.destroy');
        Route::resource('users', UserController::class);

        // Organização (settings + danger zone)
        Route::get('settings/organisation', [OrganisationSettingsController::class, 'edit'])->name('settings.organisation.edit');
        Route::put('settings/organisation', [OrganisationSettingsController::class, 'update'])->name('settings.organisation.update');
        Route::delete('settings/organisation', [OrganisationSettingsController::class, 'destroy'])->name('settings.organisation.destroy');

        // Auditoria
        Route::get('audit', [AuditLogController::class, 'index'])->name('audit.index');

        //tenants -Empresa
        Route::any('tenants/search', [App\Http\Controllers\Admin\TenantController::class , 'search'])->name('tenants.search');
        Route::resource('tenants', TenantController::class);


        //Role - Cargo
        Route::any('roles/search', [App\Http\Controllers\Admin\ACL\RoleController::class , 'search'])->name('roles.search');
        Route::resource('roles', RoleController::class);


        //Permissão - Role
        Route::get('/roles/{id}/permission/{idPermission}/detach', [App\Http\Controllers\Admin\ACL\PermissionRoleController::class , 'detachPermissionRole'])->name('roles.permissions.detach');
        Route::post('/roles/{id}/permission/store', [App\Http\Controllers\Admin\ACL\PermissionRoleController::class , 'attachPermissionRole'])->name('roles.permissions.attach');
        Route::any('/roles/{id}/permission/create', [App\Http\Controllers\Admin\ACL\PermissionRoleController::class , 'permissionsAvailable'])->name('roles.permissions.available');
        Route::get('/roles/{id}/permission', [App\Http\Controllers\Admin\ACL\PermissionRoleController::class , 'permissions'])->name('roles.permissions');
        Route::get('/permission/{id}/roles', [App\Http\Controllers\Admin\ACL\PermissionRoleController::class , 'roles'])->name('permission.roles');



        //Perfis
        Route::any('/profiles/search', [App\Http\Controllers\Admin\ACL\ProfileController::class , 'search'])->name('profiles.search');
        Route::resource('profiles', ProfileController::class);

        //Perfil x Permissão
        Route::get('/profiles/{id}/permission/{idPermission}/detach', [App\Http\Controllers\Admin\ACL\PermissionProfileController::class , 'detachPermissionProfile'])->name('profiles.permissions.detach');
        Route::post('/profiles/{id}/permission/store', [App\Http\Controllers\Admin\ACL\PermissionProfileController::class , 'attachPermissionProfile'])->name('profiles.permissions.attach');
        Route::any('/profiles/{id}/permission/create', [App\Http\Controllers\Admin\ACL\PermissionProfileController::class , 'permissionsAvailable'])->name('profiles.permissions.available');
        Route::get('/profiles/{id}/permission', [App\Http\Controllers\Admin\ACL\PermissionProfileController::class , 'permissions'])->name('profiles.permissions');
        Route::get('/permission/{id}/profiles', [App\Http\Controllers\Admin\ACL\PermissionProfileController::class , 'profiles'])->name('permission.profiles');


        //Permissão
        Route::any('/permission/search', [App\Http\Controllers\Admin\ACL\PermissionController::class , 'search'])->name('permission.search');
        Route::resource('permission', PermissionController::class);
        //Home
        Route::get('/', [App\Http\Controllers\Admin\DashboardController::class , 'home'])->name('admin.index');
        Route::get('/dashboard/export', [App\Http\Controllers\Admin\DashboardController::class , 'exportUsage'])->name('admin.dashboard.export');
        Route::get('/dashboard/occurrences-recent', [App\Http\Controllers\Admin\DashboardController::class , 'occurrencesRecent'])->name('admin.dashboard.occurrences-recent');
        Route::get('/dashboard/occurrences-heatmap', [App\Http\Controllers\Admin\DashboardController::class , 'occurrencesHeatmap'])->name('admin.dashboard.occurrences-heatmap');
        Route::get('/dashboard/drivers-last-positions', [App\Http\Controllers\Admin\DashboardController::class , 'driversLastPositions'])->name('admin.dashboard.drivers-last-positions');
        //Ocorrencias
        Route::any('/occurrences/search', [App\Http\Controllers\Admin\OccurrencesController::class , 'search'])->name('occurrences.search');
        Route::get('/occurrences/{id}/driver-route', [App\Http\Controllers\Admin\OccurrencesController::class, 'driverRoute'])->name('occurrences.driver-route');
        Route::get('/occurrences/{id}/suggest-drivers', [App\Http\Controllers\Admin\DispatchController::class, 'suggest'])->name('occurrences.suggest-drivers');
        Route::post('/occurrences/{id}/assign-driver', [App\Http\Controllers\Admin\DispatchController::class, 'assign'])->name('occurrences.assign-driver');
        //Central de despacho (mapa + lista)
        Route::get('/dispatch', [App\Http\Controllers\Admin\DispatchController::class, 'console'])->name('dispatch.console');
        Route::get('/occurrences/{id}/pdf', [App\Http\Controllers\Admin\OccurrencesController::class, 'pdf'])->name('occurrences.pdf');
        Route::post('/occurrences/{id}/dismiss-duplicate', [App\Http\Controllers\Admin\OccurrencesController::class, 'dismissDuplicate'])->name('occurrences.dismiss-duplicate');
        Route::delete('/occurrences/{id}/forget', [App\Http\Controllers\Admin\OccurrencesController::class, 'forget'])->name('occurrences.forget');
        Route::resource('occurrences', OccurrencesController::class);

        //Tipo de ocorrencia
        Route::any('/typeOccurrences/search', [App\Http\Controllers\Admin\TypeOccurrenceController::class , 'search'])->name('typeOccurrences.search');
        Route::resource('typeOccurrences', TypeOccurrenceController::class);

        Route::any('/statusOccurrences/search', [App\Http\Controllers\Admin\StatusOccurrenceController::class , 'search'])->name('statusOccurrences.search');
        Route::resource('statusOccurrences', StatusOccurrenceController::class);

        //Prioridade de ocorrencia
        Route::any('/priorities/search', [App\Http\Controllers\Admin\PriorityController::class , 'search'])->name('priorities.search');
        Route::resource('priorities', PriorityController::class);

        //Orgão
        Route::any('/issuings/search', [App\Http\Controllers\Admin\IssuingController::class , 'search'])->name('issuings.search');
        Route::resource('issuings', IssuingController::class);

        Route::any('/drivers/search', [DriverController::class, 'search'])->name('drivers.search');
        Route::resource('drivers', DriverController::class);

        //Departamento
        Route::any('/departments/search', [DepartmentController::class, 'search'])->name('departments.search');
        Route::resource('departments', DepartmentController::class);

        //Equipe
        Route::any('/teams/search', [TeamController::class, 'search'])->name('teams.search');
        Route::resource('teams', TeamController::class);

        //Pesquisas
        Route::any('/surveys/search', [SurveyController::class, 'search'])->name('surveys.search');
        Route::post('/surveys/{id}/toggle', [SurveyController::class, 'toggle'])->name('surveys.toggle');
        Route::get('/surveys/{id}/responses', [SurveyController::class, 'responses'])->name('surveys.responses');
        Route::resource('surveys', SurveyController::class);

        //Notificações internas
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
        Route::post('/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');

        //Busca global
        Route::get('/search', [App\Http\Controllers\Admin\SearchController::class, 'index'])->name('search.index');

        //Relatórios
        Route::get('/reports', [App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
        Route::post('/reports', [App\Http\Controllers\Admin\ReportController::class, 'store'])->name('reports.store');
        Route::get('/reports/{id}/download', [App\Http\Controllers\Admin\ReportController::class, 'download'])->name('reports.download');
    });


Route::middleware(['auth', 'ensure.driver'])->prefix('driver')->name('driver.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Driver\DriverDashboardController::class, 'index'])->name('dashboard');
    Route::get('/occurrences', [App\Http\Controllers\Driver\DriverOccurrenceController::class, 'index'])->name('occurrences.index');
    Route::get('/occurrences/{id}', [App\Http\Controllers\Driver\DriverOccurrenceController::class, 'show'])->name('occurrences.show');
    Route::post('/occurrences/{id}/accept', [App\Http\Controllers\Driver\DriverOccurrenceController::class, 'accept'])->name('occurrences.accept');
    Route::post('/occurrences/{id}/reject', [App\Http\Controllers\Driver\DriverOccurrenceController::class, 'reject'])->name('occurrences.reject');
    Route::put('/occurrences/{id}/status', [App\Http\Controllers\Driver\DriverOccurrenceController::class, 'updateStatus'])->name('occurrences.update-status');
    Route::post('/position', [App\Http\Controllers\Driver\DriverPositionController::class, 'store'])->name('position.store')->middleware('throttle:20,1');
});

Route::get('/', [App\Http\Controllers\Site\SiteController::class , 'index'])->name('site.home');
//Route::get('/', [App\Http\Controllers\HomeController::class , 'index'])->name('login');

// Pesquisas públicas (anônimas) por token
Route::get('/p/{token}', [PublicSurveyController::class, 'show'])->name('public.surveys.show');
Route::post('/p/{token}', [PublicSurveyController::class, 'store'])->name('public.surveys.store');
Route::get('/p/{token}/obrigado', [PublicSurveyController::class, 'thanks'])->name('public.surveys.thanks');

/*Route::get('/home', function () {
 return view('home'); })->name('home')->middleware('auth'); */

Auth::routes();