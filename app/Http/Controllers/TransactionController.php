<?php

namespace App\Http\Controllers;

use App\Enums\TransactionStatus;
use App\Factory\PaymentMethodFactory;
use App\Jobs\ProcessWebhookJob;
use App\Models\Transaction;
use App\Services\ValidatorService;
use Illuminate\Http\Request;
use Log;
use Exception;

class TransactionController extends Controller
{

    private $validator;

    public function __construct(ValidatorService $validator)
    {
        $this->validator = $validator;
    }

    public function createTransaction(Request $request)
    {   

        if($request->isMethod('post'))
        {
            $transactionBody = $request->all();
            $paymentService = PaymentMethodFactory::getInstanceByPaymentMethod($transactionBody['payment_method']);
            $createTransactionDto = $paymentService->create($transactionBody);
            
            $transactionBodyRequestValidator = $this->validator->validate($request->all());
            
            if($transactionBodyRequestValidator->fails())
            {
                return response()->json(['error' => $transactionBodyRequestValidator->errors(),422]);
            }

            try
            {
                $transaction = new Transaction();
                $transaction->transaction_uuid = $createTransactionDto->getUuid();
                $transaction->transactions_id = $createTransactionDto->getTransactionId();
                $transaction->amount = $createTransactionDto->getAmount();
                $transaction->name = $createTransactionDto->getName();
                $transaction->email = $createTransactionDto->getEmail();
                $transaction->currency = $createTransactionDto->getCurrency();
                $transaction->status = TransactionStatus::PENDING;
                $transaction->notification_url = $transactionBody['notification_url'];
                $transaction->payment_method = $transactionBody['payment_method'];
                $transaction->save();

                return response()->json(['link' => $createTransactionDto->getLink()]);
            }
            catch (Exception $e) {
                Log::error('Błąd bazy danych: ' . $e->getMessage());
                return response()->json(['error' => 'Wystąpił błąd bazy danych'], 500);
            }
        }
    }

    public function confirmTransaction(Request $request)
    {
        
        $webHookBody = $request->getContent();
        $headers = $request->header();

        $paymentSevice = PaymentMethodFactory::getInstanceByPaymentMethod($request->query('payment_method'));
        $confirmTransactionDto = $paymentSevice->confirm($webHookBody,$headers);

        if($confirmTransactionDto->getStatus() === TransactionStatus::SUCCESS)
        {
            Transaction::where('transaction_uuid',$confirmTransactionDto->getRemotedCode())->update([
                'status' => $confirmTransactionDto->isCompleted() ? TransactionStatus::SUCCESS : TransactionStatus::FAIL
            ]);
            $transaction = Transaction::where('transaction_uuid',$confirmTransactionDto->getRemotedCode())->first();
            ProcessWebhookJob::dispatch($transaction);
            return response($confirmTransactionDto->getResponseBody(),200);
        }
        else{
            return response('',402);
        }
    }

}