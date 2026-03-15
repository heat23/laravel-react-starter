<?php

namespace App\Http\Requests\Admin;

class AdminUserExportRequest extends AdminUserIndexRequest
{
    // Inherits authorization and validation rules from AdminUserIndexRequest.
    // Export uses the same filter parameters as the index page.
}
