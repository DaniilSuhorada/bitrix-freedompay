<?php

namespace FreedomPay;

use Bitrix\Sale\Order;
use CSalePaySystemAction;
use CSaleStatus;
use Exception;

class Helper
{
    public const PAYMENT_SYSTEM_NAME = 'FreedomPay';
    public const PAYMENT_SYSTEM_ID = 'freedompay.pay';

    public const ORDER_ID_TYPE_ARRAY = [
        'ORDER_ID'     => ['NAME' => 'Id заказа'],
        'ORDER_NUMBER' => ['NAME' => 'Номер заказа']
    ];

    public const OFD_VERSION_ARRAY = [
        'old_ru_1_05' => ['NAME' => 'Старая версия ФФД 1.05'],
        'ru_1_05'     => ['NAME' => 'ФФД 1.05'],
        'ru_1_2'      => ['NAME' => 'ФФД 1.2']
    ];

    public const NEW_TAX_TYPE_ARRAY = [
        'none'    => ['NAME' => 'Без НДС'],
        'vat_0'   => ['NAME' => 'НДС 0%'],
        'vat_10'  => ['NAME' => 'НДС 10%'],
        'vat_20'  => ['NAME' => 'НДС 20%'],
        'vat_110' => ['NAME' => 'НДС 10/110'],
        'vat_120' => ['NAME' => 'НДС 20/120']
    ];

    public const TAX_TYPE_ARRAY = [
        '0' => ['NAME' => 'Без налога'],
        '1' => ['NAME' => 'Ставка НДС 0%'],
        '2' => ['NAME' => 'Ставка НДС 12%'],
        '3' => ['NAME' => 'Ставка НДС 12/112'],
        '4' => ['NAME' => 'Ставка НДС 18%'],
        '5' => ['NAME' => 'Ставка НДС 18/118'],
        '6' => ['NAME' => 'Ставка НДС 10%'],
        '7' => ['NAME' => 'Ставка НДС 10/110'],
        '8' => ['NAME' => 'Ставка НДС 20%'],
        '9' => ['NAME' => 'Ставка НДС 20/12']
    ];

    public const TAXATION_SYSTEM_ARRAY = [
        'osn'                => ['NAME' => 'Общая система налогообложения'],
        'usn_income'         => ['NAME' => 'Упрощенная (УСН, доходы)'],
        'usn_income_outcome' => ['NAME' => 'Упрощенная (УСН, доходы минус расходы)'],
        'envd'               => ['NAME' => 'Единый налог на вмененный доход (ЕНВД)'],
        'esn'                => ['NAME' => 'Единый сельскохозяйственный налог (ЕСН)'],
        'patent'             => ['NAME' => 'Патентная система налогообложения']
    ];

    public const PAYMENT_METHOD_ARRAY = [
        'full_prepayment'    => ['NAME' => 'Предоплата'],
        'partial_prepayment' => ['NAME' => 'Частичная предоплата'],
        'advance'            => ['NAME' => 'Аванс'],
        'full_payment'       => ['NAME' => 'Полный расчет'],
        'partial_payment'    => ['NAME' => 'Частичный расчет и кредит'],
        'credit'             => ['NAME' => 'Передача в кредит'],
        'credit_payment'     => ['NAME' => 'Выплата по кредиту']
    ];

