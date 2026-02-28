<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExportRequest;
use App\Models\User;
use App\Support\CsvExport;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function users(ExportRequest $request): StreamedResponse
    {
        $query = User::query()
            ->select(['name', 'email', 'created_at'])
            ->where('id', $request->user()->id)
            ->orderBy('name')
            ->limit(config('pagination.export.max_rows', 10000));

        if ($search = $request->validated('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return (new CsvExport([
            'Name' => 'name',
            'Email' => 'email',
            'Joined' => fn ($user) => $user->created_at?->toDateString() ?? '',
        ]))
            ->filename('users.csv')
            ->fromQuery($query);
    }
}
