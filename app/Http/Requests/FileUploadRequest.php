<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Base class for file upload form requests.
 *
 * Subclasses must implement authorize() and rules(), using fileRules() for
 * standard file validation. Example:
 *
 *   public function authorize(): bool { return true; }
 *   public function rules(): array { return $this->fileRules(['jpeg', 'png'], 5120); }
 */
abstract class FileUploadRequest extends FormRequest
{
    /**
     * Common file validation rules.
     *
     * @param  array<int, string>  $mimeTypes  Accepted MIME types
     * @param  int  $maxKilobytes  Maximum file size in KB
     * @return array<string, array<int, string>>
     */
    protected function fileRules(
        array $mimeTypes = ['jpeg', 'png', 'webp', 'pdf'],
        int $maxKilobytes = 10240
    ): array {
        return [
            'file' => ['required', 'file', 'mimes:'.implode(',', $mimeTypes), 'max:'.$maxKilobytes],
        ];
    }
}
