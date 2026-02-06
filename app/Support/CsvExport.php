<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExport
{
    private string $filename = 'export.csv';

    private string $delimiter = ',';

    /** @var array<string, string|callable> */
    private array $columns;

    /**
     * @param  array<string, string|callable>  $columns  Map of 'Display Name' => 'column_key' or callable
     */
    public function __construct(array $columns)
    {
        $this->columns = $columns;
    }

    public function filename(string $filename): static
    {
        $this->filename = $filename;

        return $this;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function delimiter(string $delimiter): static
    {
        $this->delimiter = $delimiter;

        return $this;
    }

    public function fromQuery(Builder $query): StreamedResponse
    {
        return $this->stream($query->cursor());
    }

    public function fromCollection(iterable $items): StreamedResponse
    {
        return $this->stream($items);
    }

    private function stream(iterable $items): StreamedResponse
    {
        return response()->streamDownload(function () use ($items) {
            $stream = fopen('php://output', 'w');
            $this->writeToStream($items, $stream);
            fclose($stream);
        }, $this->filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Write CSV data to a stream resource (used by both streaming and tests).
     *
     * @param  resource  $stream
     */
    public function writeToStream(iterable $items, $stream): void
    {
        // UTF-8 BOM for Excel compatibility
        fwrite($stream, "\xEF\xBB\xBF");

        // Header row
        fputcsv($stream, array_keys($this->columns), $this->delimiter, '"');

        // Data rows
        foreach ($items as $item) {
            $row = [];
            foreach ($this->columns as $accessor) {
                $value = is_callable($accessor)
                    ? $accessor($item)
                    : $this->resolveValue($item, $accessor);

                $row[] = $this->sanitize($value);
            }
            fputcsv($stream, $row, $this->delimiter, '"');
        }
    }

    private function resolveValue(mixed $item, string $key): ?string
    {
        if (is_array($item)) {
            return $item[$key] ?? null;
        }

        return $item->{$key} ?? null;
    }

    private function sanitize(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        // CSV injection protection: prefix cells starting with = + - @ with tab
        if (preg_match('/^[=+\-@]/', $value)) {
            return "\t".$value;
        }

        return $value;
    }
}
