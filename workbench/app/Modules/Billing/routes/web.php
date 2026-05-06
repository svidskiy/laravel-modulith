<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Workbench\App\Modules\Billing\Http\Controllers\InvoiceController;

Route::get('/billing', [InvoiceController::class, 'index']);
