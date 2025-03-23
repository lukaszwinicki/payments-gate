<?php

use App\Enums\TransactionStatus;
use App\Jobs\ProcessWebhookJob;
use App\Models\Transaction;
use Illuminate\Foundation\Console\ClosureCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Carbon\Carbon;

Artisan::command('inspire', function () {
    /** @var ClosureCommand $this */
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Schedule::call(function () {
   
    $transactions = Transaction::where('created_at', '>=', Carbon::now()->subHour()) 
    ->where(function($query) {
        $query->whereHas('notifications', function($q) {
            $q->where('status', TransactionStatus::FAIL);
        }, '<', 10);  
    })
    ->where(function($query) {
        $query->whereHas('notifications', function($q) {
            $q->where('status', TransactionStatus::SUCCESS);
        }, '=', 0);  
    })
    ->get();

    foreach($transactions as $transaction)
    {
        ProcessWebhookJob::dispatch($transaction);
    }
    
})->everyMinute();