<?php

class Hevelop_EanGenerator_Model_Observer
{
    public function setEanBeforeSave(Varien_Event_Observer $observer)
    {
        /** @var Hevelop_EanGenerator_Helper_Data $eanHelper */
        $eanHelper = Mage::helper('hevelop_eangenerator');

        /** @var Mage_Catalog_Model_Product $_product */
        $_product = $observer->getEvent()->getProduct();

        if (!$_product instanceof Mage_Catalog_Model_Product) {
            return $this;
        }

        if (!$eanHelper->isEnabled() || $_product->getHevelopEan()) {
            return $this;
        }

        if (empty($_product->getId())) {
            return $this;
        }

        $_eanPrefix = $eanHelper->getPrefix();

        $_eanPrefixLength = strlen($_eanPrefix);

        $_ean = $eanHelper->ean13CheckDigit($_eanPrefix . str_pad($_product->getId(), (12 - $_eanPrefixLength), "0", STR_PAD_LEFT)); // generate ean13, pad product id to available digits

        if (strlen($_ean) != 13) throw new exception ('Generated EAN13 incorrect length.');

        $_product->setHevelopEan($_ean);

        return $this;
    }
}