    public const PAYMENT_OBJECT_ARRAY = [
        'goods'                   => ['NAME' => 'Товар'],
        'excise_goods'            => ['NAME' => 'Подакцизный товар'],
        'job'                     => ['NAME' => 'Работа'],
        'service'                 => ['NAME' => 'Услуга'],
        'gambling_bet'            => ['NAME' => 'Ставка азартной игры'],
        'gambling_win'            => ['NAME' => 'Выигрыш азартной игры'],
        'lottery_ticket'          => ['NAME' => 'Лотерейный билет'],
        'lottery_win'             => ['NAME' => 'Выигрыш в лотереи'],
        'intellectual_activity'   => ['NAME' => 'Результаты интеллектуальной деятельности'],
        'payment'                 => ['NAME' => 'Платеж'],
        'agent_commission'        => ['NAME' => 'Агентское вознаграждение'],
        'payout'                  => ['NAME' => 'Выплата'],
        'another_subject'         => ['NAME' => 'Иной предмет расчета'],
        'property_right'          => ['NAME' => 'Имущественное право'],
        'non_operating_income'    => ['NAME' => 'Внереализационный доход'],
        'insurance_contributions' => ['NAME' => 'Страховые взносы'],
        'trade_collection'        => ['NAME' => 'Торговый сбор'],
        'resort_collection'       => ['NAME' => 'Курортный сбор'],
        'pledge'                  => ['NAME' => 'Залог'],
        'expense'                 => ['NAME' => 'Расход'],
        'pension_insurance_ip'    => ['NAME' => 'Взносы на обязательное пенсионное страхование ИП'],
        'pension_insurance'       => ['NAME' => 'Взносы на обязательное пенсионное страхование'],
        'health_insurance_ip'     => ['NAME' => 'Взносы на обязательное медицинское страхование ИП'],
        'health_insurance'        => ['NAME' => 'Взносы на обязательное медицинское страхование'],
        'social_insurance'        => ['NAME' => 'Взносы на обязательное социальное страхование'],
        'casino'                  => ['NAME' => 'Платеж казино'],
        'insurance_collection'    => ['NAME' => 'Страховые взносы']
    ];

    public const DELIVERY_PAYMENT_OBJECT_ARRAY = [
        'job'     => ['NAME' => 'Работа'],
        'service' => ['NAME' => 'Услуга']
    ];

    public const MEASURE_ARRAY = [
        'piece'             => ['NAME' => 'Штука'],
        'gram'              => ['NAME' => 'Грамм'],
        'kilogram'          => ['NAME' => 'Килограмм'],
        'ton'               => ['NAME' => 'Тонна'],
        'centimeter'        => ['NAME' => 'Сантиметр'],
        'decimeter'         => ['NAME' => 'Дециметр'],
        'meter'             => ['NAME' => 'Метр'],
        'square_centimeter' => ['NAME' => 'Квадратный сантиметр'],
        'square_decimeter'  => ['NAME' => 'Квадратный дециметр'],
        'square_meter'      => ['NAME' => 'Квадратный метр'],
        'milliliter'        => ['NAME' => 'Миллилитр'],
        'liter'             => ['NAME' => 'Литр'],
        'cubic_meter'       => ['NAME' => 'Кубический метр'],
        'kilowatt_hour'     => ['NAME' => 'Киловатт/час'],
        'gigacalorie'       => ['NAME' => 'Гигакалория'],
        'day'               => ['NAME' => 'Сутки'],
        'hour'              => ['NAME' => 'Час'],
        'minute'            => ['NAME' => 'Минута'],
        'second'            => ['NAME' => 'Секунда'],
        'kilobyte'          => ['NAME' => 'Килобайт'],
        'megabyte'          => ['NAME' => 'Мегабайт'],
        'gigabyte'          => ['NAME' => 'Гигабайт'],
        'terabyte'          => ['NAME' => 'Терабайт'],
        'rest'              => ['NAME' => 'Остальное'],
    ];

    public static function getScriptName(): string
    {
        $path = parse_url($_SERVER['PHP_SELF'], PHP_URL_PATH);
        $len = strlen($path);

        if ($len === 0 || '/' === $path[$len - 1]) {
            return '';
        }

        return basename($path);
    }

    public static function makeSignature($scriptName, $arrParams, $secretKey): string
    {
        unset($arrParams['pg_sig']);
        $arrParams = self::makeFlatParamsArray($arrParams, '');
        ksort($arrParams);
        array_unshift($arrParams, $scriptName);
        $arrParams[] = $secretKey;

        return md5(implode(';', $arrParams));
    }

