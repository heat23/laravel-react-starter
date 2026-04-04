<?php

namespace App\Models;

use Carbon\Carbon;
use Laravel\Cashier\Subscription as CashierSubscription;

/**
 * @property Carbon|null $past_due_since
 */
class Subscription extends CashierSubscription
{
    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'past_due_since' => 'datetime',
        ]);
    }
}
