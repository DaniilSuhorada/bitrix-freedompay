<?php

namespace FreedomPay;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\SystemException;
use CSaleBasket;
use CSalePaySystemAction;
use Bitrix\Sale\Order;
use CUser;
use JsonException;
use RuntimeException;

class Payment
{
    public const EMPTY_AMOUNT_OR_PAY_SYSTEM_ID_ERROR = 'Amount or PaySystemId is empty';
    public const USER_ID_NOT_FOUND_ERROR = 'User ID not found';
    public const EMPTY_CUSTOMER_INFO_ERROR = 'Empty customer phone and email';

    private mixed $order;
    private float $amount;
    private float $orderAmount;
    private $paySystemId;
    private array $userArray;
    private mixed $orderId;
    private ApiClient $apiClient;
    private array $requestArray;

    /**
     * @throws ArgumentNullException
     * @throws ArgumentException
     * @throws SystemException
     * @throws NotImplementedException
     */
    public function __construct()
    {
        $this->order = $this->getOrder();
        $this->orderAmount = 0;

        $paymentCollection = $this->order->getPaymentCollection();

        foreach ($paymentCollection as $payment) {
            if ($payment->getField('PAY_SYSTEM_NAME') === Helper::PAYMENT_SYSTEM_NAME) {
                $this->amount = (float)$payment->getField('SUM');
                $this->paySystemId = $payment->getField('PAY_SYSTEM_ID');
            }

            $this->orderAmount += (float)$payment->getField('SUM');
        }

        if (empty($this->amount) || empty($this->paySystemId)) {
            throw new RuntimeException(self::EMPTY_AMOUNT_OR_PAY_SYSTEM_ID_ERROR);
        }

        $this->userArray = $this->getUserArray();
        $this->orderId = $this->getOrderId();
        $this->apiClient = new ApiClient();
        $this->requestArray = $this->generatePaymentDataArray();
    }

    /**
     * @throws JsonException
     */
    public function getPaymentUrl(): string
    {
        return $this->apiClient->createPayment($this->requestArray);
    }

    /**
     * @throws SystemException
     */
    private function generatePaymentDataArray(): array
    {
        $callbackUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/' . Helper::PAYMENT_SYSTEM_ID . '/';

        $requestArray['pg_salt'] = uniqid('', true);
        $requestArray['pg_merchant_id'] = CSalePaySystemAction::GetParamValue('MERCHANT_ID');
        $requestArray['pg_order_id'] = $this->orderId;
        $requestArray['pg_amount'] = (float)number_format($this->amount, 2, '.', '');
        $requestArray['pg_currency'] = $this->order->getCurrency();
        $requestArray['pg_description'] = 'Order ID: ' . $this->orderId;
        $requestArray['pg_user_ip'] = $_SERVER['REMOTE_ADDR'];
        $requestArray['pg_result_url'] = $callbackUrl . "result.php?PAYMENT_SYSTEM=$this->paySystemId";
        $requestArray['pg_request_method'] = 'POST';
        $requestArray['pg_success_url'] = $callbackUrl . "callback.php?PAYMENT_SYSTEM=$this->paySystemId";
        $requestArray['pg_refund_url'] = $callbackUrl . "refund.php?PAYMENT_SYSTEM=$this->paySystemId";
        $requestArray['pg_success_url_method'] = 'AUTOPOST';
        $requestArray['pg_failure_url'] = $callbackUrl . "callback.php?PAYMENT_SYSTEM=$this->paySystemId";
        $requestArray['pg_failure_url_method'] = 'AUTOPOST';

        $isTestingMode = CSalePaySystemAction::GetParamValue('TESTING_MODE') === 'Y' ? 1 : 0;

        if ($isTestingMode) {
            $requestArray['pg_testing_mode'] = $isTestingMode;
        }

        $email = $this->getEmail();

        if (!empty($email) && Helper::emailIsValid($email)) {
            $requestArray['pg_user_contact_email'] = $email;
            $requestArray['pg_user_email'] = $email;
        }

        $phoneNumber = $this->getPhoneNumber();

        if (!empty($phoneNumber)) {
            $requestArray['pg_user_phone'] = $phoneNumber;
        }

        if (CSalePaySystemAction::GetParamValue('OFD') === 'Y') {
            $ofdVersion = CSalePaySystemAction::GetParamValue('OFD_VERSION');

            if ($ofdVersion === 'old_ru_1_05' || empty($ofdVersion)) {
                $requestArray['pg_receipt_positions'] = $this->getReceiptPositionsForDeprecatedOfd();
            } else {
                $requestArray['pg_receipt'] = $this->getReceipt();
            }
        }

        $requestArray['pg_sig'] = Helper::makeSignature(
            'init_payment.php',
            $requestArray,
            CSalePaySystemAction::GetParamValue('SECRET_KEY')
        );

        return $requestArray;
    }

