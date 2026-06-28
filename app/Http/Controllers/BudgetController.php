<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BudgetController extends Controller
{
    public function index(Request $request) { return Inertia::render('Budgets/Index', ['budgets' => Budget::where('user_id', $request->user()->id)->latest('month')->get()]); }

    public function show(Request $request, string $month)
    {
        $budget = Budget::firstOrCreate(['user_id' => $request->user()->id, 'month' => $month]);
        return Inertia::render('Budgets/Show', [
            'budget' => $budget->load('items.category'),
            'categories' => Category::where('user_id', $request->user()->id)->where('type', 'expense')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
            'total_limit' => ['nullable', 'numeric', 'min:0'],
            'saving_goal' => ['nullable', 'numeric', 'min:0'],
            'items' => ['array'],
            'items.*.category_id' => ['required', 'integer'],
            'items.*.limit_amount' => ['required', 'numeric', 'min:0'],
        ]);
        $budget = Budget::updateOrCreate(
            ['user_id' => $request->user()->id, 'month' => $data['month']],
            ['total_limit' => $data['total_limit'] ?? null, 'saving_goal' => $data['saving_goal'] ?? null]
        );
        $budget->items()->delete();
        foreach ($data['items'] ?? [] as $item) {
            $budget->items()->create($item);
        }

        return redirect()->route('budgets.show', $budget->month);
    }
}
