<?php

use App\Support\CsvExport;

it('generates correct CSV headers', function () {
    $export = new CsvExport([
        'Name' => 'name',
        'Email' => 'email',
    ]);

    $output = captureStreamOutput($export, []);

    $lines = explode("\n", trim($output));
    // First line is BOM + header (fputcsv only quotes when necessary)
    expect(str_replace("\xEF\xBB\xBF", '', $lines[0]))->toBe('Name,Email');
});

it('maps string keys to column values', function () {
    $export = new CsvExport([
        'Name' => 'name',
        'Email' => 'email',
    ]);

    $items = [
        (object) ['name' => 'John', 'email' => 'john@example.com'],
        (object) ['name' => 'Jane', 'email' => 'jane@example.com'],
    ];

    $output = captureStreamOutput($export, $items);
    $lines = explode("\n", trim($output));

    expect($lines)->toHaveCount(3); // header + 2 data rows
    expect($lines[1])->toBe('John,john@example.com');
    expect($lines[2])->toBe('Jane,jane@example.com');
});

it('maps callable accessors', function () {
    $export = new CsvExport([
        'Full Name' => fn ($row) => strtoupper($row->name),
    ]);

    $items = [(object) ['name' => 'John']];
    $output = captureStreamOutput($export, $items);
    $lines = explode("\n", trim($output));

    expect($lines[1])->toBe('JOHN');
});

it('handles empty dataset', function () {
    $export = new CsvExport([
        'Name' => 'name',
    ]);

    $output = captureStreamOutput($export, []);
    $lines = explode("\n", trim($output));

    expect($lines)->toHaveCount(1); // header only
});

it('handles null values', function () {
    $export = new CsvExport([
        'Name' => 'name',
        'Email' => 'email',
    ]);

    $items = [(object) ['name' => 'John', 'email' => null]];
    $output = captureStreamOutput($export, $items);
    $lines = explode("\n", trim($output));

    expect($lines[1])->toBe('John,');
});

it('includes UTF-8 BOM', function () {
    $export = new CsvExport(['Name' => 'name']);
    $output = captureStreamOutput($export, []);

    expect(substr($output, 0, 3))->toBe("\xEF\xBB\xBF");
});

it('supports custom filename', function () {
    $export = (new CsvExport(['Name' => 'name']))->filename('custom.csv');

    expect($export->getFilename())->toBe('custom.csv');
});

it('supports custom delimiter', function () {
    $export = (new CsvExport([
        'Name' => 'name',
        'Email' => 'email',
    ]))->delimiter(';');

    $items = [(object) ['name' => 'John', 'email' => 'john@example.com']];
    $output = captureStreamOutput($export, $items);
    $lines = explode("\n", trim($output));

    expect($lines[1])->toBe('John;john@example.com');
});

it('protects against CSV injection', function () {
    $export = new CsvExport([
        'Name' => 'name',
        'Formula' => 'formula',
    ]);

    $items = [
        (object) ['name' => 'Safe', 'formula' => '=SUM(A1:A10)'],
        (object) ['name' => 'Safe', 'formula' => '+cmd|evil'],
        (object) ['name' => 'Safe', 'formula' => '-danger'],
        (object) ['name' => 'Safe', 'formula' => '@evil'],
    ];

    $output = captureStreamOutput($export, $items);
    $lines = explode("\n", trim($output));

    // Each dangerous cell should be prefixed with a tab character
    expect($lines[1])->toContain("\t=SUM(A1:A10)");
    expect($lines[2])->toContain("\t+cmd|evil");
    expect($lines[3])->toContain("\t-danger");
    expect($lines[4])->toContain("\t@evil");
});

it('works with array items', function () {
    $export = new CsvExport([
        'Name' => 'name',
        'Email' => 'email',
    ]);

    $items = [
        ['name' => 'John', 'email' => 'john@example.com'],
    ];

    $output = captureStreamOutput($export, $items);
    $lines = explode("\n", trim($output));

    expect($lines[1])->toBe('John,john@example.com');
});

it('quotes values containing the delimiter', function () {
    $export = new CsvExport([
        'Name' => 'name',
    ]);

    $items = [(object) ['name' => 'Doe, John']];
    $output = captureStreamOutput($export, $items);
    $lines = explode("\n", trim($output));

    expect($lines[1])->toBe('"Doe, John"');
});

/**
 * Helper to capture streamed CSV output.
 */
function captureStreamOutput(CsvExport $export, iterable $items): string
{
    ob_start();
    $export->writeToStream($items, fopen('php://output', 'w'));

    return ob_get_clean();
}
