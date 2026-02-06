<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth'])->group(function() {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/purchases', [\App\Http\Controllers\PurchaseController::class, 'index'])
    ->name('purchases.index')
    ->middleware(['role:admin,store_helper']);

Route::get('/purchases/items', [\App\Http\Controllers\PurchaseController::class, 'itemsIndex'])
    ->name('purchases.items.index')
    ->middleware(['auth', 'role:admin']);

Route::get('/purchases/create', [\App\Http\Controllers\PurchaseController::class, 'create'])
    ->name('purchases.create')
    ->middleware(['role:admin,store_helper']);

Route::post('/purchases', [\App\Http\Controllers\PurchaseController::class, 'store'])
    ->name('purchases.store')
    ->middleware(['role:admin,store_helper']);

Route::get('/purchases/{purchase}', [\App\Http\Controllers\PurchaseController::class, 'show'])
    ->name('purchases.show')
    ->whereNumber('purchase')
    ->middleware(['role:admin,store_helper']);

    Route::get('/issues', [\App\Http\Controllers\IssueController::class, 'index'])
    ->name('issues.index')
    ->middleware(['role:admin,store_helper']);

Route::get('/issues/create', [\App\Http\Controllers\IssueController::class, 'create'])
    ->name('issues.create')
    ->middleware(['role:admin,store_helper']);

Route::post('/issues', [\App\Http\Controllers\IssueController::class, 'store'])
    ->name('issues.store')
    ->middleware(['role:admin,store_helper']);

Route::get('/issues/{issue}', [\App\Http\Controllers\IssueController::class, 'show'])
    ->name('issues.show')
    ->whereNumber('issue')
    ->middleware(['role:admin,store_helper']);


    Route::get('/stock', [\App\Http\Controllers\StockController::class, 'index'])
    ->name('stock.index')
    ->middleware(['role:admin']);

    // Returns (Industrial mode)
    // Manual returns are disabled. Only:
    // i) Issue Return (inward) against a specific Issue
    // ii) Purchase Return (outward) against a specific Purchase
    Route::get('/returns', [\App\Http\Controllers\ReturnsHomeController::class,'index'])
        ->name('returns.index')
        ->middleware(['role:admin,store_helper']);

    Route::get('/returns/issue', [\App\Http\Controllers\IssueReturnController::class,'index'])
        ->name('returns.issue.index')
        ->middleware(['role:admin,store_helper']);
    Route::get('/returns/issue/create', [\App\Http\Controllers\IssueReturnController::class,'create'])
        ->name('returns.issue.create')
        ->middleware(['role:admin,store_helper']);
    Route::post('/returns/issue', [\App\Http\Controllers\IssueReturnController::class,'store'])
        ->name('returns.issue.store')
        ->middleware(['role:admin,store_helper']);

    Route::get('/returns/purchase', [\App\Http\Controllers\PurchaseReturnController::class,'index'])
        ->name('returns.purchase.index')
        ->middleware(['role:admin']);
    Route::get('/returns/purchase/create', [\App\Http\Controllers\PurchaseReturnController::class,'create'])
        ->name('returns.purchase.create')
        ->middleware(['role:admin']);
    Route::post('/returns/purchase', [\App\Http\Controllers\PurchaseReturnController::class,'store'])
        ->name('returns.purchase.store')
        ->middleware(['role:admin']);

    Route::get('/settings', [\App\Http\Controllers\SettingsController::class, 'index'])
    ->name('settings.index')
    ->middleware(['auth','role:admin']);

    Route::post('/settings', [\App\Http\Controllers\SettingsController::class, 'update'])
    ->name('settings.update')
    ->middleware(['auth','role:admin']);

    Route::get('/backup', [\App\Http\Controllers\BackupController::class,'index'])
    ->name('backup.index')
    ->middleware(['auth','role:admin']);

    Route::post('/backup/settings', [\App\Http\Controllers\BackupController::class,'updateSettings'])
    ->name('backup.settings.update')
    ->middleware(['auth','role:admin']);

    Route::post('/backup/manual', [\App\Http\Controllers\BackupController::class,'manualBackup'])
    ->name('backup.manual')
    ->middleware(['auth','role:admin']);

    Route::get('/backup/download/latest', [\App\Http\Controllers\BackupController::class,'downloadLatest'])
    ->name('backup.download.latest')
    ->middleware(['auth','role:admin']);

    Route::get('/backup/download/{filename}', [\App\Http\Controllers\BackupController::class,'download'])
    ->name('backup.download')
    ->where('filename', '^[A-Za-z0-9_\-\.]+$')
    ->middleware(['auth','role:admin']);

    Route::post('/backup/restore', [\App\Http\Controllers\BackupController::class,'restore'])
    ->name('backup.restore')
    ->middleware(['auth','role:admin']);



