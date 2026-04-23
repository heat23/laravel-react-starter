<?php

use App\Http\Requests\Admin\AdminListRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

uses(RefreshDatabase::class);

// Concrete stub for testing the abstract base class
function makeAdminListRequest(array $data = []): AdminListRequest
{
    $stub = new class($data) extends AdminListRequest
    {
        public function __construct(private array $inputData = []) {}

        protected function allowedSorts(): array
        {
            return ['name', 'created_at', 'email'];
        }
    };

    return $stub;
}

function validateAdminList(array $data, array $allowedSorts = ['name', 'created_at', 'email']): Illuminate\Validation\Validator
{
    $stub = new class extends AdminListRequest
    {
        public array $sorts = [];

        protected function allowedSorts(): array
        {
            return $this->sorts;
        }
    };
    $stub->sorts = $allowedSorts;

    return Validator::make($data, $stub->rules());
}

it('passes with no parameters', function (): void {
    $v = validateAdminList([]);
    expect($v->fails())->toBeFalse();
});

it('accepts valid sort column', function (): void {
    $v = validateAdminList(['sort' => 'name']);
    expect($v->fails())->toBeFalse();
});

it('rejects invalid sort column', function (): void {
    $v = validateAdminList(['sort' => 'invalid_column']);
    expect($v->fails())->toBeTrue()
        ->and($v->errors()->has('sort'))->toBeTrue();
});

it('accepts valid dir values', function (string $dir): void {
    $v = validateAdminList(['dir' => $dir]);
    expect($v->fails())->toBeFalse();
})->with(['asc', 'desc']);

it('rejects invalid dir value', function (): void {
    $v = validateAdminList(['dir' => 'sideways']);
    expect($v->fails())->toBeTrue()
        ->and($v->errors()->has('dir'))->toBeTrue();
});

it('accepts per_page within 1–100 range', function (int $perPage): void {
    $v = validateAdminList(['per_page' => $perPage]);
    expect($v->fails())->toBeFalse();
})->with([1, 25, 50, 100]);

it('rejects per_page outside range', function (int $perPage): void {
    $v = validateAdminList(['per_page' => $perPage]);
    expect($v->fails())->toBeTrue()
        ->and($v->errors()->has('per_page'))->toBeTrue();
})->with([0, 101, -1]);

it('rejects search longer than 255 characters', function (): void {
    $v = validateAdminList(['search' => str_repeat('a', 256)]);
    expect($v->fails())->toBeTrue()
        ->and($v->errors()->has('search'))->toBeTrue();
});

it('authorize returns true for admin user', function (): void {
    $admin = User::factory()->create(['is_admin' => true]);
    $request = makeAdminListRequest();
    $request->setUserResolver(fn () => $admin);
    expect($request->authorize())->toBeTrue();
});

it('authorize returns false for non-admin user', function (): void {
    $user = User::factory()->create(['is_admin' => false]);
    $request = makeAdminListRequest();
    $request->setUserResolver(fn () => $user);
    expect($request->authorize())->toBeFalse();
});
