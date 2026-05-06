<?php

declare(strict_types=1);

namespace Workbench\App\Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;

final class Invoice extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = ['number', 'amount'];
}
