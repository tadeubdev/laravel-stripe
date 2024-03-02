<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WebhookStripeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Cashier\Cashier;
use Stripe\Order;

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

Route::group(['middleware' => ['auth', 'verified']], function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/checkout', function (Request $request) {
        $stripePriceId = 'price_1Oq0HJFKiAfAzS3fhm8QYCAo';
        return $request->user()
            ->newSubscription('default', $stripePriceId)
            ->trialDays(5)
            ->allowPromotionCodes()
            ->checkout([
                'success_url' => route('checkout-success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('checkout-cancel'),
            ]);
    })->name('checkout');

});

Route::post('webhook/stripe', WebhookStripeController::class);

Route::get('/checkout/success', function (Request $request) {
    $sessionId = $request->get('session_id');

    if ($sessionId === null) {
        return 'Invalid session_id.';
    }

    $session = Cashier::stripe()->checkout->sessions->retrieve($sessionId);

    if ($session->payment_status !== 'paid') {
        return 'Payment not completed.';
    }

    //$orderId = $session->metadata->order_id;
    //
    //$order = Order::retrieve($orderId);
    //$order->status = 'paid';
    //$order->save();

    return view('checkout.success');
})->name('checkout-success');

Route::view('checkout/cancel', 'checkout.cancel')->name('checkout-cancel');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
