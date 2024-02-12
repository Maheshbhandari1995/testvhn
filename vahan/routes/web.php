<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\RcdetailsController;
use App\Http\Controllers\ChallanController;
use App\Http\Controllers\LicenseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DashboardreportController;
use App\Http\Controllers\BulkUploadController;
use App\Http\Controllers\RcBulkUploadController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\CommonController;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\CronController;
use App\Http\Controllers\BulkCronController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\OcrController;
use App\Http\Controllers\PanController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/', [LoginController::class, 'indexfun']);
Route::get('/dashboard', [LoginController::class, 'indexfun']);
Route::get('/login', [LoginController::class, 'indexfun'])->name('login');
Route::post('/signin', [LoginController::class, 'signin']);
Route::get('/signout', [LoginController::class, 'signout']);

Route::post('dashboard/list', [DashboardController::class, 'getDashboardList'])->name('dashboard');
Route::post('userdashboard/list', [DashboardController::class, 'getUserDashboardList'])->name('userdashboard.list');


//Bulk List
Route::get('/rcbulkreport', function () {
    return view('rc.rcbulkreport');
})->name('rc.rcbulkreport');

Route::post('rcbulkreport.list', [RcBulkUploadController::class, 'rcBulkReportList'])->name('rcbulkreport.list');

 Route::middleware(['auth.user'])->group(function () {

    Route::get('/schallan', function () {
        return view('challan.schallan');
    })->name('challan.schallan');

    Route::get('/schallan', [ChallanController::class, 'signzyViewChallan'])->name('challan.schallan');

    Route::post('data/schallandata', [ChallanController::class, 'signzyChallanPostData'])->name('data.schallandata');
    
        Route::get('/rc', function () {
            return view('rc.rc');
        });

        Route::get('/rc_bulk', function () {
            return view('rc.rc_bulk');
        });
        
        Route::post('authbridgeRCBulk.Data', [BulkUploadController::class, 'authbridgeRCBulkData'])->name('authbridgeRCBulk.Data');

        Route::post('vehicles/data', [RcdetailsController::class, 'retrieveVehicleData'])->name('vehicles.data'); 
        Route::get('/rc_chassis', [RcdetailsController::class, 'invincibleViewRCWithChassis'])->name('rc.rc_chassis');
        Route::post('rcWithChassisPostData', [RcdetailsController::class, 'invincibleRCWithChassisPostData'])->name('rc.rcWithChassisPostData');
        Route::get('/rc_sign', [RcdetailsController::class, 'signzyViewRC'])->name('rc.rc_sign');
        Route::post('rcSignPostData', [RcdetailsController::class, 'signzyRCPostData'])->name('rc.rcSignPostData');

        Route::get('/rc_auth', [RcdetailsController::class, 'authbridgeViewRC'])->name('rc.rc_auth');
        Route::post('rcPostData', [RcdetailsController::class, 'rcPostData'])->name('rc.rcPostData');
		Route::post('downloadPDF', [RcdetailsController::class, 'downloadPDF_RC'])->name('rc.downloadPDF');

                
        Route::get('/rc_bulk_upload', function () {
            return view('rc.rc_bulk_upload');
        })->name('rc.rc_bulk_upload');
        
        Route::post('rcAuthBulk', [RcBulkUploadController::class, 'authbridgeRCBulkData'])->name('rcAuthBulk.postData');


        Route::get('/rc_bulk_upload_logic', function () {
            return view('rc.rc_bulk_upload_logic');
        })->name('rc.rc_bulk_upload_logic');
        
        Route::post('rcAuthBulkLogic', [RcBulkUploadController::class, 'authbridgeRCBulkLogicData'])->name('rcAuthBulkLogic.postData');
        
        

        Route::get('/rcbulkreport_logic', function () {
            return view('rc.rcbulkreport_logic');
        })->name('rc.rcbulkreport_logic');

        Route::post('rcbulkreport_logic.list', [RcBulkUploadController::class, 'rcBulkReportLogicList'])->name('rcbulkreport_logic.list');


        
        //License////////////   
        Route::post('licensesignzy/data', [LicenseController::class, 'retrieveSignzyLicenseData'])->name('licensesignzy.data');

        Route::get('/license', function () {
            return view('license.drv_license');
        });

        Route::get('/drvlicense', function () {
            return view('license.drvdigilicense');
        });

        Route::get('/challan_auth', [ChallanController::class, 'authbridgeViewChallan'])->name('challan.challan_auth');
        Route::post('challan/challanAuthPostData', [ChallanController::class, 'authbridgeChallanPostData'])->name('challan.challanAuthPostData');

        Route::get('/challan_chassis', [ChallanController::class, 'invincibleViewChallanWithChassis'])->name('challan.challan_chassis');

        Route::post('challan/challanWithChassisPostData', [ChallanController::class, 'invincibleChallanWithChassisPostData'])->name('challan.challanWithChassisPostData');

        Route::post('challan/data', [ChallanController::class, 'retrieveChallanData'])->name('challan.data');
    // Add more routes here
        Route::post('signzy/rc', [SignzyController::class, 'getRC'])->name('signzy.rc');
       
        Route::get('/rc_bulk', function () {
            return view('rc.rc_bulk');
        }); 

        Route::get('/rc_dl_auth', function () {
            return view('rc.rc_dl_auth');
        }); 

        Route::post('license/licensedrv', [LicenseController::class, 'Licensedigitapdldata'])->name('license.licensedrv');
        Route::post('license/licensedlauth', [LicenseController::class, 'LicenseAuthbridgedldata'])->name('license.licensedlauth');
        
        Route::get('/challan_rto', function () {
            return view('challan.challan_rto');
        });

        Route::get('/challan', function () {
                return view('challan.challan_data');
            });

        Route::post('challanrto/data', [ChallanController::class, 'retrieveChallanRtoData'])->name('challanrto.data');


        Route::get('/rc_chassis_rto', function () {
            return view('rc.rc_chassis_rto');
        })->name('rc.rc_chassis_rto');
        
        Route::post('rcChassisRTOPostdata', [RcdetailsController::class, 'rtoRCWithChassisPostData'])->name('rc.rcChassisRTOPostdata');

        // Route::post('download.pdfchallan', [ChallanController::class, 'Challanpdf'])->name('download.pdfchallan');

        Route::post('rc/downloadChallanPDF', [ChallanController::class, 'Challanpdf'])->name('rc.downloadChallanPDF');
        
		
	    Route::get('/ocr_auth', [OcrController::class, 'authbridgeViewOCR'])->name('ocr.ocr_auth');
        Route::post('ocrPostData', [OcrController::class, 'authbridgeOCRPostData'])->name('ocr.ocrPostData');
		
		Route::get('/pancard', [PanController::class, 'authbridgeViewPancard'])->name('pancard.pancard');
        Route::post('pancardPostData', [PanController::class, 'authbridgePancardPostData'])->name('pancard.pancardPostData');

});	
     
 Route::middleware(['auth.admin'])->group(function () {
        Route::get('/users', [UsersController::class, 'index'])->name('users.index');
        Route::post('users/list', [UsersController::class, 'getUserList'])->name('users.list');
        Route::get('/users/create', [UsersController::class, 'create'])->name('users.create');
        Route::post('/users', [UsersController::class, 'store'])->name('users.store');
        Route::get('/users/{users}/edit', [UsersController::class, 'edit'])->name('users.edit');
        Route::put('/users/{users}', [UsersController::class, 'update'])->name('users.update');
        Route::post('/users/{users}/status', [UsersController::class, 'updateStatus'])->name('users.updateStatus');
        Route::delete('/users/{users}', [UsersController::class, 'destroy'])->name('users.destroy');
        Route::get('/users/client_list', [UsersController::class, 'getClientList'])->name('users.client_list');
        
        Route::post('company/details', [UsersController::class, 'getClientDetails'])->name('users.get_client_details');

        Route::post('api/data', [ModuleController::class, 'getApiData'])->name('api.data');
        Route::post('dashboard/list', [DashboardController::class, 'getDashboardList'])->name('dashboard.list');

        Route::get('/report', function () {
            return view('report.report');
        });
        Route::get('/report', function () {
            return view('report.report');
        });
        Route::post('report/list', [ReportController::class, 'getReportList'])->name('report.list');

        Route::get('/dashboardreport', function () {
            return view('dashboardreport.dashboardreport');
        });

        Route::post('dashboardreport/list', [DashboardreportController::class, 'getDashBoardReportList'])->name('dashboardreport.list');

        
        Route::get('/bi_dashboard', function () {
            return view('bi_dashboard');
        });
        
        Route::post('/rcbulkreport/{id}/recall', [RcBulkUploadController::class, 'reCallBulk'])->name('rcbulkreport.recall');

        Route::get('/summarybillingreport', function () {
            return view('billingreport.summarybillingreport');
        });
    
        Route::get('/vendorbillingreport', function () {
            return view('billingreport.vendorbillingreport');
        });

        Route::get('/apireport', function () {
            return view('apireport.apireport');
        });

        Route::get('/apisummaryreport', function () {
            return view('billingreport.apisummaryreport');
        });

        Route::get('csv/apibillingreport', [BillingController::class, 'getApiSummaryReportCsv'])->name('csv.apibillingreport');

        Route::post('apireport/csv', [BillingController::class, 'getapiReportCsv'])->name('apireport.csv');
    
        Route::post('summarybillingreport/csv', [BillingController::class, 'getSummaryBillingReportCsv'])->name('summarybillingreport.csv');
    
        Route::post('vendorbillingreport/csv', [BillingController::class, 'getVendorBillingReportCsv'])->name('vendorbillingreport.csv');
    
    });

 Route::middleware(['auth.superadmin'])->group(function () {
    /////////////created by gaurav for api_module(module) and Client(company) and rc, challan ////////////////////////
        Route::resource('/company', CompanyController::class);
        Route::resource('/module', ModuleController::class);
        Route::get('/module/delete/{id}', [ModuleController::class, 'delete'])->name('module.delete');
        Route::get('/company/delete/{id}', [CompanyController::class, 'delete'])->name('company.delete');
        Route::post('module/list', [ModuleController::class, 'getModuleList'])->name('module.list');
        Route::post('company/list', [CompanyController::class, 'getCompanyList'])->name('company.list');
        Route::get('/modules', [CompanyController::class, 'getModules'])->name('company.modules');
        Route::get('/primary_vendors', [CompanyController::class, 'getPrimaryVendors'])->name('company.primary_vendors');
        Route::get('/secondar_vendors', [CompanyController::class, 'getSecondaryVendors'])->name('company.secondary_vendors');
        Route::get('/remove_modules', [CompanyController::class, 'removeModule'])->name('company.remove_modules');
        

        Route::get('/billingreport', function () {
            return view('billingreport.modulebillingreport');
        });

        Route::get('/summarybillingreport', function () {
            return view('billingreport.summarybillingreport');
        });

        Route::get('/vendorbillingreport', function () {
            return view('billingreport.vendorbillingreport');
        });

        Route::post('modulebillingreport/csv', [BillingController::class, 'getBillingReportCsv'])->name('modulebillingreport.csv');

        Route::post('summarybillingreport/csv', [BillingController::class, 'getSummaryBillingReportCsv'])->name('summarybillingreport.csv');

        Route::post('vendorbillingreport/csv', [BillingController::class, 'getVendorBillingReportCsv'])->name('vendorbillingreport.csv');

        ////vendor billing.//////////

 });

