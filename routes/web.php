<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\OperatorController;

Route::get('/', function () {
    return view('login');
});



// temporary for admin created
// Route::get('/create-admin', function () {
//     User::create([
//         'admin_name' => 'admin1',
//         'admin_pass' => 'pranjal',
//         'role'       => 1,
//         'posted_date' => now(),
//     ]);
//     return 'Admin created.';
// });

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

    Route::get('/admin/page', [AdminController::class, 'page'])->name('admin.page');

    Route::get('admin/users/create', [AdminController::class, 'usercreate'])->name('user.create');
    Route::post('admin/users', [AdminController::class, 'userstore'])->name('admin.store');
    Route::post('admin/users/{user}', [AdminController::class, 'userupdate'])->name('admin.update');
    Route::delete('admin/users/{user}', [AdminController::class, 'userdestroy'])->name('admin.destroy');



    Route::get('/dashboard/download', [AdminController::class, 'download'])->name('dashboard.download');

    Route::get('admin/dashboard', [AdminController::class, 'index'])->name('dashboard.index');
    // Route::post('admin/dashboard/download', [AdminController::class, 'download'])->name('dashboard.download');
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

    Route::get('admin/birthdays', [AdminController::class, 'birthday_index'])->name('birthdays.index');

    // Route::get('/admin/view_complaint', [AdminController::class, 'complaint_index'])->name('complaints.index');
    Route::get('/admin/commander_complaints', [AdminController::class, 'CommanderComplaints'])->name('commander.complaint.view');
    Route::get('/admin/operator_complaints', [AdminController::class, 'OperatorComplaints'])->name('operator.complaint.view');

    Route::get('/admin/commander_suchna', [AdminController::class, 'CommanderSuchnas'])->name('commander.suchnas.view');
    Route::get('/admin/operator_suchna', [AdminController::class, 'OperatorSuchnas'])->name('operator.suchnas.view');


    Route::get('/admin/details_complaint/{id}', [AdminController::class, 'complaint_show'])->name('complaints.show');
    Route::post('/admin/complaints/{id}/reply', [AdminController::class, 'postReply'])->name('complaints.reply');
    Route::delete('/complaints/{id}', [AdminController::class, 'complaintDestroy'])->name('complaints.destroy');

    Route::get('admin/assign_responsibility', [AdminController::class, 'reponsibility_index'])->name('responsibility.index');
    Route::post('admin/assign_responsibility', [AdminController::class, 'responsibility_filter'])->name('responsibility.filter');
    Route::get('/responsibility/download', [AdminController::class, 'downloadFullData'])->name('responsibility.download');
    Route::post('admin/assign_responsibility/store', [AdminController::class, 'responsibility_store'])->name('responsibility.store');
    Route::post('admin/assign-position/save', [AdminController::class, 'responsibility_store'])->name('responsibility.store');
    Route::get('/get-vidhansabhas', [AdminController::class, 'getVidhansabhasByDistrict'])->name('get.vidhansabhas');
    Route::get('/admin/fetch-location/{registration_id}', [AdminController::class, 'fetchLocationData']);
    Route::get('/admin/fetch-responsibility/{registration_id}', [AdminController::class, 'fetchFullResponsibilityData']);
    Route::post('/admin/responsibility/update/{assign_position_id}', [AdminController::class, 'responsibility_update'])->name('responsibility.update');
    Route::get('/admin/get-vidhansabha/{district_id}', [AdminController::class, 'getVidhansabha']);
    Route::get('/admin/get-mandal/{vidhansabha_id}', [AdminController::class, 'getMandal'])->name('get.mandals');
    Route::get('/admin/get-nagar/{mandal_id}', [AdminController::class, 'getNagar'])->name('get.nagars');
    Route::get('/admin/get-polling/{gram_id}', [AdminController::class, 'getPolling'])->name('get.pollings');
    Route::get('/admin/get-area/{polling_id}', [AdminController::class, 'getArea']);


    Route::get('admin/view-responsibilities', [AdminController::class, 'viewResponsibilities'])->name('view_responsibility.index');
    Route::delete('admin/assign/{id}', [AdminController::class, 'assign_destroy'])->name('assign.destroy');



    Route::get('admin/card_responsibiity_pdf', [AdminController::class, 'generate'])->name('generate.index');


    Route::get('/change-password', [LoginController::class, 'showChangePasswordForm'])->name('change_password.index');
    Route::post('/change-password', [LoginController::class, 'changePassword'])->name('change-password');


    Route::post('admin/view-responsibilities/store', [AdminController::class, 'nagarStore'])->name('nagaradd.store');

    // data upload routes
    Route::get('admin/upload_voter', [AdminController::class, 'upload'])->name('upload.index');
    Route::get('admin/download-voters-alt', [AdminController::class, 'exportVoterExcel'])->name('voters.download');
    Route::post('/admin/upload-voter-sheet', [AdminController::class, 'uploadVoterData'])->name('voter.upload');
    Route::get('admin/voter/details/{id}', [AdminController::class, 'votershow'])->name('voter.show');
    Route::get('admin/voter/update/{id}', [AdminController::class, 'voterUpdate'])->name('voter.update');
    Route::post('/admin/voter/update/{id}', [AdminController::class, 'voterUpdatePost'])->name('voter.update.post');

    // view voter data routes
    Route::get('/admin/voterlist', [AdminController::class, 'voterListPage'])->name('voter.view');
    Route::get('/admin/voterlist/view', [AdminController::class, 'viewvoter'])->name('viewvoter.index');
    Route::post('admin/voterlist', [AdminController::class, 'voterdata'])->name('voterdata.index');
    Route::get('/admin/voterlist/download', [AdminController::class, 'downloadvoterFullData'])->name('voterlist.download');
    Route::get('/admin/voterlist/files/json', [AdminController::class, 'downloadFilesJson'])->name('voterlist.files.json');


    Route::post('/admin/voterlist/request-download', [AdminController::class, 'requestDownload'])
        ->name('voterlist.request');

    Route::get('/admin/voterlist/downloads', [AdminController::class, 'downloadList'])
        ->name('voterlist.files');

    Route::get('/admin/voterlist/file/{id}', [AdminController::class, 'downloadFile'])
        ->name('voterlist.file');

    // member data upload routes
    Route::get('/admin/membership_form', [AdminController::class, 'membercreate'])->name('membership.create');
    Route::post('/admin/membership_form', [AdminController::class, 'memberstore'])->name('membership.store');
    Route::get('/admin/member/{id}/edit', [AdminController::class, 'memberedit'])->name('membership.edit');
    Route::post('/admin/member/{id}/update', [AdminController::class, 'memberupdate'])->name('membership.update');
    Route::post('/get-districts', [AdminController::class, 'getDistricts'])->name('get.districts');
    Route::post('/get-vidhansabhas', [AdminController::class, 'getVidhansabhasByDistrict'])->name('get.vidhansabhaD');

    Route::get('/admin/get-districts/{division_id}', [AdminController::class, 'districtsfetch']);
    Route::get('/admin/get-vidhansabha/{district_id}', [AdminController::class, 'vidhansabhafetch']);
    Route::get('/admin/get-mandal/{vidhansabha_id}', [AdminController::class, 'getMandals']);
    Route::get('/admin/get-nagar/{mandal_id}', [AdminController::class, 'getNagars']);
    Route::get('/admin/get-pollings/{mandal_id}', [AdminController::class, 'getPollings']);
    Route::get('/admin/get-areas/{polling_id}', [AdminController::class, 'getAreas']);
    Route::get('/admin/get-gram_pollings/{mandal_id}', [AdminController::class, 'getgramPollings']);
    Route::get('/admin/get-subjects/{department_id}', [AdminController::class, 'getSubjects']);


    Route::get('/admin/complaints/{id}/summary', [AdminController::class, 'summary'])->name('admincomplaints.summary');
});



