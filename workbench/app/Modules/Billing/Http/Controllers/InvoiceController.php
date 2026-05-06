<?php

declare(strict_types=1);

namespace Workbench\App\Modules\Billing\Http\Controllers;

use Illuminate\Routing\Controller;
use Workbench\App\Modules\Billing\Models\Invoice;

final class InvoiceController extends Controller
{
    /**
     * @return array{module: string, count: int, invoices: list<array<string, mixed>>}
     */
    public function index(): array
    {
        return [
            'module' => 'Billing',
            'count' => Invoice::query()->count(),
            'invoices' => Invoice::query()->get()->toArray(),
        ];
    }
}