    public static function verifySignature($signature, $scriptName, $arrParams, $secretKey): bool
    {
        return (string)$signature === self::makeSignature($scriptName, $arrParams, $secretKey);
    }

    public static function convertPhoneNumber($phoneNumber): string
    {
        preg_match_all('/\d/', @$phoneNumber, $array);

        return implode('', $array[0]);
    }

    public static function emailIsValid($email): bool|int
    {
        return preg_match('/^[\w.+-]+@[\w.-]+\.\w{2,}$/', $email);
    }

    public static function getPaymentSystemSettings($paymentSystemId): array
    {
        $shop = CSalePaySystemAction::GetList('', ['PAY_SYSTEM_ID' => $paymentSystemId]);
        $shopArray = $shop->Fetch();

        if (!empty($shopArray)) {
            $settings = unserialize($shopArray['PARAMS']);
        } else {
            self::makeResponse(
                'error',
                'Error on getting payment settings'
            );
        }

        return $settings;
    }

    public static function getOrderIdFromResult(): int
    {
        $request = self::getRequest();
        $settings = self::getPaymentSystemSettings($request['PAYMENT_SYSTEM']);

        if ($settings['ORDER_ID_TYPE']['VALUE'] === 'ORDER_NUMBER') {
            try {
                $order = Order::loadByAccountNumber($request['pg_order_id']);
            } catch (Exception $exception) {
                self::makeResponse(
                    'error',
                    $exception->getMessage()
                );
            }

            return (int)$order->getId();
        }

        return (int)$request['pg_order_id'];
    }

    public static function getSaleStatusList(): array
    {
        $orderStatuses = CSaleStatus::GetList([], ['LID' => LANGUAGE_ID]);
        $statuses = [];

        while ($status = $orderStatuses->Fetch()) {
            $statuses[] = $status;
        }

        $statusIdAndNameArray = [];

        foreach ($statuses as $status) {
            $statusIdAndNameArray[$status['ID']] = [
                'NAME' => $status['NAME']
            ];
        }

        return $statusIdAndNameArray;
    }

    public static function getRequest(): ?array
    {
        global $HTTP_RAW_POST_DATA;

        if (isset($_POST['pg_xml'])) {
            $request['pg_xml'] = $_POST['pg_xml'];
        } elseif (isset($_POST['pg_sig'])) {
            $request = $_POST;
        } elseif (isset($_GET['pg_sig'])) {
            $request = $_GET;
        } elseif (!empty($HTTP_RAW_POST_DATA)) {
            $request['pg_xml'] = $HTTP_RAW_POST_DATA;
        } elseif (($HTTP_RAW_POST_DATA = file_get_contents('php://input'))) {
            $request['pg_xml'] = $HTTP_RAW_POST_DATA;
        } else {
            return null;
        }

        return $request;
    }

    public static function makeResponse(string $status, string $description): void
    {
        global $APPLICATION;
        $APPLICATION->RestartBuffer();

        header('Content-Type: text/xml');
        header('Pragma: no-cache');

        $response = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
        $response .= '<response>';
        $response .= "<pg_status>$status</pg_status>";
        $response .= "<pg_description>$description</pg_description>";
        $response .= '</response>';

        echo $response;

        exit();
    }

    private static function makeFlatParamsArray($arrParams, $parentName = ''): array
    {
        $arrFlatParams = [];
        $i = 0;

        foreach ($arrParams as $key => $value) {
            $i++;
            $name = $parentName . $key . sprintf('%03d', $i);

            if (is_array($value)) {
                $arrFlatParams = array_merge(
                    $arrFlatParams,
                    self::makeFlatParamsArray($value, $name)
                );

                continue;
            }

            $arrFlatParams += [$name => (string)$value];
        }

        return $arrFlatParams;
    }
}
