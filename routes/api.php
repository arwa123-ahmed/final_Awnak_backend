<?php
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ServiceMatchController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RechargeBalanceController;
use Illuminate\Support\Facades\Mail;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middle ware group. Make something great!
|
*/
Route::get('/test', function () {
    return response()->json(['message' => 'API works']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

    //define routes for registeration -->> data not send to backend until verify otp
    Route::post('/send-otp', [RegisterController::class, 'sendOtp']);
    Route::post('/verify-otp', [RegisterController::class, 'verifyOtp']);
    Route::post('/register', [RegisterController::class, 'register']);
    Route::post('/login', [RegisterController::class, 'login']);
    Route::post('/check-email', [RegisterController::class, 'checkEmail']);
    //for forgetting password
    Route::post('/forget-password', [RegisterController::class, 'forgetPassword']);
    Route::post('/reset-password', [RegisterController::class, 'resetPassword']);
 

    // تسجيل اليوزر
   // Route::post('/register', [RegisterController::class, 'register']);
   // Route::post('/login', [RegisterController::class, 'login']);
    Route::post('/logout', [RegisterController::class,'logout'])->middleware("auth:sanctum");
    Route::put('/profile', [RegisterController::class,'update'])->middleware("auth:sanctum");

    Route::middleware('auth:sanctum')->group(function () {
    //تبعات عمار 
    Route::post('/update/role', [RegisterController::class, 'updateRole']);
    Route::post('/update/user', [RegisterController::class, 'updateUser']);



    // نعرض نضيف نعدل نحذف خدمه
    Route::get('/services', [ServiceController::class, 'index']);       
    Route::get('/services/{id}', [ServiceController::class, 'show']);   
    Route::post('/services', [ServiceController::class, 'create']);     
    Route::put('/services/{id}', [ServiceController::class, 'update']); 
    Route::delete('/services/{id}', [ServiceController::class, 'destroy']); 
    Route::get('/services', [ServiceController::class, 'show_type']);
    // نضيف نحذف نعدل كاتيجوري
    Route::post('/storeCategory', [CategoryController::class, 'storeCategory']);
    Route::PUT('/editCategory/{id}', [CategoryController::class, 'editCategory']);
    Route::DELETE('/deleteCategory/{id}', [CategoryController::class, 'deleteCategory']);
    Route::get('/showCategory', [CategoryController::class, 'showCategory']);
    // طلب خدمة
    Route::post('/services/{service}/request', [ServiceMatchController::class, 'store']);
    //  عرض طلبات الفولنتير 
    Route::get('/volunteer/requests', [ServiceMatchController::class, 'volunteerRequests']);
    // عرض طلبات الكاستمر
    Route::get('/customer/requests', [ServiceMatchController::class, 'customerRequests']);

// Section Category Delivary !!!!!!!! 
    // تحديث حالة الطلب (فولنتير , كاستمر)
    Route::put('/service-matches/{id}/update-status-volunteer', [ServiceMatchController::class, 'updateStatusByVolunteer']);
    Route::put('/service-matches/{id}/update-status-Customer', [ServiceMatchController::class, 'updateStatusByCustomer']);
    //بحالة خلص المتطوع قبل نهايه الوقت
    Route::put('/orderFinished/{id}', [ServiceMatchController::class, 'orderFinished']); 
    // بحالة انو المتطوع يتأخر
    Route::post('/service-match/{id}/volunteer-delay', [ServiceMatchController::class, 'volunteerDelay']);
// Done...... Section Category Delivary !!!!!!!! 
   //rating route
   Route::post('/ratings/{servicematch_id}', [RatingController::class, 'store']);
   //report route
   Route::post('/report/{id}', [ReportController::class, 'store']);

   Route::post('/recharge-balance', [RechargeBalanceController::class, 'store']);
   Route::post('/moneyTransfer/{id}', [ServiceMatchController::class, 'moneyTransfer']);
   //عرض الطلبات والعروض والكاتيجوري مع تسجيل  
    Route::get('showOffers/{id}', [ServiceController::class, 'showOffers']); 
    Route::get('showRequests/{id}', [ServiceController::class, 'showRequests']);

   });
   
    Route::get('/categories', [CategoryController::class, 'index']);
   
