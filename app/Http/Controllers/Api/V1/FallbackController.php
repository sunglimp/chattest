<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\BaseController;

class FallbackController extends BaseController
{
    public function notFound()
    {
        return response()->json(['status' => 404, 'message' => 'Not found'], 404);
    }
}
