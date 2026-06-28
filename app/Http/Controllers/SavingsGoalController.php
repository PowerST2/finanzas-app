<?php

namespace App\Http\Controllers;

use App\Models\SavingsGoal;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SavingsGoalController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Goals/Index', [
            'goals' => SavingsGoal::with('wallet')->where('user_id', $request->user()->id)->latest()->get(),
            'wallets' => Wallet::where('user_id', $request->user()->id)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'wallet_id' => ['nullable', 'integer'],
            'name' => ['required', 'string', 'max:120'],
            'target_amount' => ['required', 'numeric', 'gt:0'],
            'current_amount' => ['nullable', 'numeric', 'min:0'],
            'target_date' => ['nullable', 'date'],
        ]);
        $data['user_id'] = $request->user()->id;
        SavingsGoal::create($data);

        return back();
    }
}
