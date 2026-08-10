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

use Illuminate\Support\Facades\Auth;

//Auth::routes();

Route::get('/login', [App\Http\Controllers\HomeController::class , 'index'])->name('login');

Route::get('/home', [App\Http\Controllers\HomeController::class , 'index'])->name('home');

Route::prefix('admin')
    ->namespace('')
    ->middleware('auth')
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
        Route::resource('users', UserController::class);

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
        Route::get('/dashboard/occurrences-recent', [App\Http\Controllers\Admin\DashboardController::class , 'occurrencesRecent'])->name('admin.dashboard.occurrences-recent');
        Route::get('/dashboard/drivers-last-positions', [App\Http\Controllers\Admin\DashboardController::class , 'driversLastPositions'])->name('admin.dashboard.drivers-last-positions');
        //Ocorrencias
        Route::any('/occurrences/search', [App\Http\Controllers\Admin\OccurrencesController::class , 'search'])->name('occurrences.search');
        Route::get('/occurrences/{id}/driver-route', [App\Http\Controllers\Admin\OccurrencesController::class, 'driverRoute'])->name('occurrences.driver-route');
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


/*Route::get('/home', function () {
 return view('home'); })->name('home')->middleware('auth'); */

Auth::routes();