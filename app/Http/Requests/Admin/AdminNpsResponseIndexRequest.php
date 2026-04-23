<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class AdminNpsResponseIndexRequest extends AdminListRequest
{
    protected function allowedSorts(): array
    {
        return ['score', 'created_at'];
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'category' => ['nullable', 'string', Rule::in(['promoter', 'passive', 'detractor'])],
            'survey_trigger' => ['nullable', 'string', 'max:60'],
        ]);
    }
}
