<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminToggleAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        // This action accepts no user input. The target user is identified via the route
        // parameter only. Authorization is enforced exclusively by authorize() above.
        return [];
    }
}
