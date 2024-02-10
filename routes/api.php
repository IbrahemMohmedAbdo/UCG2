<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Sizes\SizeController;
use App\Http\Controllers\Colors\ColorController;
use App\Http\Controllers\Orders\OrderController;
use Spatie\Permission\Middleware\RoleMiddleware;
use App\Http\Controllers\Profile\ProfileController;
use App\Http\Controllers\Orders\PromoCodeController;
use App\Http\Controllers\Products\ProductController;
use App\Http\Controllers\Categories\CategoryController;
use App\Http\Controllers\Commisions\CommisionController;
use App\Http\Controllers\Statistics\StatisticsController;
use App\Http\Controllers\Permissions\PermissionController;
use App\Http\Controllers\Transactions\TransactionController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
	// profile routes
	 Route::post('createAccounts', [AuthController::class, 'registerAccounts']);
	Route::post('verifyAccount/{user_id}', [AuthController::class, 'verifyAccount']);


    Route::get('profile', [ProfileController::class, 'show']);
	Route::get('profile/users', [ProfileController::class, 'searchForUsersWithTypes']); //for admin
	Route::get('profile/users/{id}', [ProfileController::class, 'searchForUsersById']);
	Route::get('profile/{type}', [ProfileController::class, 'showUsersPerType']); //for admin
    Route::get('profile/search/{key}', [ProfileController::class, 'searchForUsers']); //for admin
	Route::post('profile/create', [ProfileController::class, 'create']);
    Route::match(['post', 'put'], 'profile/edite/{id}', [ProfileController::class, 'updateUsers']); //for admin
    Route::delete('profile/delete/{id}', [ProfileController::class, 'delete']); //for admin
	Route::patch('profile/reset-password/{id}', [ProfileController::class, 'resetPassword']); //for admin

	//Commission..
    Route::get('commissions', [CommisionController::class, 'index']);
    Route::post('createCommissions', [CommisionController::class, 'store']);
    Route::put('updateCommissions/{id}', [CommisionController::class, 'update']);

	    // permissions and roles...
    Route::get('allPermissions',[PermissionController::class, 'allPermission']);
    Route::get('allRoles',[PermissionController::class, 'allRoles']);
    Route::get('roles/{role}/permissions', [PermissionController::class, 'permissionsForRole']);
    Route::put('roles/{role}/permissions', [PermissionController::class, 'updateRolePermissions']);
	 Route::delete('roles/{role}/permissions', [PermissionController::class, 'deleteRolePermissions']);
	 Route::post('createRole', [PermissionController::class, 'createRoleWithPermissions']);
	 Route::delete('admin/roles/{roleId}', [PermissionController::class, 'deleteRole']);

    Route::get('getCategories', [CategoryController::class, 'getCategories']);
    Route::post('addCategory', [CategoryController::class, 'addCategory']);
	Route::post('updateCategory/{id}', [CategoryController::class, 'updateCategory']);
    Route::delete('categories/{id}', [CategoryController::class, 'deleteCategory']);

    Route::post('addProduct', [ProductController::class, 'addProduct']);
    Route::get('getProducts', [ProductController::class, 'getProducts']);
    Route::get('getProductById/{id}', [ProductController::class, 'getProductById']);
    Route::post('updateProduct/{id}', [ProductController::class, 'updateProduct']);
	Route::delete('Products/{id}', [ProductController::class, 'deleteProduct']);

// Order routes...
Route::get('getOrders', [OrderController::class, 'getOrders']);
Route::get('getOrderById/{id}', [OrderController::class, 'getOrder']);
Route::post('addOrder', [OrderController::class, 'store']);
Route::put('/orders/{id}/cancelled/{user_id}',[OrderController::class,'cancel_Order']);
Route::put('/orders/{order_id}/assign-driver/{driver_id}', [OrderController::class, 'assignDriver']);
Route::put('/orders/{id}/packed/{user_id}', [OrderController::class, 'packed']);
Route::put('/orders/{id}/delivered/{user_id}', [OrderController::class, 'delivered']);
Route::put('/orders/{id}/delivering/{user_id}', [OrderController::class, 'delivering']);
Route::put('/orders/{id}/returned/{user_id}', [OrderController::class, 'returned']);
Route::put('/orders/{id}/postponed/{user_id}', [OrderController::class, 'postponed']);
Route::post('qrLink/{order}', [OrderController::class, 'createQrLinkAction']);
Route::delete('Orders/{id}', [OrderController::class, 'delete_order']);

// Promo Code...
Route::post('/promocodes', [PromoCodeController::class, 'store']);
Route::delete('/promocodes/{id}', [PromoCodeController::class, 'delete']);



	// statistics routes...
    Route::get('statistics', [StatisticsController::class, 'statistics'])->name('statistics');
    Route::get('best-selling-products', [StatisticsController::class, 'BestSellingProducts'])->name('BestSellingProducts');
    Route::get('best-selling-categories', [StatisticsController::class, 'BestSellingCategories'])->name('BestSellingCategories');
    Route::get('worst-selling-products', [StatisticsController::class, 'WorstSellingProducts'])->name('WorstSellingProducts');
	Route::get('monthly/orders', [StatisticsController::class, 'monthOrderStatistics'])->name('monthOrderStatistics'); 							Route::get('user/orders', [StatisticsController::class, 'ordersForUser']);
	Route::get('user/orders/{id}', [StatisticsController::class, 'ordersForUser']);

	// wallet routes...
	Route::get('wallets', [WalletController::class, 'allWallet'])->name('wallets');
    Route::get('wallet/user', [WalletController::class, 'userWallet'])->name('user.wallet');
    Route::get('transaction/wallet/{user}', [WalletController::class, 'transferWalletFunds'])->name('driver.wallet');
	Route::get('users/wallet/{type}', [WalletController::class, 'walletByType'])->name('type.wallet');

// TransactionWallet
    Route::post('/transfer-funds', [TransactionController::class, 'transferAllFundsUser']);
    Route::post('/sub-funds', [TransactionController::class, 'subFundsDriver']);
    Route::post('/add-funds', [TransactionController::class, 'addFundsRepresintive']);
    Route::get('/transaction-history', [TransactionController::class, 'getTransactionHistory']);


// sizes and colors..

  Route::apiResource('sizes', SizeController::class);
  Route::apiResource('colors', ColorController::class);

});



Route::middleware('guest:sanctum')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});
