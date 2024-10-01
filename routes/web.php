<?php

use App\Http\Controllers\AcccountCountroller;
use App\Http\Controllers\admin\DashboardController;
use App\Http\Controllers\admin\jobApplicationController;
use App\Http\Controllers\admin\JobController;
use App\Http\Controllers\admin\UserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JobsController;
use GuzzleHttp\Middleware;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/',[HomeController::class,'index'])->name('home');
Route::get('/jobs',[JobsController::class,'index'])->name('jobs');
Route::get('/jobs/detail/{id}',[JobsController::class,'detail'])->name('jobDetail');
Route::post('/apply-job',[JobsController::class,'applyJob'])->name('applyJob');
Route::post('/save-job',[JobsController::class,'saveJob'])->name('saveJob');
Route::get('/forgot-password',[AcccountCountroller::class,'forgotPassword'])->name('account.forgotPassword');
Route::post('/process-forgot-password',[AcccountCountroller::class,'processForgotPassword'])->name('account.processForgotPassword');
Route::get('/reset-password/{token}',[AcccountCountroller::class,'resetPassword'])->name('account.resetPassword');
Route::post('/process-reset-password',[AcccountCountroller::class,'processResetPassword'])->name('account.processResetPassword');


Route::group(['prefix' => 'admin'],function(){
     Route::get('/dashboard',[DashboardController::class,'index'])->name('admin.dashboard');
     Route::get('/users',[UserController::class,'index'])->name('admin.users');
     Route::get('/users/{id}',[UserController::class,'edit'])->name('admin.users.edit');
     Route::post('/users/{id}',[UserController::class,'update'])->name('admin.users.update');
     Route::delete('/users',[UserController::class,'destroy'])->name('admin.users.destroy');
     Route::get('/jobs',[JobController::class,'index'])->name('admin.jobs');
     Route::get('/jobs/edit/{id}',[JobController::class,'edit'])->name('admin.jobs.edit');
     Route::post('/jobs/{id}',[JobController::class,'update'])->name('admin.jobs.update');
     Route::delete('/jobs',[JobController::class,'destroy'])->name('admin.jobs.destroy');
     Route::get('/job-applications',[jobApplicationController::class,'index'])->name('admin.jobApplications');
     Route::delete('/job-applications',[jobApplicationController::class,'destroy'])->name('admin.jobApplications.destroy');


})->middleware('checkRole');


Route::group(['account'],function(){

// guest route
    Route::group(['Middleware' => 'guest'],function(){
        Route::get('/account/register',[AcccountCountroller::class,'registration'])->name('account.registration');
        Route::post('/account/process-register',[AcccountCountroller::class,'processregistration'])->name('account.processRegistration');
        Route::get('/account/login',[AcccountCountroller::class,'login'])->name('account.login');
        Route::post('/account/authenticate',[AcccountCountroller::class,'authenticate'])->name('account.authenticate');

    });

// authenticated route
       Route::group(['middleware' => 'auth' ],function(){
        Route::get('/account/profile',[AcccountCountroller::class,'profile'])->name('account.profile');
        Route::post('/account/update-profile',[AcccountCountroller::class,'updateProfile'])->name('account.updateprofile');
        Route::post('/account/update-profile-pic',[AcccountCountroller::class,'updateProfilePic'])->name('account.updateprofilepic');
        Route::get('/account/logout',[AcccountCountroller::class,'logout'])->name('account.logout');
        Route::get('/account/create-job',[AcccountCountroller::class,'createJob'])->name('account.createJob');
        Route::post('/account/save-job',[AcccountCountroller::class,'saveJob'])->name('account.saveJob');
        Route::get('/account/my-jobs',[AcccountCountroller::class,'myJobs'])->name('account.myJobs');
        Route::get('/account/my-jobs/edit/{jobid}',[AcccountCountroller::class,'editjob'])->name('account.editjob');
        Route::post('/account/update-job/{jobid}',[AcccountCountroller::class,'updatejob'])->name('account.updatejob');
        Route::post('/account/delete-job',[AcccountCountroller::class,'deleteJob'])->name('account.deletejob');
        Route::get('/account/my-job-applications',[AcccountCountroller::class,'myJobApplications'])->name('account.myJobApplications');
        Route::post('/account/remove-job-application',[AcccountCountroller::class,'removeJobs'])->name('account.removeJobs');
        Route::get('/account/saved-jobs',[AcccountCountroller::class,'savedJobs'])->name('account.savedJobs');
        Route::post('/account/remove-saved-job',[AcccountCountroller::class,'removeSavedJob'])->name('account.removeSavedJob');
        Route::post('/account/update-password',[AcccountCountroller::class,'updatePassword'])->name('account.updatePassword');
       });

});