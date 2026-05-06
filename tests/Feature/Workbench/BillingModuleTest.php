<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Svidskiy\Modulith\Contracts\ModuleRepository;
use Workbench\App\Modules\Billing\Models\Invoice;

uses(RefreshDatabase::class);

it('discovers the Billing module via the repository', function (): void {
    $modules = resolve(ModuleRepository::class)->all();

    expect($modules)->toHaveKey('Billing');
});

it('serves the Billing module route', function (): void {
    Invoice::query()->create(['number' => 'INV-001', 'amount' => 99.00]);

    $this->getJson('/billing')
        ->assertOk()
        ->assertJsonPath('module', 'Billing')
        ->assertJsonPath('count', 1);
});
