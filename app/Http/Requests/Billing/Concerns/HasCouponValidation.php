<?php

namespace App\Http\Requests\Billing\Concerns;

use App\Services\BillingService;
use Illuminate\Contracts\Validation\Validator;

trait HasCouponValidation
{
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $coupon = $this->input('coupon');

            if (! $coupon || $validator->errors()->has('coupon')) {
                return;
            }

            $error = app(BillingService::class)->validateCouponCode($coupon);

            if ($error !== null) {
                $validator->errors()->add('coupon', $error);
            }
        });
    }
}