// manager routes
Route::middleware('checklogin')->group(function () {

    // dashboard routes
    Route::get('/manager/dashboard', [ManagerController::class, 'dashboard'])->name('manager.dashboard');
    Route::get('/calendar-data', [ManagerController::class, 'getCalendarData']);
    Route::get('/complaint-summary', [ManagerController::class, 'getComplaintSummary']);
    Route::get('/fetch-suchna', [ManagerController::class, 'fetchSuchna']);
    Route::get('/fetch-vibhaag-count', [ManagerController::class, 'fetchVibhaagWiseCount']);
    Route::get('/fetch-status', [ManagerController::class, 'fetchStatus']);
    Route::get('/fetch-status-suchna', [ManagerController::class, 'fetchSuchnaStatus']);
    Route::get('/dashboard/stats', [ManagerController::class, 'fetchDashboardStats']);
    Route::get('/complaints/{section}', [ManagerController::class, 'sectionView']);
    Route::get('/voters/details', [ManagerController::class, 'voterDetails']);
    Route::get('/detail_suchna/{id}', [ManagerController::class, 'detail_suchna']);
    Route::get('/ajax/forwarded-counts', [ManagerController::class, 'getForwardedCounts'])
        ->name('ajax.forwarded.counts');
    Route::get('/ajax/forwarded-counts-vikash', [ManagerController::class, 'getForwardedVikashCounts'])
        ->name('ajax.forwarded.counts.vikash');
    Route::get('/fetch-forwards', [ManagerController::class, 'getForwardedComplaintsPerManager']);
    Route::get('/ajax/complaints/not_opened_count', [ManagerController::class, 'countUnheardComplaints']);
    Route::get('/ajax/complaints/not_opened_count_vikash', [ManagerController::class, 'countUnheardComplaintsVikash']);
    Route::get('/voters/details/download', [ManagerController::class, 'downloadVoters'])->name('voterlistdashboard.download');
    Route::get('/dashboard/followup-counts', [ManagerController::class, 'getFollowupCounts'])->name('dashboard.followupCounts');
    Route::get('/followup/followup-details', [ManagerController::class, 'followupDetails'])
        ->name('complaints.followupDetails');
    Route::get('/dashboard/followup-not-done-details', [ManagerController::class, 'notDoneDetails'])
        ->name('dashboard.notDoneDetails');
    Route::get('/dashboard/followup-not-done-counts', [ManagerController::class, 'getNotDoneCounts'])
        ->name('dashboard.notDoneCounts');


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

    // department routes
    Route::get('manager/department_master', [ManagerController::class, 'department_index'])->name('department.index');
    Route::post('manager/department_master', [ManagerController::class, 'department_store'])->name('department.store');
    Route::get('manager/department/edit/{id}', [ManagerController::class, 'department_edit'])->name('department.edit');
    Route::post('manager/department/update/{id}', [ManagerController::class, 'department_update'])->name('department.update');



    // designation routes
    Route::get('manager/designation_master', [ManagerController::class, 'indexDesignation'])->name('designation.master');
    Route::post('manager/designation_master/store', [ManagerController::class, 'designationStore'])->name('designation.store');
    Route::get('manager/designation_master/edit/{id}', [ManagerController::class, 'designationEdit'])->name('designation.edit');
    Route::post('manager/designation_master/update/{id}', [ManagerController::class, 'designationUpdate'])->name('designation.update');

    // complaint subject routes
    Route::get('manager/complaint_master', [ManagerController::class, 'indexComplaint'])->name('complaintSubject.master');
    Route::post('manager/complaint_master/store', [ManagerController::class, 'complaintSubjectStore'])->name('complaintSubject.store');
    Route::get('manager/complaint_master/edit/{id}', [ManagerController::class, 'complaintSubjectEdit'])->name('complaintSubject.edit');
    Route::post('manager/complaint_master/update/{id}', [ManagerController::class, 'complaintSubjectUpdate'])->name('complaintSubject.update');


    // complaint Reply routes
    Route::get('manager/complaint_reply_master', [ManagerController::class, 'complaintReplyIndex'])->name('complaintReply.index');
    Route::post('manager/complaint_reply_master', [ManagerController::class, 'complaintReplyStore'])->name('complaintReply.store');
    Route::get('manager/reply/edit/{id}', [ManagerController::class, 'complaintReplyEdit'])->name('complaintReply.edit');
    Route::post('manager/reply/update/{id}', [ManagerController::class, 'complaintReplyUpdate'])->name('complaintReply.update');


    // adhikari routes
    Route::get('manager/create_adhikari_master', [ManagerController::class, 'adhikariIndex'])->name('adhikari.index');
    Route::post('manager/create_adhikari_master/store', [ManagerController::class, 'adhikariStore'])->name('adhikari.store');
    Route::get('manager/create_adhikari_master/edit/{id}', [ManagerController::class, 'adhikariEdit'])->name('adhikari.edit');
    Route::post('manager/create_adhikari_master/update/{id}', [ManagerController::class, 'adhikariUpdate'])->name('adhikari.update');


    // category routes
    Route::get('manager/category_master', [ManagerController::class, 'categoryIndex'])->name('category.index');
    Route::post('manager/category_master', [ManagerController::class, 'categoryStore'])->name('category.store');
    Route::get('manager/category/edit/{id}', [ManagerController::class, 'categoryEdit'])->name('category.edit');
    Route::post('manager/category/update/{id}', [ManagerController::class, 'categoryUpdate'])->name('category.update');
    // Route::get('/category/delete/{id}', [ManagerController::class, 'categoryDestroy'])->name('category.delete');



    // interest routes
    Route::get('manager/interest_master', [ManagerController::class, 'interestIndex'])->name('interest.index');
    Route::post('manager/interest_master', [ManagerController::class, 'interestStore'])->name('interest.store');
    Route::get('manager/interest/edit/{id}', [ManagerController::class, 'interestEdit'])->name('interest.edit');
    Route::post('manager/interest/update/{id}', [ManagerController::class, 'interestUpdate'])->name('interest.update');
    // Route::get('/interest/delete/{id}', [ManagerController::class, 'interestDestroy'])->name('interest.delete');


    // business routes
    Route::get('manager/business_master', [ManagerController::class, 'businessIndex'])->name('business.index');
    Route::post('manager/business_master', [ManagerController::class, 'businessStore'])->name('business.store');
    Route::get('manager/business/edit/{id}', [ManagerController::class, 'businessEdit'])->name('business.edit');
    Route::post('manager/business/update/{id}', [ManagerController::class, 'businessUpdate'])->name('business.update');
    // Route::get('/business/delete/{id}', [ManagerController::class, 'businessDestroy'])->name('business.delete');


    // politics routes
    Route::get('manager/politics_master', [ManagerController::class, 'politicsIndex'])->name('politics.index');
    Route::post('manager/politics_master', [ManagerController::class, 'politicsStore'])->name('politics.store');
    Route::get('manager/politics/edit/{id}', [ManagerController::class, 'politicsEdit'])->name('politics.edit');
    Route::post('manager/politics/update/{id}', [ManagerController::class, 'politicsUpdate'])->name('politics.update');
    // Route::get('/politics/delete/{id}', [ManagerController::class, 'politicsDestroy'])->name('politics.delete');


    // religion routes
    Route::get('manager/religion_master', [ManagerController::class, 'religionIndex'])->name('religion.index');
    Route::post('manager/religion_master', [ManagerController::class, 'religionStore'])->name('religion.store');
    Route::get('manager/religion/edit/{id}', [ManagerController::class, 'religionEdit'])->name('religion.edit');
    Route::post('manager/religion/update/{id}', [ManagerController::class, 'religionUpdate'])->name('religion.update');
    // Route::get('/religion/delete/{id}', [ManagerController::class, 'religionDestroy'])->name('religion.delete');


    // education routes
    Route::get('manager/education_master', [ManagerController::class, 'educationIndex'])->name('education.index');
    Route::post('manager/education_master', [ManagerController::class, 'educationStore'])->name('education.store');
    Route::get('manager/education/edit/{id}', [ManagerController::class, 'educationEdit'])->name('education.edit');
    Route::post('manager/education/update/{id}', [ManagerController::class, 'educationUpdate'])->name('education.update');
    // Route::get('/education/delete/{id}', [ManagerController::class, 'educationDestroy'])->name('education.delete');


    Route::post('manager/ajax/designation', [ManagerController::class, 'getDesignation'])->name('ajax.designation');

    Route::get('/manager/commander_complaints', [ManagerController::class, 'viewCommanderComplaints'])->name('commander.complaints.view');
    Route::get('/manager/operator_complaints', [ManagerController::class, 'viewOperatorComplaints'])->name('operator.complaints.view');

    Route::get('/manager/commander_suchna', [ManagerController::class, 'viewCommanderSuchnas'])->name('commander.suchna.view');
    Route::get('/manager/operator_suchna', [ManagerController::class, 'viewOperatorSuchnas'])->name('operator.suchna.view');
    Route::post('/manager/attachments/{attachment}/delete', [ManagerController::class, 'attachmentdestroy'])
        ->name('attachments.destroy');
    Route::post('/manager/update-complaint/{id}', [ManagerController::class, 'updateComplaint'])->name('complaints.update');
   
    Route::post('/manager/update-suchna/{id}', [ManagerController::class, 'updateSuchna'])->name('suchna.update');

    Route::get('/manager/details_complaints/{id}', [ManagerController::class, 'allcomplaints_show'])->name('complaints_show.details');
    Route::get('/manager/details_suchnas/{id}', [ManagerController::class, 'allsuchnas_show'])->name('suchna_show.details');
    Route::post('/manager/complaints/{id}/reply', [ManagerController::class, 'complaintsReply'])->name('complaint_reply.reply');

    Route::get('manager/get-voter', [ManagerController::class, 'getVoter'])->name('get.voter');


    Route::get('/manager/get-districts/{division_id}', [ManagerController::class, 'getDistricts']);
    Route::get('/manager/get-vidhansabha/{district_id}', [ManagerController::class, 'getVidhansabhas']);
    Route::get('/manager/get-mandal/{vidhansabha_id}', [ManagerController::class, 'getMandals']);
    Route::get('/manager/get-nagar/{mandal_id}', [ManagerController::class, 'getNagars']);
    Route::get('/manager/get-pollings/{mandal_id}', [ManagerController::class, 'getPollings']);
    Route::get('/manager/get-areas/{polling_id}', [ManagerController::class, 'getAreas']);
    Route::get('/manager/get-subjects/{department_id}', [ManagerController::class, 'getSubjects']);
    Route::get('/manager/get-gram_pollings/{mandal_id}', [ManagerController::class, 'getgramPollings']);
    Route::get('/manager/get-nagars-by-vidhansabha/{vidhansabha_id}', [ManagerController::class, 'getNagarsByVidhansabha']);
    Route::get('/manager/get-pollings-gram/{mandal_id}', [ManagerController::class, 'getPollingsByNagar']);
    Route::get('/get-designations/{department_name}', [ManagerController::class, 'getDesignations']);
    Route::get('/manager/get-subjects-department/{departmentName}', [ManagerController::class, 'getSubjectsByDepartment']);

    Route::get('/manager/get-parent-mandal/{nagar_id}', [ManagerController::class, 'getMandalFromNagar']);
    Route::get('/manager/get-mandal-from-id/{mandal_id}', [ManagerController::class, 'getMandalOptionsFromId']);
    Route::get('/manager/get-parent-vidhansabha/{mandal_id}', [ManagerController::class, 'getVidhansabhaFromMandal']);
    Route::get('/manager/get-parent-district/{vidhansabha_id}', [ManagerController::class, 'getDistrictFromVidhansabha']);
    Route::get('/manager/get-parent-division/{district_id}', [ManagerController::class, 'getDivisionFromDistrict']);

    Route::get('/manager/get-vidhansabha-from-id/{vidhansabha_id}', [ManagerController::class, 'getVidhansabhaOptionsFromId']);
    Route::get('/manager/get-district-from-id/{district_id}', [ManagerController::class, 'getDistrictOptionsFromId']);
    Route::get('/manager/get-division-from-id/{division_id}', [ManagerController::class, 'getDivisionOptionsFromId']);



    // all followups show in details routes 
    Route::get('/manager/allfollowups_details', [ManagerController::class, 'allfollowupsdetails'])->name('allfollowup_details.index');
    Route::get('/manager/allfollowups', [ManagerController::class, 'followupindex'])
        ->name('allfollowups.index');



    // daily report routes 
    Route::get('/manager/daily_report', [ManagerController::class, 'daily_report'])->name('dailyreport.index');


    Route::get('/manager/complaints/{id}/summary', [ManagerController::class, 'summary'])->name('complaints.summary');
});



