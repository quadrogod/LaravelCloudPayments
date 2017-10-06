<?php

namespace Albakov\LaravelCloudPayments;

use Illuminate\Http\Request;
use Log;

trait Notifications
{

    /**
     * Check payment
     * @param Illuminate\Http\Request $request
     * @return json
     */
    public function check(Request $request)
    {
        $data = $this->validateAll($request);
        return response()->json($data);
    }

    /**
     * Confirm payment
     * @param Illuminate\Http\Request $request
     * @return json
     */
    public function pay(Request $request)
    {

        $data = $this->validateAll($request);

        if ((int) $data['code'] === 0) {
            // payment success
            // mark order payment status - success
            // send email to admin and customer
            // ...
        }

        return response()->json($data);
    }

    /**
     * !!!FOR TEST ONLY!!!
     * Check data
     * @param Illuminate\Http\Request $request
     * @return array
     */
    public function validateAll($request)
    {
        // $secrets = $this->validateSecrets($request);

        // if ($secrets['code'] !== 0) {
        //     return $secrets;
        // }

        // // Replace $request->InvoiceId for your order id, for example:
        // // $order = Order::find($request->InvoiceId);
        // // $orderId = $order->id;
        // $orderId = $request->InvoiceId;

        // $order = $this->validateOrder($request->InvoiceId, $orderId);

        // if ($order['code'] !== 0) {
        //     return $order;
        // }

        // // Replace $request->Amount for your order amount, for example:
        // // $order->total;
        // $orderAmount = $request->Amount;

        // $amount = $this->validateAmount($request->Amount, $orderAmount);

        // if ($amount['code'] !== 0) {
        //     return $amount;
        // }

        // return ['code' => 0];

        return ['code' => 13];
    }

    /**
     * Validate Secrets
     * @return object $request
     * @return array
     */
    public function validateSecrets($request)
    {
        // Create Site Secret
        $secret = hash_hmac('sha256', file_get_contents('php://input'), config('cloudpayments.apiSecret'), true);
        $secret = base64_encode($secret);

        // Get CloudPayments secret
        $headers = apache_request_headers();
        $secretCloudPayments = isset($headers['Content-Hmac']) ? $headers['Content-Hmac'] : '';

        // Check secrets
        if (!empty($secretCloudPayments) && $secret === $secretCloudPayments) {
            return ['code' => 0];
        }

        Log::error("Secret from CloudPayments doesn\'t match Site Secret! Site secret: {$secret} and Content-Hmac: {$secretCloudPayments} Check API Secret!");
        return ['code' => 13];
    }

    /**
     * Validate order
     * @return string $invoiceId
     * @return string $orderId
     * @return array
     */
    public function validateOrder($invoiceId, $orderId)
    {

        if ((string) $invoiceId === (string) $orderId) {
            return ['code' => 0];
        }

        Log::error("Order not found! Incoming order: {$invoiceId}.");
        return ['code' => 10];
    }
    
    /**
     * Validate amount
     * @return string $amount
     * @return string|Int|Float $orderAmount
     * @return array
     */
    public function validateAmount($amount, $orderAmount)
    {
        // Prepare amount
        $orderAmount = number_format($orderAmount, 2, '.', '');

        // Check amount
        if ($orderAmount === $amount) {
            return ['code' => 0];
        }

        Log::error("Order amount doesn't match CloudPayments amount! Incoming amount: {$amount}, Order amount: {$orderAmount}");
        return ['code' => 11];
    }
}
