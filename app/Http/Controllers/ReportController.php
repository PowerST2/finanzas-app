<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Loan;
use App\Models\MonthlyClosing;
use App\Models\Transaction;
use App\Support\Currency;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function monthly(Request $request)
    {
        $month = $request->query('month', now()->format('Y-m'));
        $user = $request->user();
        $userId = $user->id;
        $display = $user->currency ?: 'PEN';
        $rows = Transaction::with(['wallet', 'category'])->where('user_id', $userId)->where('month', $month)->where('status', 'confirmed')->get();
        $incomeTypes = ['income', 'loan_received', 'loan_collection'];
        $expenseTypes = ['expense', 'loan_payment', 'loan_given'];

        return Inertia::render('Reports/Monthly', [
            'month' => $month,
            'currency' => $display,
            'income' => (float) $rows->whereIn('type', $incomeTypes)->sum(fn ($tx) => Currency::convert((float) $tx->amount, $tx->wallet?->currency, $display)),
            'expense' => (float) $rows->whereIn('type', $expenseTypes)->sum(fn ($tx) => Currency::convert((float) $tx->amount, $tx->wallet?->currency, $display)),
            'trend' => Transaction::where('user_id', $userId)->where('status', 'confirmed')->where('month', '>=', now()->subMonths(5)->format('Y-m'))
                ->selectRaw("month, sum(case when type in ('income','loan_received','loan_collection') then amount else 0 end) as income, sum(case when type in ('expense','loan_payment','loan_given') then amount else 0 end) as expense")
                ->groupBy('month')->orderBy('month')->get(),
            'byCategory' => $rows->filter(fn ($tx) => $tx->category)->groupBy('category_id')->map(fn ($group) => [
                'name' => $group->first()->category->name,
                'type' => $group->first()->category->type,
                'amount' => $group->sum(fn ($tx) => Currency::convert((float) $tx->amount, $tx->wallet?->currency, $display)),
            ])->sortByDesc('amount')->values(),
            'debt' => (float) Loan::with('wallet')->where('user_id', $userId)->where('kind', 'borrowed')->where('status', 'active')->get()->sum(fn ($loan) => Currency::convert((float) $loan->current_balance, $loan->wallet?->currency, $display)),
            'receivable' => (float) Loan::with('wallet')->where('user_id', $userId)->where('kind', 'lent')->where('status', 'active')->get()->sum(fn ($loan) => Currency::convert((float) $loan->current_balance, $loan->wallet?->currency, $display)),
            'closing' => MonthlyClosing::where('user_id', $userId)->where('month', $month)->first(),
        ]);
    }

    public function csv(Request $request): StreamedResponse
    {
        $month = $request->query('month', now()->format('Y-m'));
        $rows = Transaction::with(['wallet', 'category'])->where('user_id', $request->user()->id)->where('month', $month)->orderBy('date')->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['fecha_hora', 'tipo', 'billetera', 'categoria', 'monto', 'moneda', 'monto_original', 'moneda_original', 'descripcion']);
            foreach ($rows as $row) {
                fputcsv($out, [$row->date->format('d/m/y - H:i'), $row->type, $row->wallet->name, $row->category?->name, $row->amount, $row->wallet?->currency, $row->original_amount, $row->original_currency, $row->description]);
            }
            fclose($out);
        }, "movimientos-$month.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function close(Request $request)
    {
        $month = $request->validate(['month' => ['required', 'date_format:Y-m']])['month'];
        $user = $request->user();
        $userId = $user->id;
        $display = $user->currency ?: 'PEN';
        $rows = Transaction::with('wallet')->where('user_id', $userId)->where('month', $month)->where('status', 'confirmed')->get();
        $income = (float) $rows->whereIn('type', ['income', 'loan_received', 'loan_collection'])->sum(fn ($tx) => Currency::convert((float) $tx->amount, $tx->wallet?->currency, $display));
        $expense = (float) $rows->whereIn('type', ['expense', 'loan_payment', 'loan_given'])->sum(fn ($tx) => Currency::convert((float) $tx->amount, $tx->wallet?->currency, $display));

        MonthlyClosing::updateOrCreate(['user_id' => $userId, 'month' => $month], [
            'income' => $income,
            'expense' => $expense,
            'balance' => $income - $expense,
        ]);

        return back();
    }

    public function print(Request $request)
    {
        $month = $request->query('month', now()->format('Y-m'));
        $rows = Transaction::with(['wallet', 'category'])->where('user_id', $request->user()->id)->where('month', $month)->orderBy('date')->get();

        return response()->view('reports.monthly-print', compact('month', 'rows'));
    }
}
