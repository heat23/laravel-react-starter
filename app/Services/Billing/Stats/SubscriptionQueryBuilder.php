<?php

namespace App\Services\Billing\Stats;

use App\Enums\PlanTier;
use App\Helpers\QueryHelper;
use App\Services\BillingService;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SubscriptionQueryBuilder
{
    public function __construct(
        private BillingService $billingService,
    ) {}

    /**
     * @param  array{search?: string, status?: string, tier?: string, sort?: string, dir?: string}  $validated
     */
    public function paginated(array $validated): LengthAwarePaginator
    {
        $query = $this->build($validated);
        $billingService = $this->billingService;

        return $query->paginate(config('pagination.admin.users', 25))->through(fn ($row) => [
            'id' => $row->id,
            'user_id' => $row->user_id,
            'user_name' => $row->user_name,
            'user_email' => $row->user_email,
            'stripe_status' => $row->stripe_status,
            'tier' => PlanTier::safeValue($billingService->resolveTierFromPrice($row->item_price)),
            'quantity' => $row->quantity,
            'trial_ends_at' => $row->trial_ends_at,
            'ends_at' => $row->ends_at,
            'created_at' => $row->created_at,
        ]);
    }

    /**
     * @param  array{search?: string, status?: string, tier?: string, sort?: string, dir?: string}  $validated
     */
    public function build(array $validated): Builder
    {
        $firstItem = DB::table('subscription_items')
            ->select('subscription_id', 'stripe_price')
            ->whereIn('id', function ($sub) {
                // MIN() is ANSI SQL — works on MySQL and SQLite.
                $sub->from('subscription_items')
                    ->select(DB::raw('MIN(id)'))
                    ->groupBy('subscription_id');
            });

        $query = DB::table('subscriptions')
            ->leftJoin('users', function ($join) {
                $join->on('subscriptions.user_id', '=', 'users.id')
                    ->whereNull('users.deleted_at');
            })
            ->leftJoinSub($firstItem, 'first_item', 'subscriptions.id', '=', 'first_item.subscription_id')
            ->select(
                'subscriptions.id',
                'subscriptions.user_id',
                'subscriptions.stripe_id',
                'subscriptions.stripe_status',
                'subscriptions.quantity',
                'subscriptions.trial_ends_at',
                'subscriptions.ends_at',
                'subscriptions.created_at',
                QueryHelper::coalesceExpr('users.name', '[Deleted User]', 'user_name'),
                QueryHelper::coalesceExpr('users.email', '', 'user_email'),
                'first_item.stripe_price as item_price',
            );

        if (! empty($validated['search'])) {
            $query->where(function ($q) use ($validated) {
                QueryHelper::whereLike($q, 'users.name', $validated['search']);
                QueryHelper::whereLike($q, 'users.email', $validated['search'], 'or');
                QueryHelper::whereLike($q, 'subscriptions.stripe_id', $validated['search'], 'or');
            });
        }

        if (! empty($validated['status'])) {
            $query->where('subscriptions.stripe_status', $validated['status']);
        }

        if (! empty($validated['tier'])) {
            $tier = $validated['tier'];
            $monthlyPrice = config("plans.{$tier}.stripe_price_monthly");
            $annualPrice = config("plans.{$tier}.stripe_price_annual");
            $query->where(function ($q) use ($monthlyPrice, $annualPrice) {
                $q->where('first_item.stripe_price', $monthlyPrice);
                if ($annualPrice) {
                    $q->orWhere('first_item.stripe_price', $annualPrice);
                }
            });
        }

        $sortMap = [
            'created_at' => 'subscriptions.created_at',
            'stripe_status' => 'subscriptions.stripe_status',
            'quantity' => 'subscriptions.quantity',
            'user_name' => 'users.name',
        ];
        $sort = $sortMap[$validated['sort'] ?? 'created_at'] ?? 'subscriptions.created_at';
        $dir = $validated['dir'] ?? 'desc';
        $query->orderBy($sort, $dir);

        return $query;
    }
}
