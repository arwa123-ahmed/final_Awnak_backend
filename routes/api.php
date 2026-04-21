    <?php

    use App\Models\User;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\Auth\RegisterController;
    use App\Http\Controllers\ServiceController;
    use App\Http\Controllers\CategoryController;
    use App\Http\Controllers\ChatbotController;
    use App\Http\Controllers\Controller;
    use App\Http\Controllers\ServiceMatchController;
    use App\Http\Controllers\RatingController;
    use App\Http\Controllers\ReportController;
    use App\Http\Controllers\RechargeBalanceController;
    use App\Http\Controllers\Admin\ServiceManagementController;
    use App\Http\Controllers\ChatController;
    use App\Http\Controllers\ContactController;
    use App\Http\Controllers\ProgrammingController;
    use App\Http\Controllers\NotificationController;
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
    Route::post('/logout', [RegisterController::class, 'logout'])->middleware("auth:sanctum");
    Route::put('/profile', [RegisterController::class, 'update'])->middleware("auth:sanctum");
    //contact
    Route::post('/contact', [ContactController::class, 'send']);
    Route::middleware('auth:sanctum')->group(function () {
        //تبعات عمار
        Route::post('/update/role', [RegisterController::class, 'updateRole']);
        Route::post('/update/user', [RegisterController::class, 'updateUser']);



        // نعرض نضيف نعدل نحذف خدمه
        Route::get('/services/offers/{id}', [ServiceController::class, 'showOffers']);
        Route::get('/services/requests/{id}', [ServiceController::class, 'showRequests']);

        Route::get('/services', [ServiceController::class, 'index']);
        Route::get('/services/{id}', [ServiceController::class, 'show']);
        Route::post('/services', [ServiceController::class, 'create']);
        Route::put('/services/{id}', [ServiceController::class, 'update']);
        Route::delete('/services/{id}', [ServiceController::class, 'destroy']);


        Route::get('/my-offers', [ServiceController::class, 'myOffers']);
        Route::get('/my-requests', [ServiceController::class, 'myRequests']);
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
        // Route::put('/service-matches/{id}/update-status-volunteer', [ServiceMatchController::class, 'updateStatusByVolunteer']);
        // Route::put('/service-matches/{id}/update-status-Customer', [ServiceMatchController::class, 'updateStatusByCustomer']);
        //بحالة خلص المتطوع قبل نهايه الوقت

        // Route::put('/orderFinished/{id}', [ServiceMatchController::class, 'orderFinished']);
        //  جديد - بيستخدم ProgrammingController


        // بحالة انو المتطوع يتأخر
        Route::post('/service-match/{id}/volunteer-delay', [ServiceMatchController::class, 'volunteerDelay']);
        //service done
        Route::put('/service-matches/{id}/update-status-volunteer', [ProgrammingController::class, 'updateStatusByVolunteer']);
        Route::put('/service-matches/{id}/update-status-Customer', [ProgrammingController::class, 'updateStatusByCustomer']);
        Route::put('/orderFinished/{id}', [ProgrammingController::class, 'orderFinished']);
        // Route::put('/orderFinished/{id}', [ProgrammingController::class, 'orderFinished']);
        Route::post('/service-matches/{id}/done', [ProgrammingController::class, 'orderFinished']); // ✅ أضف السطر ده

        // Route::post('/service-matches/{id}/done', [ServiceMatchController::class, 'orderFinished']);
        // Done...... Section Category Delivary !!!!!!!!
        //rating route
        Route::post('/ratings/{servicematch_id}', [RatingController::class, 'store']);
        //report route
        // Route::post('/report/{id}', [ReportController::class, 'store']);
        // Route::post('/reports/{servicematch_id}', [ReportController.class, 'store']);
        Route::post('/reports/{servicematch_id}', [App\Http\Controllers\ReportController::class, 'store']);

        Route::post('/recharge-balance', [RechargeBalanceController::class, 'store']);
        Route::post('/moneyTransfer/{id}', [ServiceMatchController::class, 'moneyTransfer']);
        //عرض الطلبات والعروض والكاتيجوري مع تسجيل

        Route::get('/chat/{match_id}/messages', [ChatController::class, 'getMessages']);
        Route::post('/chat/{match_id}/messages', [ChatController::class, 'sendMessage']);
        Route::put('/chat/{match_id}/done', [ChatController::class, 'markDone']);
        Route::post('/inquiry/{volunteer_id}', [ChatController::class, 'startInquiry']);
        // notification
        Route::get('/my-matches', [ServiceMatchController::class, 'myMatchRequests']);

        //balance
        Route::get('/balance', [RegisterController::class, 'getBalance']);
    });

    Route::get('/categories/filter', [CategoryController::class, 'showCategory']);
    Route::get('/categories', [CategoryController::class, 'index']);

    // chatbot
    Route::post('/chatbot', [ChatbotController::class, 'reply']);


    use App\Http\Controllers\Admin\UserManagementController;

    // Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    //     Route::get('/users', [UserManagementController::class, 'index']);
    //     Route::delete('/users/{id}', [UserManagementController::class, 'destroy']);
    //     Route::post('/users/{id}/suspend', [UserManagementController::class, 'suspend']);
    //     Route::post('/users/{id}/unsuspend', [UserManagementController::class, 'unsuspend']);
    // });


    // Route::middleware('auth:sanctum')->prefix('admin')->group(function () {

    //     // USERS
    //     Route::get('/users', [UserManagementController::class, 'index']);
    //     Route::delete('/users/{id}', [UserManagementController::class, 'destroy']);

    //     Route::post('/users/{id}/suspend', [UserManagementController::class, 'suspend']);
    //     Route::post('/users/{id}/unsuspend', [UserManagementController::class, 'unsuspend']);

    //     Route::post('/users/{id}/update-national-id', [UserManagementController::class, 'updateNationalId']);
    //     // activation
    //     Route::post('/users/{id}/activation', [UserManagementController::class, 'toggleActivation']);

    //     // SERVICES
    //     Route::get('/services', [ServiceManagementController::class, 'index']);
    //     Route::delete('/services/{id}', [ServiceManagementController::class, 'destroy']);
    //     //Recharge balance (a)
    //      Route::get('/recharges', [RechargeBalanceController::class, 'index']);
    // Route::post('/recharges/{id}/approve', [RechargeBalanceController::class, 'approve']);
    // Route::post('/recharges/{id}/reject', [RechargeBalanceController::class, 'reject']);
    // });
    Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
        // USERS
        Route::get('/users', [UserManagementController::class, 'index']);
        Route::delete('/users/{id}', [UserManagementController::class, 'destroy']);
        Route::post('/users/{id}/suspend', [UserManagementController::class, 'suspend']);
        Route::post('/users/{id}/unsuspend', [UserManagementController::class, 'unsuspend']);
        Route::post('/users/{id}/update-national-id', [UserManagementController::class, 'updateNationalId']);
        Route::post('/users/{id}/activation', [UserManagementController::class, 'toggleActivation']);

        // SERVICES
        Route::get('/services', [ServiceManagementController::class, 'index']);
        Route::delete('/services/{id}', [ServiceManagementController::class, 'destroy']);

        // RECHARGES
        Route::get('/recharges', [RechargeBalanceController::class, 'index']);
        Route::post('/recharges/{id}/approve', [RechargeBalanceController::class, 'approve']);
        Route::post('/recharges/{id}/reject', [RechargeBalanceController::class, 'reject']);
    });


    // Route::middleware('auth:sanctum')->group(function () {

    //     // Chat
    //     Route::get('/chat/{matchId}/messages', [ChatController::class, 'getMessages']);
    //     Route::post('/chat/{matchId}/messages', [ChatController::class, 'sendMessage']);
    //     Route::put('/chat/{matchId}/done', [ChatController::class, 'markDone']);

    //     //
    //     Route::get('/my-matches', [ServiceMatchController::class, 'myMatches']);
    // });
    Route::middleware('auth:sanctum')->get('/profile/{id}', [RegisterController::class, 'show']);

    Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
        // Reports Routes
        // Route::get('/reports', [App\Http\Controllers\Admin\ReportManagementController::class, 'index']);
        Route::get('/reports', [ReportController::class, 'index']);
Route::post('/users/{id}/action', [UserManagementController::class, 'toggleSuspend']);

        // شيلنا /admin/ اللي في الأول عشان هي موجودة في الـ prefix
        // Route::post('/users/{id}/action', [App\Http\Controllers\Admin\ReportManagementController::class, 'handleAction']);

        Route::post('/users/{id}/suspend-with-message', [App\Http\Controllers\Admin\ReportManagementController::class, 'suspendUser']);
    });
