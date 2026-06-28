<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\RecurringRule;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class CalendarController extends Controller
{
    public function __invoke(Request $request)
    {
        $month = $request->query('month', now()->format('Y-m'));
        $from = "{$month}-01";
        $to = Carbon::parse($from)->endOfMonth();

        return Inertia::render('Calendar/Index', [
            'month' => $month,
            'items' => collect()
                ->merge(Loan::where('user_id', $request->user()->id)->where('status', 'active')->whereBetween('due_date', [$from, $to])->get()->map(fn ($loan) => [
                    'date' => $loan->due_date->format('Y-m-d'),
                    'title' => ($loan->kind === 'lent' ? 'Cobrar ' : 'Pagar ').$loan->name,
                    'amount' => $loan->current_balance,
                    'href' => route('loans.show', $loan),
                ]))
                ->merge(RecurringRule::where('user_id', $request->user()->id)->where('is_active', true)->whereBetween('next_at', [$from, $to])->get()->map(fn ($rule) => [
                    'date' => $rule->next_at->format('Y-m-d'),
                    'title' => 'Recurrente: '.$rule->description,
                    'amount' => $rule->amount,
                    'href' => route('recurring.index'),
                ]))
                ->sortBy('date')->values(),
        ]);
    }
}
