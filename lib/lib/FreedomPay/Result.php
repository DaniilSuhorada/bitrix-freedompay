<?php

namespace FreedomPay;

use Bitrix\Sale\PaymentCollection;
use CDatabase;
use CLang;
use CSaleOrder;
use CSalePaySystemAction;
use Exception;
use Bitrix\Sale\Order;

class Result
{
    private array $request;
    private array $settings;
    private int $orderId;
    private mixed $order;
    private array $arrayOrder;
    private mixed $currentPayment;
    private PaymentCollection $paymentsCollection;

    public function __construct()
    {
        $this->request = Helper::getRequest();
        $this->settings = Helper::getPaymentSystemSettings($this->request['PAYMENT_SYSTEM']);

        $this->orderId = Helper::getOrderIdFromResult();
        $this->order = $this->getOrder();
        $this->arrayOrder = $this->getArrayOrder();

        $this->paymentsCollection = $this->order->getPaymentCollection();

        foreach ($this->paymentsCollection as $payment) {
            if ($payment->getField('PAY_SYSTEM_NAME') === Helper::PAYMENT_SYSTEM_NAME) {
                $this->currentPayment = $payment;

                break;
            }
        }
    }

    public function handleResultRequest(): void
    {
        $this->checkSignature();
        $this->checkAmount();

        if ($this->request['pg_result'] == 1) {
            $this->handleSuccessfulPayment();
        } else {
            $this->handleCanceledPayment();
        }
    }

    private function getOrder()
    {
        try {
            if (CSalePaySystemAction::GetParamValue('ORDER_ID_TYPE') === 'ORDER_NUMBER') {
                $order = Order::loadByAccountNumber($this->orderId);
            } else {
                $order = Order::load($this->orderId);
            }
        } catch (Exception $exception) {
            Helper::makeResponse(
                'error',
                $exception->getMessage()
            );
        }

        if (empty($order)) {
            Helper::makeResponse(
                'error',
                'Order not found'
            );
        }

        return $order;
    }
    
    private function getArrayOrder(): array
    {
        $arrayOrder = CSaleOrder::GetByID($this->orderId);
        
        if (empty($arrayOrder)) {
            Helper::makeResponse(
                'error',
                'Array order not found'
            );
        }

        return $arrayOrder;
    }

    private function checkSignature(): void
    {
        if (!Helper::verifySignature(
            $this->request['pg_sig'],
            Helper::getScriptName(),
            $this->request,
            $this->settings['SECRET_KEY']['VALUE']
        )) {
            Helper::makeResponse(
                'error',
                'Signature is not valid'
            );
        }
    }

    private function checkAmount(): void
    {
        if ($this->request['pg_amount'] != $this->currentPayment->getField('SUM')) {
            Helper::makeResponse(
                'error',
                'Amount is not correct'
            );
        }
    }

    private function handleSuccessfulPayment(): void
    {
        $this->checkOrderPayed();

        if ($this->arrayOrder['CANCELED'] === 'Y') {
            $this->updateCanceledOrder('0');
        }

        $this->currentPayment->setPaid('Y');

        if ($this->order->getPrice() === $this->paymentsCollection->getPaidSum()) {
            $this->payOrder();
        } else {
            CSaleOrder::Update($this->orderId, [
                'SUM_PAID' => $this->paymentsCollection->getPaidSum(),
            ]);

            $this->paymentsCollection->save();

            Helper::makeResponse(
                'ok',
                'FreedomPay payment paid'
            );
        }
    }

    private function handleCanceledPayment(): void
    {
        if ($this->arrayOrder['CANCELED'] === 'Y') {
            Helper::makeResponse(
                'ok',
                'Order already canceled'
            );
        }

        if ($this->arrayOrder['PAYED'] === 'Y') {
            Helper::makeResponse(
                'ok',
                'Order already paid'
            );
        }

        $this->updateCanceledOrder('1');

        Helper::makeResponse(
            'ok',
            'Payment failed'
        );
    }

    private function checkOrderPayed(): void
    {
        if ($this->arrayOrder['PAYED'] === 'Y') {
            Helper::makeResponse(
                'error',
                'Order already payed'
            );
        }
    }

    private function updateCanceledOrder(string $psStatusCode): void
    {
        CSaleOrder::Update($this->orderId, [
            'STATUS_ID'        => $this->settings['STATUS_FAILED']['VALUE'],
            'PS_STATUS'        => $this->settings['STATUS_FAILED']['VALUE'],
            'PS_STATUS_CODE'   => $psStatusCode,
            'PS_SUM'           => $this->request['pg_amount'],
            'PS_CURRENCY'      => $this->request['pg_currency'],
            'PS_RESPONSE_DATE' => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat('FULL', LANG))),
        ]);

        Helper::makeResponse(
            'rejected',
            'Order canceled'
        );
    }

    private function payOrder(): void
    {
        if (!CSaleOrder::PayOrder($this->orderId, 'Y')) {
            Helper::makeResponse(
                'error',
                'Order can\'t be payed'
            );
        } else {
            CSaleOrder::Update($this->orderId, array(
                'SUM_PAID'  => $this->paymentsCollection->getPaidSum(),
                'STATUS_ID' => $this->settings['STATUS_PAID']['VALUE']
            ));

            $this->currentPayment->delete();

            Helper::makeResponse(
                'ok',
                'Order payed'
            );
        }
    }
}