    private function getEmail()
    {
        $email = $this->userArray['EMAIL'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
            !empty($_POST['NEW_EMAIL']) &&
            trim($_POST['SET_NEW_USER_DATA']) !== '') {
            $email = $_POST['NEW_EMAIL'];
        }

        if (isset($GLOBALS['SALE_INPUT_PARAMS']['PROPERTY']['EMAIL'])) {
            $email = $GLOBALS['SALE_INPUT_PARAMS']['PROPERTY']['EMAIL'];
        }

        return $email;
    }

    private function getPhoneNumber(): string
    {
        $userPhone = '';

        if (!empty($this->userArray['PERSONAL_MOBILE'])) {
            $userPhone = $this->userArray['PERSONAL_MOBILE'];
        } elseif (!empty($this->userArray['PERSONAL_PHONE'])) {
            $userPhone = $this->userArray['PERSONAL_PHONE'];
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
            !empty($_POST['NEW_PHONE']) &&
            trim($_POST['SET_NEW_USER_DATA']) !== '') {
            $userPhone = $_POST['NEW_PHONE'];
        }

        return Helper::convertPhoneNumber($userPhone);
    }

    /**
     * @throws ArgumentNullException
     * @throws ArgumentException
     * @throws SystemException
     * @throws NotImplementedException
     */
    private function getOrder()
    {
        $orderId = $this->getOrderId();

        if (CSalePaySystemAction::GetParamValue('ORDER_ID_TYPE') === 'ORDER_NUMBER') {
            $order = Order::loadByAccountNumber($orderId);
        } else {
            $order = Order::load($orderId);
        }

        return $order;
    }

    private function getUserArray(): array
    {
        $userId = CUser::GetByID($GLOBALS['USER']->GetID());

        if (empty($userId)) {
            throw new RuntimeException(self::USER_ID_NOT_FOUND_ERROR);
        }

        return $userId->Fetch();
    }

    private function getOrderId()
    {
        $orderId = $_GET['ORDER_ID'];

        if (empty($orderId) && !empty($GLOBALS['SALE_INPUT_PARAMS']['ORDER']['ID'])) {
            $orderId = $GLOBALS['SALE_INPUT_PARAMS']['ORDER']['ID'];
        }

        return $orderId;
    }

    /**
     * @throws SystemException
     */
    private function getReceipt(): array
    {
        $receipt = [];

        $receipt['receipt_format'] = CSalePaySystemAction::GetParamValue('OFD_VERSION');
        $receipt['operation_type'] = CSalePaySystemAction::GetParamValue('TAXATION_SYSTEM');
        $receipt['customer'] = $this->getReceiptCustomer();
        $receipt['positions'] = $this->getReceiptPositionsForNewOfd();

        return $receipt;
    }

    private function getReceiptCustomer(): array
    {
        $customer = [];

        $email = $this->getEmail();

        if (!empty($email) && Helper::emailIsValid($email)) {
            $customer['email'] = $email;
        }

        $phoneNumber = $this->getPhoneNumber();

        if (!empty($phoneNumber)) {
            $customer['phone'] = $phoneNumber;
        }

        if (empty($email) && empty($phoneNumber)) {
            throw new RuntimeException(self::EMPTY_CUSTOMER_INFO_ERROR);
        }

        return $customer;
    }

