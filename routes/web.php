<?php

use App\Http\Controllers\AlertController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PendingDebtController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RecurringRuleController;
use App\Http\Controllers\SavingsGoalController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CreditCardController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WalletController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/onboarding', [OnboardingController::class, 'create'])->name('onboarding.create');
    Route::post('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');
    Route::post('/wallets/{wallet}/suspend', [WalletController::class, 'suspend'])->name('wallets.suspend');
    Route::post('/wallets/{wallet}/resume', [WalletController::class, 'resume'])->name('wallets.resume');
    Route::resource('wallets', WalletController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::get('/credit-cards', [CreditCardController::class, 'index'])->name('credit-cards.index');
    Route::post('/credit-cards/{wallet}/payments', [CreditCardController::class, 'pay'])->name('credit-cards.pay');
    Route::post('/credit-cards/{wallet}/reset-cycle', [CreditCardController::class, 'reset'])->name('credit-cards.reset');
    Route::get('/transactions/import', [TransactionController::class, 'importCreate'])->name('transactions.import');
    Route::get('/transactions/import/template', [TransactionController::class, 'template'])->name('transactions.import.template');
    Route::post('/transactions/import', [TransactionController::class, 'importStore'])->name('transactions.import.store');
    Route::post('/transactions/{transaction}/cancel', [TransactionController::class, 'cancel'])->name('transactions.cancel');
    Route::post('/transactions/{transaction}/duplicate', [TransactionController::class, 'duplicate'])->name('transactions.duplicate');
    Route::get('/transactions/attachments/{attachment}', [TransactionController::class, 'attachment'])->name('transactions.attachments.show');
    Route::resource('transactions', TransactionController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::resource('loans', LoanController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('/loans/{loan}/payments', [LoanController::class, 'pay'])->name('loans.pay');
    Route::get('/pending-debts', [PendingDebtController::class, 'index'])->name('pending-debts.index');
    Route::post('/pending-debts', [PendingDebtController::class, 'store'])->name('pending-debts.store');
    Route::post('/pending-debts/{debt}/payments', [PendingDebtController::class, 'pay'])->name('pending-debts.pay');
    Route::get('/budgets', [BudgetController::class, 'index'])->name('budgets.index');
    Route::get('/budgets/{month}', [BudgetController::class, 'show'])->name('budgets.show');
    Route::post('/budgets', [BudgetController::class, 'store'])->name('budgets.store');
    Route::get('/alerts', [AlertController::class, 'index'])->name('alerts.index');
    Route::patch('/alerts/{alert}/read', [AlertController::class, 'read'])->name('alerts.read');
    Route::get('/reports/monthly', [ReportController::class, 'monthly'])->name('reports.monthly');
    Route::get('/reports/monthly/csv', [ReportController::class, 'csv'])->name('reports.monthly.csv.simple');
    Route::get('/reports/monthly.csv', [ReportController::class, 'csv'])->name('reports.monthly.csv');
    Route::get('/reports/monthly.print', [ReportController::class, 'print'])->name('reports.monthly.print');
    Route::post('/reports/monthly/close', [ReportController::class, 'close'])->name('reports.monthly.close');
    Route::get('/calendar', CalendarController::class)->name('calendar.index');
    Route::get('/goals', [SavingsGoalController::class, 'index'])->name('goals.index');
    Route::post('/goals', [SavingsGoalController::class, 'store'])->name('goals.store');
    Route::get('/recurring', [RecurringRuleController::class, 'index'])->name('recurring.index');
    Route::post('/recurring', [RecurringRuleController::class, 'store'])->name('recurring.store');
    Route::get('/settings', [ProfileController::class, 'edit'])->name('settings');
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    Route::post('/admin/wallet-types', [AdminController::class, 'storeWalletType'])->name('admin.wallet-types.store');
    Route::patch('/admin/wallet-types/{walletType}', [AdminController::class, 'updateWalletType'])->name('admin.wallet-types.update');
    Route::post('/admin/currencies', [AdminController::class, 'storeCurrency'])->name('admin.currencies.store');
    Route::patch('/admin/currencies/{currency}', [AdminController::class, 'updateCurrency'])->name('admin.currencies.update');
    Route::patch('/admin/users/{user}', [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/visual', [ProfileController::class, 'visual'])->name('profile.visual');
    Route::post('/profile/security-question', [ProfileController::class, 'securityQuestion'])->name('profile.security-question');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
