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

    Route::get('/issues', [\App\Http\Controllers\IssueController::class, 'index'])
    ->name('issues.index')
    ->middleware(['role:admin,store_helper']);

    Route::get('/issues/create', [\App\Http\Controllers\IssueController::class, 'create'])
    ->name('issues.create')
    ->middleware(['role:admin,store_helper']);

    Route::post('/issues', [\App\Http\Controllers\IssueController::class, 'store'])
    ->name('issues.store')
    ->middleware(['role:admin,store_helper']);

    // Issue Return (Helper can return only issued items)
    Route::get('/issue-returns', [\App\Http\Controllers\IssueReturnController::class,'index'])
        ->name('issue-returns.index')
        ->middleware(['role:admin,store_helper']);
    Route::get('/issue-returns/create', [\App\Http\Controllers\IssueReturnController::class,'create'])
        ->name('issue-returns.create')
        ->middleware(['role:admin,store_helper']);
    Route::post('/issue-returns', [\App\Http\Controllers\IssueReturnController::class,'store'])
        ->name('issue-returns.store')
        ->middleware(['role:admin,store_helper']);
    Route::get('/issue-returns/issue/{issue}/lines', [\App\Http\Controllers\IssueReturnController::class,'issueLines'])
        ->name('issue-returns.issue-lines')
        ->middleware(['role:admin,store_helper']);

    Route::get('/stock', [\App\Http\Controllers\StockController::class, 'index'])
    ->name('stock.index')
    ->middleware(['role:admin']);

    Route::get('/returns', [\App\Http\Controllers\ReturnController::class,'index'])->name('returns.index')->middleware(['role:admin,store_helper']);
    Route::get('/returns/create', [\App\Http\Controllers\ReturnController::class,'create'])->name('returns.create')->middleware(['role:admin,store_helper']);
    Route::post('/returns', [\App\Http\Controllers\ReturnController::class,'store'])->name('returns.store')->middleware(['role:admin,store_helper']);

    Route::get('/settings', [\App\Http\Controllers\SettingsController::class, 'index'])
    ->name('settings.index')
    ->middleware(['auth','role:admin']);

    Route::post('/settings', [\App\Http\Controllers\SettingsController::class, 'update'])
    ->name('settings.update')
    ->middleware(['auth','role:admin']);

    Route::get('/backup', [\App\Http\Controllers\BackupController::class,'index'])
    ->name('backup.index')
    ->middleware(['auth','role:admin']);

    Route::post('/backup/manual', [\App\Http\Controllers\BackupController::class,'manualBackup'])
    ->name('backup.manual')
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
    Route::get('/items/{item}/edit', [\App\Http\Controllers\ItemController::class, 'edit'])->name('items.edit');
    Route::put('/items/{item}', [\App\Http\Controllers\ItemController::class, 'update'])->name('items.update');
    Route::delete('/items/{item}', [\App\Http\Controllers\ItemController::class, 'destroy'])->name('items.destroy');
    Route::get('/items/{item}/stock', [\App\Http\Controllers\ItemStockController::class, 'show'])
    ->name('items.stock.show');

    // Print views
    Route::get('/print/purchases', [\App\Http\Controllers\ExportController::class, 'printPurchases'])->name('print.purchases');
    Route::get('/print/issues', [\App\Http\Controllers\ExportController::class, 'printIssues'])->name('print.issues');
    Route::get('/print/stock', [\App\Http\Controllers\ExportController::class, 'printStock'])->name('print.stock');
    Route::get('/print/returns', [\App\Http\Controllers\ExportController::class, 'printReturns'])->name('print.returns');
    Route::get('/print/issue-returns', [\App\Http\Controllers\ExportController::class, 'printIssueReturns'])->name('print.issue-returns');

    // CSV exports
    Route::get('/export/purchases.csv', [\App\Http\Controllers\ExportController::class, 'csvPurchases'])->name('export.purchases.csv');
    Route::get('/export/issues.csv', [\App\Http\Controllers\ExportController::class, 'csvIssues'])->name('export.issues.csv');
    Route::get('/export/stock.csv', [\App\Http\Controllers\ExportController::class, 'csvStock'])->name('export.stock.csv');
    Route::get('/export/returns.csv', [\App\Http\Controllers\ExportController::class, 'csvReturns'])->name('export.returns.csv');
    Route::get('/export/issue-returns.csv', [\App\Http\Controllers\ExportController::class, 'csvIssueReturns'])->name('export.issue-returns.csv');
    Route::get('/export/issue-returns.csv', [\App\Http\Controllers\ExportController::class, 'csvIssueReturns'])->name('export.issue-returns.csv');

    // PDF exports
    Route::get('/export/purchases.pdf', [\App\Http\Controllers\ExportController::class, 'pdfPurchases'])->name('export.purchases.pdf');
    Route::get('/export/issues.pdf', [\App\Http\Controllers\ExportController::class, 'pdfIssues'])->name('export.issues.pdf');
    Route::get('/export/stock.pdf', [\App\Http\Controllers\ExportController::class, 'pdfStock'])->name('export.stock.pdf');
    Route::get('/export/returns.pdf', [\App\Http\Controllers\ExportController::class, 'pdfReturns'])->name('export.returns.pdf');
    Route::get('/export/issue-returns.pdf', [\App\Http\Controllers\ExportController::class, 'pdfIssueReturns'])->name('export.issue-returns.pdf');
    Route::get('/export/issue-returns.pdf', [\App\Http\Controllers\ExportController::class, 'pdfIssueReturns'])->name('export.issue-returns.pdf');
    Route::get('/export/issue-returns.pdf', [\App\Http\Controllers\ExportController::class, 'pdfIssueReturns'])->name('export.issue-returns.pdf');
});


    // Admin-only test route (keep for verification)
    Route::get('/admin-test', function () {
        return 'Admin OK';
    })->middleware(['role:admin']);
});

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


require __DIR__.'/auth.php';