// Admin only: Groups and Items
    Route::middleware(['role:admin'])->group(function () {
    Route::get('/groups', [\App\Http\Controllers\GroupController::class, 'index'])->name('groups.index');
    Route::get('/groups/create', [\App\Http\Controllers\GroupController::class, 'create'])->name('groups.create');
    Route::post('/groups', [\App\Http\Controllers\GroupController::class, 'store'])->name('groups.store');
    Route::get('/groups/{group}/edit', [\App\Http\Controllers\GroupController::class, 'edit'])->name('groups.edit');
    Route::put('/groups/{group}', [\App\Http\Controllers\GroupController::class, 'update'])->name('groups.update');
    Route::delete('/groups/{group}', [\App\Http\Controllers\GroupController::class, 'destroy'])->name('groups.destroy');

    Route::get('/items', [\App\Http\Controllers\ItemController::class, 'index'])->name('items.index');
    Route::get('/items/create', [\App\Http\Controllers\ItemController::class, 'create'])->name('items.create');
    Route::post('/items', [\App\Http\Controllers\ItemController::class, 'store'])->name('items.store');
    Route::get('/items/{item}/edit', [\App\Http\Controllers\ItemController::class, 'edit'])->name('items.edit')->whereNumber('item');
    Route::put('/items/{item}', [\App\Http\Controllers\ItemController::class, 'update'])->name('items.update')->whereNumber('item');
    Route::delete('/items/{item}', [\App\Http\Controllers\ItemController::class, 'destroy'])->name('items.destroy')->whereNumber('item');
    Route::get('/items/{item}/stock', [\App\Http\Controllers\ItemStockController::class, 'show'])
    ->name('items.stock.show')
    ->whereNumber('item');

    // Print views
    Route::get('/print/purchases', [\App\Http\Controllers\ExportController::class, 'printPurchases'])->name('print.purchases');
    Route::get('/print/issues', [\App\Http\Controllers\ExportController::class, 'printIssues'])->name('print.issues');
    Route::get('/print/stock', [\App\Http\Controllers\ExportController::class, 'printStock'])->name('print.stock');
    Route::get('/print/returns', [\App\Http\Controllers\ExportController::class, 'printReturns'])->name('print.returns');

    // CSV exports
    Route::get('/export/purchases.csv', [\App\Http\Controllers\ExportController::class, 'csvPurchases'])->name('export.purchases.csv');
    Route::get('/export/issues.csv', [\App\Http\Controllers\ExportController::class, 'csvIssues'])->name('export.issues.csv');
    Route::get('/export/stock.csv', [\App\Http\Controllers\ExportController::class, 'csvStock'])->name('export.stock.csv');
    Route::get('/export/issue-returns.csv', [\App\Http\Controllers\ExportController::class, 'csvIssueReturns'])->name('export.issue_returns.csv');
    Route::get('/export/purchase-returns.csv', [\App\Http\Controllers\ExportController::class, 'csvPurchaseReturns'])->name('export.purchase_returns.csv');

    // Full history CSV (ledger-style)
    Route::get('/export/history.csv', [\App\Http\Controllers\ExportController::class, 'csvFullHistory'])->name('export.ledger.csv');

    // PDF exports
    Route::get('/export/purchases.pdf', [\App\Http\Controllers\ExportController::class, 'pdfPurchases'])->name('export.purchases.pdf');
    Route::get('/export/issues.pdf', [\App\Http\Controllers\ExportController::class, 'pdfIssues'])->name('export.issues.pdf');
    Route::get('/export/stock.pdf', [\App\Http\Controllers\ExportController::class, 'pdfStock'])->name('export.stock.pdf');
    Route::get('/export/issue-returns.pdf', [\App\Http\Controllers\ExportController::class, 'pdfIssueReturns'])->name('export.issue_returns.pdf');
    Route::get('/export/purchase-returns.pdf', [\App\Http\Controllers\ExportController::class, 'pdfPurchaseReturns'])->name('export.purchase_returns.pdf');

    Route::get('/print/item-ledger/{item}', [\App\Http\Controllers\ExportController::class, 'printItemLedger'])->name('print.item.ledger');
Route::get('/export/item-ledger/{item}.pdf', [\App\Http\Controllers\ExportController::class, 'pdfItemLedger'])->name('export.item.ledger.pdf');

    
});


    // Admin-only test route (keep for verification)
    Route::get('/admin-test', function () {
        return 'Admin OK';
    })->middleware(['role:admin']);
});

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth','role:admin'])->group(function () {
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile/update', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');

    Route::post('/profile/users', [\App\Http\Controllers\ProfileController::class, 'storeUser'])->name('profile.users.store');
    Route::post('/profile/users/{user}/reset-password', [\App\Http\Controllers\ProfileController::class, 'resetUserPassword'])->name('profile.users.reset');

    Route::post('/profile/users/{user}/update', [ProfileController::class, 'updateUser'])
    ->name('profile.users.update');

Route::post('/profile/users/{user}/delete', [ProfileController::class, 'deleteUser'])
    ->name('profile.users.delete');

});



require __DIR__.'/auth.php';
