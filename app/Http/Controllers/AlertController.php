<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Services\FinanceService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AlertController extends Controller
{
    public function index(Request $request, FinanceService $finance)
    {
        $finance->generateAlerts($request->user()->load('wallets'));

        return Inertia::render('Alerts/Index', ['alerts' => Alert::where('user_id', $request->user()->id)->latest('triggered_at')->get()]);
    }

    public function read(Request $request, Alert $alert)
    {
        abort_unless($alert->user_id === $request->user()->id, 404);
        $alert->update(['read_at' => now()]);

        return back();
    }
}