    /**
     * @throws SystemException
     */
    private function getReceiptPositionsForNewOfd(): array
    {
        $basketList = CSaleBasket::GetList([], ['ORDER_ID' => $this->orderId]);
        $receiptPositions = [];
        $taxType = CSalePaySystemAction::GetParamValue('NEW_TAX_TYPE');

        while ($itemArray = $basketList->Fetch()) {
            $receiptPosition = [
                'quantity'       => $itemArray['QUANTITY'],
                'name'           => $itemArray['NAME'],
                'vat_code'       => $taxType,
                'price'          => (float)$itemArray['PRICE'],
                'payment_method' => CSalePaySystemAction::GetParamValue('PAYMENT_METHOD'),
                'payment_object' => CSalePaySystemAction::GetParamValue('PAYMENT_OBJECT'),
            ];

            if (CSalePaySystemAction::GetParamValue('OFD_VERSION') === 'ru_1_2') {
                $receiptPosition['measure'] = CSalePaySystemAction::GetParamValue('MEASURE');
            }

            $receiptPositions[] = $receiptPosition;
        }

        $deliveryPrice = $this->order->getDeliveryPrice();
        $deliveryTaxType = CSalePaySystemAction::GetParamValue('NEW_DELIVERY_TAX_TYPE');

        if ($deliveryPrice > 0 && CSalePaySystemAction::GetParamValue('DELIVERY_IN_OFD') === 'Y') {
            $receiptPosition = [
                'quantity'       => 1,
                'name'           => GetMessage('DELIVERY'),
                'vat_code'       => $deliveryTaxType,
                'price'          => $deliveryPrice,
                'payment_method' => CSalePaySystemAction::GetParamValue('PAYMENT_METHOD'),
                'payment_object' => CSalePaySystemAction::GetParamValue('DELIVERY_PAYMENT_OBJECT'),
            ];

            if (CSalePaySystemAction::GetParamValue('OFD_VERSION') === 'ru_1_2') {
                $receiptPosition['measure'] = 'piece';
            }

            $receiptPositions[] = $receiptPosition;
        }

        if ($this->orderAmount > $this->amount) {
            $this->applyDiscount($receiptPositions);
        }

        return $receiptPositions;
    }

    /**
     * @throws SystemException
     */
    private function getReceiptPositionsForDeprecatedOfd(): array
    {
        $basketList = CSaleBasket::GetList([], ['ORDER_ID' => $this->orderId]);
        $receiptPositions = [];
        $taxType = CSalePaySystemAction::GetParamValue('TAX_TYPE');

        while ($itemArray = $basketList->Fetch()) {
            $receiptPosition = [
                'count'    => $itemArray['QUANTITY'],
                'name'     => $itemArray['NAME'],
                'tax_type' => $taxType,
                'price'    => (float)$itemArray['PRICE'],
            ];

            $receiptPositions[] = $receiptPosition;
        }

        $deliveryPrice = $this->order->getDeliveryPrice();
        $deliveryTaxType = CSalePaySystemAction::GetParamValue('DELIVERY_TAX_TYPE');

        if ($deliveryPrice > 0 && CSalePaySystemAction::GetParamValue('DELIVERY_IN_OFD') === 'Y') {
            $receiptPosition = [
                'count'    => 1,
                'name'     => GetMessage('DELIVERY'),
                'tax_type' => $deliveryTaxType,
                'price'    => $deliveryPrice,
            ];

            $receiptPositions[] = $receiptPosition;
        }

        if ($this->orderAmount > $this->amount) {
            $this->applyDiscount($receiptPositions);
        }

        return $receiptPositions;
    }

    private function applyDiscount(array &$receiptPositions): void
    {
        $discountPercentage = $this->amount / $this->orderAmount * 100;

        foreach ($receiptPositions as &$position) {
            $position['price'] = round($position['price'] * $discountPercentage) / 100;
        }
    }
}
