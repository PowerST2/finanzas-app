<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\FinanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class LoanController extends Controller
{
    public function index(Request $request) { return Inertia::render('Loans/Index', ['loans' => Loan::with('wallet')->where('user_id', $request->user()->id)->latest()->get()]); }

    public function create(Request $request) { return Inertia::render('Loans/Form', ['wallets' => Wallet::where('user_id', $request->user()->id)->where('is_active', true)->orderBy('name')->get()]); }

    public function store(Request $request, FinanceService $finance)
    {
        $data = $request->validate([
            'wallet_id' => ['required', 'integer'],
            'kind' => ['required', 'in:borrowed,lent'],
            'name' => ['required', 'string', 'max:120'],
            'lender_name' => ['nullable', 'string', 'max:120'],
            'principal_amount' => ['required', 'numeric', 'gt:0'],
            'interest_rate' => ['nullable', 'numeric', 'min:0'],
            'received_at' => ['required', 'date'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
        $finance->createLoan($request->user(), $data);

        return redirect()->route('loans.index');
    }

    public function show(Request $request, Loan $loan)
    {
        abort_unless($loan->user_id === $request->user()->id, 404);

        return Inertia::render('Loans/Show', ['loan' => $loan->load(['payments.transaction']), 'wallets' => Wallet::where('user_id', $request->user()->id)->where('is_active', true)->orderBy('name')->get()]);
    }

    public function suspend(Request $request, Loan $loan)
    {
        abort_unless($loan->user_id === $request->user()->id, 404);
        if ($loan->status === 'active') {
            $loan->update(['status' => 'suspended']);
        }

        return back();
    }

    public function resume(Request $request, Loan $loan)
    {
        abort_unless($loan->user_id === $request->user()->id, 404);
        if ($loan->status === 'suspended') {
            $loan->update(['status' => 'active']);
        }

        return back();
    }

    public function destroy(Request $request, Loan $loan, FinanceService $finance)
    {
        abort_unless($loan->user_id === $request->user()->id, 404);

        DB::transaction(function () use ($loan, $finance) {
            $wallets = Wallet::whereIn('id', Transaction::where('loan_id', $loan->id)->pluck('wallet_id'))->get();
            Transaction::where('loan_id', $loan->id)->delete();
            $loan->delete();
            $wallets->each(fn (Wallet $wallet) => $finance->refreshWallet($wallet));
        });

        return redirect()->route('loans.index');
    }

    public function pay(Request $request, Loan $loan, FinanceService $finance)
    {
        $data = $request->validate([
            'wallet_id' => ['required', 'integer'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'paid_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);
        $finance->payLoan($request->user(), $loan, $data);

        return redirect()->route('loans.show', $loan);
    }
}