// operator routes
Route::middleware('checklogin')->group(function () {

    Route::get('/operator/dashboard', [OperatorController::class, 'dashboard'])->name('operator.dashboard');
    Route::get('/operator/complaint-summary', [OperatorController::class, 'getComplaintSummary']);
    Route::get('/operator/todays-followups', [OperatorController::class, 'getTodaysFollowups']);
    Route::get('/operator/followup-summary', [OperatorController::class, 'getFollowupSummary']);

    Route::get('/operator/complaints', [OperatorController::class, 'index'])->name('operator_complaint.index');
    Route::get('/get-voter', [OperatorController::class, 'getVoter'])->name('get.voter');
    Route::post('/operator/complaints/store', [OperatorController::class, 'store'])->name('operator_complaint.store');
    Route::get('/operator/view_complaint', [OperatorController::class, 'view_complaints'])->name('operator_complaint.view');
    Route::get('/operator/details_complaint/{id}', [OperatorController::class, 'operator_complaints_show'])->name('operator_complaint.show');
    Route::post('/operator/complaints/{id}/reply', [OperatorController::class, 'operatorReply'])->name('operator_complaint.reply');

    Route::get('/operator/info', [OperatorController::class, 'suchnaIndex'])->name('operator_suchna.index');
    Route::get('/operator/view_info', [OperatorController::class, 'view_suchna'])->name('operator_suchna.view');
    Route::post('/operator/info/store', [OperatorController::class, 'suchnaStore'])->name('operator_suchna.store');

    Route::get('/operator/get-districts/{division_id}', [OperatorController::class, 'getDistricts']);
    Route::get('/operator/get-vidhansabha/{district_id}', [OperatorController::class, 'getVidhansabhas']);
    Route::get('/operator/get-mandal/{vidhansabha_id}', [OperatorController::class, 'getMandals']);
    Route::get('/operator/get-nagar/{mandal_id}', [OperatorController::class, 'getNagars']);
    Route::get('/operator/get-pollings/{mandal_id}', [OperatorController::class, 'getPollings']);
    Route::get('/operator/get-areas/{polling_id}', [OperatorController::class, 'getAreas']);
    Route::get('/operator/get-gram_pollings/{mandal_id}', [OperatorController::class, 'getgramPollings']);
    Route::get('/operator/get-subjects/{department_id}', [OperatorController::class, 'getSubjects']);

    Route::get('/operator/get-parent-mandal/{nagar_id}', [OperatorController::class, 'getMandalFromNagar']);
    Route::get('/operator/get-mandal-from-id/{mandal_id}', [OperatorController::class, 'getMandalOptionsFromId']);
    Route::get('/operator/get-parent-vidhansabha/{mandal_id}', [OperatorController::class, 'getVidhansabhaFromMandal']);
    Route::get('/operator/get-parent-district/{vidhansabha_id}', [OperatorController::class, 'getDistrictFromVidhansabha']);
    Route::get('/operator/get-parent-division/{district_id}', [OperatorController::class, 'getDivisionFromDistrict']);

    Route::get('/get-polling-area/{nagarId}', [OperatorController::class, 'getPollingAndArea']);
    Route::get('/operator/get-nagars-by-vidhansabha/{vidhansabha_id}', [OperatorController::class, 'getNagarsByVidhansabha']);
    Route::get('/get-designations/{department_name}', [OperatorController::class, 'getDesignations'])->name('get.designations');
    Route::post('operator/ajax/designation', [OperatorController::class, 'getDesignation'])->name('ajax.designation');
    Route::get('/operator/get-subjects-department/{departmentName}', [OperatorController::class, 'getSubjectsByDepartment']);

    Route::get('/operator/get-vidhansabha-from-id/{vidhansabha_id}', [OperatorController::class, 'getVidhansabhaOptionsFromId']);
    Route::get('/operator/get-district-from-id/{district_id}', [OperatorController::class, 'getDistrictOptionsFromId']);
    Route::get('/operator/get-division-from-id/{division_id}', [OperatorController::class, 'getDivisionOptionsFromId']);



    Route::get('/operator/next_followup', [OperatorController::class, 'nextFollowup'])->name('next_followup.index');
    Route::get('/operator/next_followups', [OperatorController::class, 'nextFollowupFilter'])->name('next_followup_filter.index');
    Route::post('/update-contact-status/{id}', [OperatorController::class, 'updateContactStatus'])->name('update.contact.status');
    Route::get('/operator/followup_details/{id}', [OperatorController::class, 'followup_show'])->name('follow_up.show');


    Route::get('/operator/complaints/{id}/summary', [OperatorController::class, 'summary'])->name('operatorcomplaints.summary');



    // incoming call routes
    Route::get('/operator/incoming_calls', [OperatorController::class, 'incoming'])->name('incoming_calls.index');
    Route::post('/operator/incoming-calls/store-reason', [OperatorController::class, 'storeIncomingReason'])->name('incoming.storeReason');
    Route::post('/operator/update-incoming-contact-status/{id}', [OperatorController::class, 'updateIncomingContactStatus'])->name('update.incoming.contact.status');
});



