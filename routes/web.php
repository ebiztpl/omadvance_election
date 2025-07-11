<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\MemberController;

Route::get('/', function () {
    return view('login');
});



// temporary for admin created
Route::get('/create-admin', function () {
    User::create([
        'admin_name' => 'admin1',
        'admin_pass' => 'pranjal',
        'role'       => 1,
        'posted_date' => now(),
    ]);
    return 'Admin created.';
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::get('/logout', [LoginController::class, 'logout']);

Route::post('/send-otp', [LoginController::class, 'sendOtp']);
Route::post('/verify_otp', [LoginController::class, 'verifyOtp']);

// Route::get('/dashboard', [LoginController::class, 'index'])->name('dashboard');


Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');



// admin routes
Route::middleware('checklogin')->group(function () {
    Route::get('admin/dashboard', [AdminController::class, 'index'])->name('dashboard.index');
    Route::post('admin/dashboard/download', [AdminController::class, 'download'])->name('dashboard.download');
    Route::post('admin/dashboard', [AdminController::class, 'filter'])->name('dashboard.filter');
    Route::post('admin/dashboard/vidhansabha-options', [AdminController::class, 'getVidhansabha']);
    Route::post('admin/dashboard/mandal-options', [AdminController::class, 'getMandal']);
    Route::get('admin/register/details/{id}', [AdminController::class, 'show'])->name('register.show');
    Route::post('/admin/registration/upload-photo', [AdminController::class, 'uploadPhoto'])->name('registration.uploadPhoto');

    Route::get('admin/register/card/{id}', [AdminController::class, 'card'])->name('register.card');
    Route::get('/admin/card/print/{id}', [AdminController::class, 'print'])->name('admin.card.print');
    Route::delete('/register/{id}', [AdminController::class, 'destroy'])->name('register.destroy');


    Route::get('/admin/dashboard2', [AdminController::class, 'dashboard2_index'])->name('dashboard2.index');
    Route::post('/dashboard2.filter', [AdminController::class, 'dashboard2_filter'])->name('dashboard2.filter');
    Route::post('/admin/dashboard2', [AdminController::class, 'dashboard2_download'])->name('dashboard2.download');

    Route::get('admin/birthdays', [AdminController::class, 'birthday_index'])->name('birthdays.index');

    Route::get('/admin/view_complaint', [AdminController::class, 'complaint_index'])->name('complaints.index');
    Route::get('/admin/details_complaint/{id}', [AdminController::class, 'complaint_show'])->name('complaints.show');
    Route::post('/admin/complaints/{id}/reply', [AdminController::class, 'postReply'])->name('complaints.reply');

    Route::get('admin/assign_responsibility', [AdminController::class, 'reponsibility_index'])->name('responsibility.index');
    Route::post('admin/assign_responsibility', [AdminController::class, 'responsibility_filter'])->name('responsibility.filter');
    Route::post('admin/assign_responsibility/store', [AdminController::class, 'responsibility_store'])->name('responsibility.store');
    Route::post('admin/assign-position/save', [AdminController::class, 'responsibility_store'])->name('responsibility.store');
    Route::get('/get-vidhansabhas', [AdminController::class, 'getVidhansabhasByDistrict'])->name('get.vidhansabhas');
    Route::get('/admin/fetch-location/{registration_id}', [AdminController::class, 'fetchLocationData']);
    Route::get('/admin/fetch-responsibility/{registration_id}', [AdminController::class, 'fetchFullResponsibilityData']);
    Route::post('/admin/responsibility/update/{assign_position_id}', [AdminController::class, 'responsibility_update'])->name('responsibility.update');
    Route::get('/admin/get-vidhansabha/{district_id}', [AdminController::class, 'getVidhansabha']);
    Route::get('/admin/get-mandal/{vidhansabha_id}', [AdminController::class, 'getMandal']);
    Route::get('/admin/get-nagar/{mandal_id}', [AdminController::class, 'getNagar']);
    Route::get('/admin/get-polling/{gram_id}', [AdminController::class, 'getPolling']);
    Route::get('/admin/get-area/{polling_id}', [AdminController::class, 'getArea']);


    Route::get('admin/view-responsibilities', [AdminController::class, 'viewResponsibilities'])->name('view_responsibility.index');
    Route::delete('admin/assign/{id}', [AdminController::class, 'assign_destroy'])->name('assign.destroy');
    


    Route::get('admin/card_responsibiity_pdf', [AdminController::class, 'generate'])->name('generate.index');


    Route::get('/change-password', [LoginController::class, 'showChangePasswordForm'])->name('change_password.index');
    Route::post('/change-password', [LoginController::class, 'changePassword'])->name('change-password');
});



// manager routes
Route::middleware('checklogin')->group(function () {
    // division routes
    Route::get('manager/division_master', [ManagerController::class, 'index'])->name('division.index');
    Route::post('manager/division_master', [ManagerController::class, 'store'])->name('division.store');
    Route::get('manager/division/edit/{id}', [ManagerController::class, 'edit'])->name('division.edit');
    Route::post('manager/division/update/{id}', [ManagerController::class, 'update'])->name('division.update');
    // Route::get('/division/delete/{id}', [ManagerController::class, 'destroy'])->name('division.delete');

    // city routes
    Route::get('manager/city-master', [ManagerController::class, 'cityMaster'])->name('city.master');
    Route::post('manager/city-master/store', [ManagerController::class, 'storeCity'])->name('city.store');
    Route::get('manager/city-master/edit/{id}', [ManagerController::class, 'editCity'])->name('city.edit');
    Route::post('manager/city-master/update/{id}', [ManagerController::class, 'updateCity'])->name('city.update');

    // vidhansabha/loksabha routes
    Route::get('manager/vidhansabha-loksabha', [ManagerController::class, 'indexVidhansabha'])->name('vidhansabha.index');
    Route::post('manager/vidhansabha-loksabha/store', [ManagerController::class, 'storeVidhansabha'])->name('vidhansabha.store');
    Route::get('manager/vidhansabha-loksabha/edit/{id}', [ManagerController::class, 'editVidhansabha'])->name('vidhansabha.edit');
    Route::post('manager/vidhansabha-loksabha/update/{id}', [ManagerController::class, 'updateVidhansabha'])->name('vidhansabha.update');

    // sansadiya chetra routes
    Route::get('manager/sansadiya', [ManagerController::class, 'indexSansadiya'])->name('sansadiya.index');
    Route::post('manager/sansadiya/store', [ManagerController::class, 'storeSansadiya'])->name('sansadiya.store');
    // mandal routes 
    Route::get('manager/mandal-master', [ManagerController::class, 'mandalindex'])->name('mandal.index');
    Route::post('manager/mandal-master', [ManagerController::class, 'mandalstore'])->name('mandal.store');
    Route::get('manager/mandal-master/edit/{id}', [ManagerController::class, 'mandaledit'])->name('mandal.edit');
    Route::post('manager/mandal-master/update/{id}', [ManagerController::class, 'mandalupdate'])->name('mandal.update');
    Route::post('manager/mandal-master/getvidhansabha', [ManagerController::class, 'getVidhansabha'])->name('mandal.getVidhansabha');


    // nagar routes
    Route::get('manager/nagar-master', [ManagerController::class, 'nagarIndex'])->name('nagar.index');
    Route::post('manager/nagar-master/store', [ManagerController::class, 'nagarStore'])->name('nagar.store');
    Route::get('manager/nagar-master/edit/{id}', [ManagerController::class, 'nagarEdit'])->name('nagar.edit');
    Route::post('manager/nagar-master/update/{id}', [ManagerController::class, 'nagarUpdate'])->name('nagar.update');

    Route::post('manager/ajax/vidhansabha', [ManagerController::class, 'getVidhansabha'])->name('ajax.vidhansabha');
    Route::post('manager/ajax/mandal', [ManagerController::class, 'getMandal'])->name('ajax.mandal');


    // polling routes
    Route::get('manager/polling-master', [ManagerController::class, 'pollingIndex'])->name('polling.index');
    Route::post('manager/polling-master/store', [ManagerController::class, 'pollingStore'])->name('polling.store');
    Route::get('manager/polling-master/edit/{id}', [ManagerController::class, 'pollingEdit'])->name('polling.edit');
    Route::post('manager/polling-master/update/{id}', [ManagerController::class, 'pollingUpdate'])->name('polling.update');

    Route::post('manager/ajax/nagar', [ManagerController::class, 'getNagar'])->name('ajax.nagar');


    // area routes
    Route::get('manager/area-master', [ManagerController::class, 'areaIndex'])->name('area.index');
    Route::post('manager/area-master/store', [ManagerController::class, 'areaStore'])->name('area.store');
    Route::get('manager/area-master/edit/{id}', [ManagerController::class, 'areaEdit'])->name('area.edit');
    Route::post('manager/area-master/update/{id}', [ManagerController::class, 'areaUpdate'])->name('area.update');
    Route::post('manager/area-master/ajax', [ManagerController::class, 'ajax'])->name('area.ajax');

    // level routes
    Route::get('manager/levels', [ManagerController::class, 'levelIndex'])->name('level.index');
    Route::post('manager/levels', [ManagerController::class, 'levelStore'])->name('level.store');
    Route::get('manager/levels/edit/{id}', [ManagerController::class, 'levelEdit'])->name('level.edit');
    Route::post('manager/levels/update/{id}', [ManagerController::class, 'levelUpdate'])->name('level.update');


    // position routes
    Route::get('manager/positions', [ManagerController::class, 'positionIndex'])->name('positions.index');
    Route::post('manager/positions', [ManagerController::class, 'positionStore'])->name('positions.store');
    Route::get('manager/positions/edit/{id}', [ManagerController::class, 'positionEdit'])->name('positions.edit');
    Route::post('manager/positions/update/{id}', [ManagerController::class, 'positionUpdate'])->name('positions.update');


    // jati routes
    Route::get('manager/jati', [ManagerController::class, 'jatiIndex'])->name('jati.index');
    Route::post('manager/jati', [ManagerController::class, 'jatiStore'])->name('jati.store');
    Route::get('manager/jati/edit/{id}', [ManagerController::class, 'jatiEdit'])->name('jati.edit');
    Route::post('manager/jati/update/{id}', [ManagerController::class, 'jatiUpdate'])->name('jati.update');

    // jatiwise_voter routes
    Route::get('manager/jati-polling', [ManagerController::class, 'jatiPollingIndex'])->name('jati_polling.index');
    Route::post('manager/jati-polling', [ManagerController::class, 'jatiPollingStore'])->name('jati_polling.store');
    Route::post('manager/ajax/pollings', [ManagerController::class, 'getPolling']);
    Route::post('manager/ajax/grams', [ManagerController::class, 'getGrams']);

    // jatiwise mambers routes
    Route::get('manager/jatiwise_members', [ManagerController::class, 'jatiwiseIndex'])->name('jatiwise.index');
    Route::post('manager/jatiwise_members/dropdown', [ManagerController::class, 'getDropdown'])->name('jatiwise.dropdown');
    Route::post('manager/jatiwise_members/filter', [ManagerController::class, 'searchJatiwise'])->name('jatiwise.filter');
});



// user routes
Route::middleware('checklogin')->group(function () {
});



// member routes
Route::middleware('checkmember')->group(function () {
    Route::get('member/dashboard', function () {return view('member/dashboard');})->name('member.dashboard');
    Route::get('member/complaints', [MemberController::class, 'index'])->name('complaint.index');
    Route::post('member/complaints/store', [MemberController::class, 'store'])->name('complaint.store');
    Route::get('/get-districts/{division_id}', [MemberController::class, 'getDistricts']);
    Route::get('/get-vidhansabha/{district_id}', [MemberController::class, 'getVidhansabhas']);
    Route::get('/get-mandal/{vidhansabha_id}', [MemberController::class, 'getMandals']);
    Route::get('/get-nagar/{mandal_id}', [MemberController::class, 'getNagars']);
    Route::get('/get-polling/{mandal_id}', [MemberController::class, 'getPollings']);
    Route::get('/get-area/{polling_id}', [MemberController::class, 'getAreas']);

    Route::get('/member/view_complaint', [MemberController::class, 'complaint_index'])->name('complaints.view');
    Route::get('/member/details_complaint/{id}', [MemberController::class, 'complaint_show'])->name('complaint.show');
    Route::post('/member/complaints/{id}/reply', [MemberController::class, 'postReply'])->name('complaint.reply');
});