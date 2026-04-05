<?php

use App\Helpers\QueryHelper;
use Illuminate\Support\Facades\DB;

it('escapeLike escapes percent, underscore, and pipe characters', function () {
    expect(QueryHelper::escapeLike('test%user'))->toBe('test|%user');
    expect(QueryHelper::escapeLike('test_user'))->toBe('test|_user');
    expect(QueryHelper::escapeLike('te|st'))->toBe('te||st');
    expect(QueryHelper::escapeLike('test%_|user'))->toBe('test|%|_||user');
    expect(QueryHelper::escapeLike('no special chars'))->toBe('no special chars');
});

it('whereLike builds a parameterized LIKE with pipe escape character', function () {
    $query = DB::table('users');
    QueryHelper::whereLike($query, 'email', 'test%user');

    $sql = $query->toSql();
    $bindings = $query->getBindings();

    expect($sql)->toContain("LIKE ? ESCAPE '|'");
    expect($bindings)->toHaveCount(1);
    expect($bindings[0])->toBe('%test|%user%');
});

it('whereLike wraps the escaped value with leading and trailing wildcards', function () {
    $query = DB::table('users');
    QueryHelper::whereLike($query, 'name', 'alice');

    $bindings = $query->getBindings();

    expect($bindings[0])->toBe('%alice%');
});
