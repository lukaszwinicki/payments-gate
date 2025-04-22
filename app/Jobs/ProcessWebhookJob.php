<?php

namespace App\Jobs;

use App\Enums\TransactionStatus;
use App\Models\Merchant;
use App\Models\Notification;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use GuzzleHttp\Client;
use App\Models\Transaction;

class ProcessWebhookJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public readonly Transaction $transaction)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $client = new Client();
        $merchantSecretKey = Merchant::where('id', $this->transaction->merchant_id)->first();
        $signature = hash_hmac('sha256', $this->transaction->status->value . $this->transaction->transaction_uuid, $merchantSecretKey->secret_key);
        $clientWebhookBody = [
            'signature' => $signature,
            'transaction_uuid' => $this->transaction->transaction_uuid,
            'amount' => $this->transaction->amount,
            'name' => $this->transaction->name,
            'email' => $this->transaction->email,
            'currency' => $this->transaction->currency,
            'status' => $this->transaction->status
        ];

        try {
            $response = $client->request('POST', $this->transaction->notification_url, [
                'json' => $clientWebhookBody
            ]);

            Notification::create([
                'transaction_id' => $this->transaction->id,
                'status' => $response->getStatusCode() == 200 ? TransactionStatus::SUCCESS : TransactionStatus::FAIL,
                'type_status' => $this->transaction->status
            ]);

        } catch (Exception) {

            Notification::create([
                'transaction_id' => $this->transaction->id,
                'status' => TransactionStatus::FAIL,
                'type_status' => $this->transaction->status
            ]);
        }
    }
}
