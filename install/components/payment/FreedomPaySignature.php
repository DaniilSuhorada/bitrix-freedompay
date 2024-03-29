<?php

class FreedomPaySignature
{
    /**
     * Get script name from URL (for use as parameter in self::make, self::check, etc.)
     *
     * @param string $url
     * @return string
     */
    public static function getScriptNameFromUrl($url)
    {
        $path = parse_url($url, PHP_URL_PATH);
        $len = strlen($path);

        if ($len === 0 || '/' === $path{$len - 1}) {
            return '';
        }

        return basename($path);
    }

    /**
     * Get name of currently executed script (need to check signature of incoming message using self::check)
     *
     * @return string
     */
    public static function getOurScriptName()
    {
        return self::getScriptNameFromUrl($_SERVER['PHP_SELF']);
    }

    /**
     * Creates a signature
     *
     * @param array $arrParams associative array of parameters for the signature
     * @param string $strSecretKey
     * @return string
     */
    public static function make($strScriptName, $arrParams, $strSecretKey)
    {
        return md5(self::makeSigStr($strScriptName, $arrParams, $strSecretKey));
    }

    /**
     * Verifies the signature
     *
     * @param string $signature
     * @param array $arrParams associative array of parameters for the signature
     * @param string $strSecretKey
     * @return bool
     */
    public static function check($signature, $strScriptName, $arrParams, $strSecretKey)
    {
        return (string)$signature === self::make($strScriptName, $arrParams, $strSecretKey);
    }


    /**
     * Returns a string, a hash of which coincide with the result of the make() method.
     * WARNING: This method can be used only for debugging purposes!
     *
     * @param array $arrParams associative array of parameters for the signature
     * @param string $strSecretKey
     * @return string
     */
    static function debug_only_SigStr($strScriptName, $arrParams, $strSecretKey)
    {
        return self::makeSigStr($strScriptName, $arrParams, $strSecretKey);
    }

    private static function makeSigStr($strScriptName, $arrParams, $strSecretKey)
    {
        $arrParams = self::makeFlatParamsArray($arrParams, '', true);
        unset($arrParams['pg_sig']);
        ksort($arrParams);
        array_unshift($arrParams, $strScriptName);
        $arrParams[] = $strSecretKey;

        return implode(';', $arrParams);
    }

    private static function makeFlatParamsArray($arrParams, $parent_name = '', $ignoreEmptyParams = false)
    {
        $arrFlatParams = [];
        $i = 0;

        foreach ($arrParams as $key => $val) {
            $i++;

            if ((string)$key === 'pg_sig') {
                continue;
            }

            $name = $parent_name . $key . sprintf('%03d', $i);

            if (is_array($val)) {
                $arrFlatParams = array_merge(
                    $arrFlatParams,
                    self::makeFlatParamsArray($val, $name, $ignoreEmptyParams)
                );

                continue;
            }

            $arrFlatParams += array($name => (string)$val);
        }

        return ($ignoreEmptyParams) ? self::clearArray($arrFlatParams) : $arrFlatParams;
    }

    public static function clearArray($array)
    {
        return array_filter($array, function ($v) {
            return strlen($v);
        }, ARRAY_FILTER_USE_BOTH);
    }

    /********************** singing XML ***********************/

    /**
     * make the signature for XML
     *
     * @param string|SimpleXMLElement $xml
     * @param string $strSecretKey
     * @return string
     */
    public static function makeXML($strScriptName, $xml, $strSecretKey)
    {
        $arrFlatParams = self::makeFlatParamsXML($xml);
        return self::make($strScriptName, $arrFlatParams, $strSecretKey);
    }

    /**
     * Verifies the signature of XML
     *
     * @param string|SimpleXMLElement $xml
     * @param string $strSecretKey
     * @return bool
     * @throws Exception
     */
    public static function checkXML($strScriptName, $xml, $strSecretKey)
    {
        if (!$xml instanceof SimpleXMLElement) {
            $xml = new SimpleXMLElement($xml);
        }

        $arrFlatParams = self::makeFlatParamsXML($xml);

        return self::check((string)$xml->pg_sig, $strScriptName, $arrFlatParams, $strSecretKey);
    }

    /**
     * Returns a string, a hash of which coincide with the result of the makeXML() method.
     * WARNING: This method can be used only for debugging purposes!
     *
     * @param string|SimpleXMLElement $xml
     * @param string $strSecretKey
     * @return string
     * @throws Exception
     */
    public static function debug_only_SigStrXML($strScriptName, $xml, $strSecretKey)
    {
        $arrFlatParams = self::makeFlatParamsXML($xml);

        return self::makeSigStr($strScriptName, $arrFlatParams, $strSecretKey);
    }

    /**
     * Returns flat array of XML params
     *
     * @param (string|SimpleXMLElement) $xml
     * @return array
     * @throws Exception
     */
    private static function makeFlatParamsXML($xml, $parent_name = '')
    {
        if (!$xml instanceof SimpleXMLElement) {
            $xml = new SimpleXMLElement($xml);
        }

        $arrParams = array();
        $i = 0;

        foreach ($xml->children() as $tag) {
            $i++;

            if ('pg_sig' === $tag->getName()) {
                continue;
            }

            $name = $parent_name . $tag->getName() . sprintf('%03d', $i);

            if ($tag->children()->count() > 0) {
                $arrParams = array_merge($arrParams, self::makeFlatParamsXML($tag, $name));
                continue;
            }

            $arrParams += array($name => (string)$tag);
        }

        return $arrParams;
    }
}
