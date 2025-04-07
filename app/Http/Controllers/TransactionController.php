<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Enums\TransactionStatus;
use App\Factory\PaymentMethodFactory;
use App\Jobs\ProcessWebhookJob;
use App\Models\Transaction;
use App\Services\CreateTransactionValidatorService;
use Illuminate\Http\Request;
use Log;
use Exception;

class TransactionController extends Controller
{

    public function __construct(private CreateTransactionValidatorService $validator){}

    public function createTransaction(Request $request)
    {   
        if($request->isMethod('post'))
        {
            $transactionBody = $request->all();
            $paymentService = PaymentMethodFactory::getInstanceByPaymentMethod(PaymentMethod::tryFrom($transactionBody['payment_method']));
            $createTransactionDto = $paymentService->create($transactionBody);
            
            $transactionBodyRequestValidator = $this->validator->validate($request->all());
            
            if($transactionBodyRequestValidator->fails())
            {
                return response()->json(['error' => $transactionBodyRequestValidator->errors(),422]);
            }

            try
            {
                $transaction = new Transaction();
                $transaction->transaction_uuid = $createTransactionDto->uuid;
                $transaction->transactions_id = $createTransactionDto->transactionId;
                $transaction->amount = $createTransactionDto->amount;
                $transaction->name = $createTransactionDto->name;
                $transaction->email = $createTransactionDto->email;
                $transaction->currency = $createTransactionDto->currency;
                $transaction->status = TransactionStatus::PENDING;
                $transaction->notification_url = $transactionBody['notification_url'];
                $transaction->payment_method = $transactionBody['payment_method'];
                $transaction->save();

                return response()->json(['link' => $createTransactionDto->link]);
            }
            catch (Exception $e) {
                return response()->json(['error' => 'Wystąpił błąd bazy danych '], 500);
            }
        }
    }

    
    public function confirmTransaction(Request $request)
    {
        $webHookBody = $request->getContent();
        $headers = $request->header();
       
        $paymentSevice = PaymentMethodFactory::getInstanceByPaymentMethod(PaymentMethod::tryFrom($request->query('payment_method')));
        $confirmTransactionDto = $paymentSevice->confirm($webHookBody,$headers);
       
        if($confirmTransactionDto->status === TransactionStatus::SUCCESS)
        {
            Transaction::where('transaction_uuid',$confirmTransactionDto->remoteCode)->update([
                'status' => $confirmTransactionDto->completed ? TransactionStatus::SUCCESS : TransactionStatus::FAIL
            ]);
            $transaction = Transaction::where('transaction_uuid',$confirmTransactionDto->remoteCode)->first();
        
            if($transaction->status == TransactionStatus::FAIL)
            {
                Log::error('Transaction failed ' . $transaction->transaction_uuid);
                ProcessWebhookJob::dispatch($transaction);
                return response()->json([
                    'message' => 'Transaction failed',
                    'transaction_uuid' => $transaction->transaction_uuid
                ], 400);
            }
            ProcessWebhookJob::dispatch($transaction);
           
            return response($confirmTransactionDto->responseBody,200);
        }
        elseif($confirmTransactionDto->status === TransactionStatus::REFUND)
        {
            return response('',200);
        }
        else
        {
            return response('',500);
        }
    }

    public function refundPayment(Request $request)
    {
        $refundBody = $request->all();
        $paymentService = PaymentMethodFactory::getInstanceByPaymentMethod(PaymentMethod::tryFrom($refundBody['payment_method']));
        $refundPaymentDto = $paymentService->refund($refundBody['transactionUuid']);

        if($refundPaymentDto->status === TransactionStatus::REFUND)
        {
            $transaction = Transaction::where('transaction_uuid', $refundBody['transactionUuid'])->first();
            $transaction->status = TransactionStatus::REFUND;
            $transaction->save();
            ProcessWebhookJob::dispatch($transaction);
        }
        else
        {
            return response('',500);
        }
    }
}