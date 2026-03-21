<?php

namespace App\Models;

use Laravel\Cashier\Subscription as CashierSubscription;

class Subscription extends CashierSubscription
{
    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'past_due_since' => 'datetime',
        ]);
    }
}