// Forgot Password Routes
Route::get('password/reset_req/{id}', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.req');
Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

Route::get('low_credit_alert', [CommonController::class, 'lowCreditAlert']);

Route::get('availableBalance', [LoginController::class, 'availableBalance'])->name('header.balance');

Route::post('notification/data', [LoginController::class, 'getNotificationData'])->name('notification.data');

Route::post('notification/change', [LoginController::class, 'getNotificationChange'])->name('notification.change');

Route::post('organization/names', [DashboardreportController::class, 'getOrganizationNames'])->name('organization.names');

Route::post('organizationadmin/names', [DashboardreportController::class, 'getOrganizationAdminNames'])->name('organizationadmin.names');

Route::post('user/names', [DashboardreportController::class, 'getUserNames'])->name('user.names');

Route::post('dashboardreport/csv', [DashboardreportController::class, 'getDashboardReportCsv'])->name('dashboardreport.csv');

Route::post('report/csv', [ReportController::class, 'getLoginActivityReportCsv'])->name('loginActivity.csv');

Route::post('organization/names', [ReportController::class, 'getOrganizationNames'])->name('organization.names');

Route::post('user/names', [ReportController::class, 'getUserNames'])->name('user.names');

Route::get('cronBulkProcess', [BulkCronController::class, 'processBulkData'])->name('cronBulkProcess');
// Route::get('resetBulkProcessFlag', [CronController::class, 'resetBulkProcessFlag'])->name('resetBulkProcessFlag');
Route::get('resetBulkProcessFlag', [BulkCronController::class, 'resetBulkProcessFlag'])->name('resetBulkProcessFlag'); 


Route::middleware(['auth.admin'])->group(function () {

    Route::get('/summarybillingreport', function () {
        return view('billingreport.summarybillingreport');
    });

    Route::get('/vendorbillingreport', function () {
        return view('billingreport.vendorbillingreport');
    });

    Route::post('summarybillingreport/csv', [BillingController::class, 'getSummaryBillingReportCsv'])->name('summarybillingreport.csv');

    Route::post('vendorbillingreport/csv', [BillingController::class, 'getVendorBillingReportCsv'])->name('vendorbillingreport.csv');

});

///////////////////End routs for users module 


