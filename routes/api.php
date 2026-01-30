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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

    // تسجيل اليوزر
    Route::post('/register', [RegisterController::class, 'register']);
    Route::post('/login', [RegisterController::class, 'login']);
    Route::post('/logout', [RegisterController::class,'logout'])->middleware("auth:sanctum");
    Route::put('/profile', [RegisterController::class,'update'])->middleware("auth:sanctum");

    Route::middleware('auth:sanctum')->group(function () {
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
   });
//عرض الطلبات والعروض والكاتيجوري بدون تسجيل 
    Route::get('/showOffers', [ServiceController::class, 'showOffers']); 
    Route::get('/showRequests', [ServiceController::class, 'showRequests']);
    Route::get('/categories', [CategoryController::class, 'index']);
