<?php

namespace App\Services;

use App\Dtos\CreateTransactionDto;
use App\Dtos\Dashboard\DashboardBalancesDto;
use App\Dtos\Dashboard\FailedTransactionsCountDto;
use App\Dtos\Dashboard\MerchantTransactionDto;
use App\Dtos\Dashboard\MerchantTransactionsListDto;
use App\Dtos\Dashboard\PaymentMethodShareDto;
use App\Dtos\Dashboard\PaymentMethodShareListDto;
use App\Dtos\Dashboard\RecentTransactionDto;
use App\Dtos\Dashboard\RecentTransactionsListDto;
use App\Dtos\Dashboard\TransactionNotificationDto;
use App\Dtos\Dashboard\TransactionNotificationsListDto;
use App\Dtos\Dashboard\TransactionsCountDto;
use App\Factories\TransactionFactory;
use App\Factory\PaymentMethodFactory;
use App\Enums\TransactionStatus;
use App\Enums\PaymentMethod;
use App\Models\Merchant;
use App\Models\Notification;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;

class TransactionService
{
    public function __construct(
        private PaymentMethodFactory $paymentMethodFactory,
        private TransactionFactory $transactionFactory,
    ) {
    }

    public function createTransaction(array $transactionBody, Merchant $merchant): ?CreateTransactionDto
    {
        Log::info('[SERVICE][CREATE-TRANSACTION][START] Received create payment request', [
            'paymentMethod' => $transactionBody['paymentMethod'],
            'transactionBody' => $transactionBody,
            'merchant_id' => $merchant->id,
            'apiKey' => $merchant->api_key
        ]);

        $paymentService = $this->paymentMethodFactory->getInstanceByPaymentMethod(PaymentMethod::tryFrom($transactionBody['paymentMethod']));
        $createTransactionDto = $paymentService->create($transactionBody);

        if ($createTransactionDto === null) {
            Log::error('[SERVICE][CREATE-TRANSACTION][ERROR] Payment service returned null for transaction creation', [
                'paymentMethod' => $transactionBody['paymentMethod']
            ]);
            return null;
        }

        $transaction = $this->transactionFactory->make();
        $transaction->transaction_uuid = $createTransactionDto->uuid;
        $transaction->transaction_id = $createTransactionDto->transactionId;
        $transaction->merchant_id = $merchant->id;
        $transaction->amount = $createTransactionDto->amount;
        $transaction->name = $createTransactionDto->name;
        $transaction->email = $createTransactionDto->email;
        $transaction->currency = $createTransactionDto->currency;
        $transaction->status = TransactionStatus::PENDING;
        $transaction->notification_url = $createTransactionDto->notificationUrl;
        $transaction->return_url = $createTransactionDto->returnUrl;
        $transaction->payment_method = $createTransactionDto->paymentMethod;

        if (!$transaction->save()) {
            Log::error('[SERVICE][CREATE-TRANSACTION][ERROR] Transaction not created', [
                'paymentMethod' => $transactionBody['paymentMethod']
            ]);
            return null;
        }

        Log::info('[SERVICE][CREATE-TRANSACTION][COMPLETED] Transaction is waiting for confirmation', [
            'paymentMethod' => $transactionBody['paymentMethod'],
            'transactionUuid' => $createTransactionDto->uuid,
        ]);

        return $createTransactionDto;
    }

    public function getMerchantTransactions(int $merchantId): MerchantTransactionsListDto
    {
        $transactions = Transaction::where('merchant_id', $merchantId)
            ->get()
            ->map(fn($t) => MerchantTransactionDto::fromModel($t))
            ->all();

        return new MerchantTransactionsListDto($transactions);
    }

    public function getTransactionNotifications(int $merchantId): TransactionNotificationsListDto
    {
        $notifications = Notification::with('transaction')
            ->whereHas('transaction', fn($q) => $q->where('merchant_id', $merchantId))
            ->get()
            ->map(fn(Notification $n) => TransactionNotificationDto::fromModel($n))
            ->all();

        return new TransactionNotificationsListDto($notifications);
    }

    public function getRecentTransactions(int $merchantId): RecentTransactionsListDto
    {
        $transactions = Transaction::where('merchant_id', $merchantId)
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn(Transaction $t) => RecentTransactionDto::fromModel($t))
            ->all();

        return new RecentTransactionsListDto($transactions);
    }

    public function getFailedCount(int $merchantId): FailedTransactionsCountDto
    {
        $transactions = Transaction::where('merchant_id', $merchantId)->get();

        return new FailedTransactionsCountDto(
            $transactions->where('status', TransactionStatus::FAIL)->count(),
        );
    }

    public function getTransactionsBalances(int $merchantId): DashboardBalancesDto
    {
        $sums = Transaction::where('merchant_id', $merchantId)
            ->selectRaw("
                SUM(CASE WHEN currency = 'PLN' THEN amount ELSE 0 END) as pln,
                SUM(CASE WHEN currency = 'EUR' THEN amount ELSE 0 END) as eur,
                SUM(CASE WHEN currency = 'USD' THEN amount ELSE 0 END) as usd")
            ->first();

        return new DashboardBalancesDto(
            pln: $sums->pln ?? 0,
            eur: $sums->eur ?? 0,
            usd: $sums->usd ?? 0
        );
    }

    public function getTotalCount(int $merchantId): TransactionsCountDto
    {
        $total = Transaction::where('merchant_id', $merchantId)->count();
        return new TransactionsCountDto($total);
    }

    public function getPaymentMethodShare(int $merchantId): PaymentMethodShareListDto
    {
        $rows = Transaction::where('merchant_id', $merchantId)
            ->selectRaw('payment_method, COUNT(*) as count')
            ->groupBy('payment_method')
            ->get();

        $total = $rows->sum('count');

        if ($total === 0) {
            return new PaymentMethodShareListDto([]);
        }

        $shares = $rows
            ->map(fn($row) => PaymentMethodShareDto::fromRow(
                $row->payment_method,
                (int) $row->getAttribute('count'),
                $total
            ))
            ->all();
        return new PaymentMethodShareListDto($shares);
    }
}
