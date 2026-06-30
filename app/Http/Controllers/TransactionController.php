<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\AuditLog;
use App\Models\CurrencyOption;
use App\Models\Transaction;
use App\Models\TransactionAttachment;
use App\Models\Wallet;
use App\Services\FinanceService;
use App\Support\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'type', 'status', 'from', 'to']);
        $transactions = Transaction::with(['wallet', 'destinationWallet', 'category', 'loan', 'attachments'])
            ->where('user_id', $request->user()->id)
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('description', 'ilike', "%{$search}%")
                        ->orWhere('amount', 'like', "%{$search}%")
                        ->orWhereHas('wallet', fn ($q) => $q->where('name', 'ilike', "%{$search}%"))
                        ->orWhereHas('category', fn ($q) => $q->where('name', 'ilike', "%{$search}%"));
                });
            })
            ->when($filters['type'] ?? null, fn ($query, $type) => $query->where('type', $type))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['from'] ?? null, fn ($query, $from) => $query->whereDate('date', '>=', $from))
            ->when($filters['to'] ?? null, fn ($query, $to) => $query->whereDate('date', '<=', $to))
            ->latest('date')
            ->paginate(20)
            ->withQueryString();
        $display = $request->user()->currency ?: 'PEN';
        $transactions->getCollection()->transform(function ($transaction) use ($display) {
            $transaction->display_amount = Currency::convert((float) $transaction->amount, $transaction->wallet?->currency, $display);
            $transaction->display_currency = $display;

            return $transaction;
        });

        return Inertia::render('Transactions/Index', [
            'transactions' => $transactions,
            'filters' => $filters,
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::render('Transactions/Form', [
            'wallets' => Wallet::where('user_id', $request->user()->id)->where('is_active', true)->orderBy('name')->get(),
            'categories' => Category::where('user_id', $request->user()->id)->orderBy('name')->get(),
            'currencies' => CurrencyOption::where('is_active', true)->orderBy('code')->get(),
            'defaultType' => $request->query('type', 'expense'),
            'defaultDate' => $request->query('date'),
        ]);
    }

    public function store(Request $request, FinanceService $finance)
    {
        $data = $request->validate([
            'wallet_id' => ['required', 'integer'],
            'destination_wallet_id' => ['nullable', 'integer'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'type' => ['required', 'in:income,expense,transfer,adjustment'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'in:confirmed,pending,cancelled'],
            'currency' => ['nullable', 'exists:currency_options,code'],
            'attachment' => ['nullable', 'file', 'max:4096'],
        ]);

        $transaction = $finance->createTransaction($request->user(), $data);
        $this->storeAttachment($request, $transaction);

        return redirect()->route('transactions.index');
    }

    public function edit(Request $request, Transaction $transaction)
    {
        abort_unless($transaction->user_id === $request->user()->id, 404);

        return Inertia::render('Transactions/Form', [
            'transaction' => $transaction->load('attachments'),
            'auditLogs' => AuditLog::where('user_id', $request->user()->id)->where('auditable_type', Transaction::class)->where('auditable_id', $transaction->id)->latest()->get(),
            'wallets' => Wallet::where('user_id', $request->user()->id)->where('is_active', true)->orderBy('name')->get(),
            'categories' => Category::where('user_id', $request->user()->id)->orderBy('name')->get(),
            'currencies' => CurrencyOption::where('is_active', true)->orderBy('code')->get(),
            'defaultType' => $transaction->type,
        ]);
    }

    public function update(Request $request, Transaction $transaction, FinanceService $finance)
    {
        $data = $request->validate([
            'wallet_id' => ['required', 'integer'],
            'destination_wallet_id' => ['nullable', 'integer'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'type' => ['required', 'in:income,expense,transfer,adjustment'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'in:confirmed,pending,cancelled'],
            'currency' => ['nullable', 'exists:currency_options,code'],
            'attachment' => ['nullable', 'file', 'max:4096'],
        ]);

        $finance->updateTransaction($request->user(), $transaction, $data);
        $this->storeAttachment($request, $transaction);

        return redirect()->route('transactions.index', $request->only(['search', 'type', 'status', 'from', 'to']));
    }

    public function cancel(Request $request, Transaction $transaction, FinanceService $finance)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:500']]);
        $finance->cancelTransaction($request->user(), $transaction, $data['reason']);

        return back();
    }

    public function destroy(Request $request, Transaction $transaction, FinanceService $finance)
    {
        $finance->deleteTransaction($request->user(), $transaction);

        return back();
    }

    public function duplicate(Request $request, Transaction $transaction, FinanceService $finance)
    {
        abort_unless($transaction->user_id === $request->user()->id, 404);

        $finance->createTransaction($request->user(), [
            'wallet_id' => $transaction->wallet_id,
            'destination_wallet_id' => $transaction->destination_wallet_id,
            'category_id' => $transaction->category_id,
            'type' => $transaction->type,
            'amount' => $transaction->original_amount ?? $transaction->amount,
            'currency' => $transaction->original_currency ?? $transaction->wallet?->currency,
            'date' => now(),
            'description' => $transaction->description,
            'status' => 'confirmed',
        ]);

        return back();
    }

    public function importCreate()
    {
        return Inertia::render('Transactions/Import', [
            'wallets' => Wallet::where('user_id', request()->user()->id)->orderBy('name')->get(['id', 'name']),
            'categories' => Category::where('user_id', request()->user()->id)->orderBy('name')->get(['id', 'name', 'type']),
        ]);
    }

    public function importStore(Request $request, FinanceService $finance)
    {
        $data = $request->validate([
            'file' => ['nullable', 'file', 'mimes:csv,txt', 'max:4096'],
            'rows' => ['nullable', 'array'],
        ]);
        $created = 0;
        $rows = $data['rows'] ?? null;

        if (! $rows && isset($data['file'])) {
            $rows = array_map('str_getcsv', file($data['file']->getRealPath()));
            $headers = array_map(fn ($x) => strtolower(trim($x)), array_shift($rows) ?: []);
            $rows = collect($rows)->map(fn ($row) => array_combine($headers, $row))->filter()->values()->all();
        }

        foreach ($rows ?? [] as $row) {
            if (! $row || blank($row['wallet_id'] ?? null) || blank($row['type'] ?? null) || blank($row['amount'] ?? null)) {
                continue;
            }

            $finance->createTransaction($request->user(), [
                'wallet_id' => $row['wallet_id'],
                'category_id' => $row['category_id'] ?? null,
                'type' => $row['type'],
                'amount' => $row['amount'],
                'date' => $row['date'] ?? now(),
                'description' => $row['description'] ?? null,
                'status' => $row['status'] ?? 'confirmed',
                'currency' => $row['currency'] ?? null,
            ]);
            $created++;
        }

        return redirect()->route('transactions.index')->with('status', "Importados {$created} movimientos.");
    }

    public function attachment(Request $request, TransactionAttachment $attachment)
    {
        abort_unless($attachment->user_id === $request->user()->id, 404);

        return Storage::disk('local')->download($attachment->path, $attachment->original_name);
    }

    public function template()
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['wallet_id', 'type', 'amount', 'currency', 'date', 'description', 'category_id', 'status']);
            fputcsv($out, ['1', 'expense', '25.50', 'PEN', now()->format('Y-m-d H:i:s'), 'Ejemplo de gasto', '', 'confirmed']);
            fclose($out);
        }, 'plantilla-movimientos.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function storeAttachment(Request $request, Transaction $transaction): void
    {
        if (! $request->hasFile('attachment')) {
            return;
        }

        $file = $request->file('attachment');
        TransactionAttachment::create([
            'user_id' => $request->user()->id,
            'transaction_id' => $transaction->id,
            'original_name' => $file->getClientOriginalName(),
            'path' => $file->store('attachments'),
            'mime' => $file->getClientMimeType(),
        ]);
    }
}