// member routes
Route::middleware('checkmember')->group(function () {
    Route::get('/member/dashboard', [MemberController::class, 'dashboard'])->name('member.dashboard');

    Route::get('member/complaint', [MemberController::class, 'complaint'])->name('member.complaint');

    Route::get('member/complaints', [MemberController::class, 'index'])->name('complaint.index');
    Route::post('member/complaints/store', [MemberController::class, 'store'])->name('complaint.store');
    Route::get('/get-districts/{division_id}', [MemberController::class, 'getDistricts']);
    Route::get('/get-vidhansabha/{district_id}', [MemberController::class, 'getVidhansabhas']);
    Route::get('/get-mandal/{vidhansabha_id}', [MemberController::class, 'getMandals']);
    Route::get('/get-nagar/{mandal_id}', [MemberController::class, 'getNagars']);
    Route::get('/get-polling/{mandal_id}', [MemberController::class, 'getPollings']);
    Route::get('/get-area/{polling_id}', [MemberController::class, 'getAreas']);
    Route::get('/member/get-pollings/{mandal_id}', [MemberController::class, 'pollingsfetch']);
    Route::get('/member/get-areas/{polling_id}', [MemberController::class, 'areasfetch']);
    Route::get('/member/get-gram_pollings/{mandal_id}', [MemberController::class, 'getgramPollings']);
    Route::get('/member/get-subjects/{department_id}', [MemberController::class, 'getSubjects']);


    Route::get('/member/view_complaint', [MemberController::class, 'complaint_index'])->name('complaints.view');

    Route::get('/member/view_suchna', [MemberController::class, 'view_suchna'])->name('suchna.view');

    Route::get('/member/details_complaint/{id}', [MemberController::class, 'complaint_show'])->name('complaint.show');
    Route::post('/member/complaints/{id}/reply', [MemberController::class, 'postReply'])->name('complaint.reply');



    Route::get('/member/complaints/{id}/summary', [MemberController::class, 'summary'])->name('membercomplaints.summary');
});
