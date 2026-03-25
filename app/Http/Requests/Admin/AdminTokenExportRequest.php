<?php

namespace App\Http\Requests\Admin;

class AdminTokenExportRequest extends AdminTokenIndexRequest
{
    // Inherits authorization and validation rules from AdminTokenIndexRequest.
    // Export uses the same filter parameters as the index page.
}
