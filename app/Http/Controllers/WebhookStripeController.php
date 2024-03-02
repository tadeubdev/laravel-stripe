<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookStripeController extends Controller
{
    public function __invoke()
    {
        Log::log('info', 'WebhookStripeController', request()->all());
    }
}
