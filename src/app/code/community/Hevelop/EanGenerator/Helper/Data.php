<?php

class Hevelop_EanGenerator_Helper_Data extends Mage_Core_Helper_Abstract
{

    const XML_PATH_ENABLED = 'hevelop_eangenerator/general/enabled';
    const XML_PATH_PREFIX = 'hevelop_eangenerator/general/prefix';

    public function conf($code, $store = null)
    {
        return Mage::getStoreConfig($code, $store);
    }

    public function isEnabled($store = null)
    {
        return $this->isModuleEnabled() && $this->conf(self::XML_PATH_ENABLED, $store);
    }

    public function getPrefix($store = null)
    {
        return $this->conf(self::XML_PATH_PREFIX, $store);
    }

    public function ean13CheckDigit($_digits)
    {

        $_digits = (string)$_digits;
        $even_sum = $_digits[1] + $_digits[3] + $_digits[5] + $_digits[7] + $_digits[9] + $_digits[11];
        $even_sum_three = $even_sum * 3;
        $odd_sum = $_digits[0] + $_digits[2] + $_digits[4] + $_digits[6] + $_digits[8] + $_digits[10];
        $total_sum = $even_sum_three + $odd_sum;
        $next_ten = (ceil($total_sum / 10)) * 10;
        $check_digit = $next_ten - $total_sum;

        return $_digits . $check_digit;

    }
}