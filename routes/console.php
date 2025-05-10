<?php

use App\Enums\TransactionStatus;
use App\Jobs\ProcessWebhookJob;
use App\Models\Notification;
use App\Models\Transaction;
use App\Services\PaynowRefundStatusService;
use Illuminate\Foundation\Console\ClosureCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


Artisan::command('inspire', function () {
    /** @var ClosureCommand $this */
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Schedule::call(function () {

    $maxFailedAttemps = 10;

    $notifications = Notification::join('transactions as t', 'notifications.transaction_id', '=', 't.id')
        ->select('notifications.transaction_id', 'notifications.status', 'notifications.type_status', DB::raw('count(*) as counts'))
        ->where('t.created_at', '>=', Carbon::now()->subHours(1))
        ->whereColumn('t.status', 'notifications.type_status')
        ->groupBy('notifications.transaction_id', 'notifications.status', 'notifications.type_status')
        ->having('notifications.status', '=', TransactionStatus::FAIL->value)
        ->havingRaw('count(*) < ?', [$maxFailedAttemps])
        ->get();

    $transactions = $notifications->map(function ($notification) {
        return $notification->transaction;
    });

    foreach ($transactions as $transaction) {
        ProcessWebhookJob::dispatch($transaction);
    }

})->everyMinute();


Schedule::call(function () {

    $refundTransactions = Transaction::where('status', TransactionStatus::REFUND_PENDING)
        ->where('payment_method', 'PAYNOW')
        ->get();

    foreach ($refundTransactions as $refundTransaction) {

        $status = PaynowRefundStatusService::getRefundPaymentStatus($refundTransaction->refund_code);

        if ($status === 'SUCCESSFUL') {
            $refundTransaction->update([
                'status' => TransactionStatus::REFUND_SUCCESS
            ]);
            ProcessWebhookJob::dispatch($refundTransaction);
        } elseif ($status === 'PENDING' || $status === 'NEW') {
            $refundTransaction->update([
                'status' => TransactionStatus::REFUND_PENDING
            ]);
            ProcessWebhookJob::dispatch($refundTransaction);
        } else {
            $refundTransaction->update([
                'status' => TransactionStatus::REFUND_FAIL
            ]);
            ProcessWebhookJob::dispatch($refundTransaction);
        }
    }

})->everyMinute();