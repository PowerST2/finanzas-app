<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\RecurringRule;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RecurringRuleController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Recurring/Index', [
            'rules' => RecurringRule::with(['wallet', 'category'])->where('user_id', $request->user()->id)->latest()->get(),
            'wallets' => Wallet::where('user_id', $request->user()->id)->where('is_active', true)->orderBy('name')->get(),
            'categories' => Category::where('user_id', $request->user()->id)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'wallet_id' => ['required', 'integer'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'type' => ['required', 'in:income,expense'],
            'frequency' => ['required', 'in:daily,weekly,monthly'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'description' => ['required', 'string', 'max:200'],
            'next_at' => ['required', 'date'],
        ]);
        $data['user_id'] = $request->user()->id;
        RecurringRule::create($data);

        return back();
    }
}
