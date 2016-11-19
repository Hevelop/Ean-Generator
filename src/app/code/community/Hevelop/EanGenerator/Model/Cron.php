<?php

class Hevelop_EanGenerator_Model_Cron
{
    public function eanGenerator()
    {
        /** @var Hevelop_EanGenerator_Helper_Data $eanHelper */
        $eanHelper = Mage::helper('hevelop_eangenerator');

        if (!$eanHelper->isEnabled()) {
            return $this;
        }


        $_eanPrefix = $eanHelper->getPrefix();

        $_eanPrefixLength = strlen($_eanPrefix);

        /** @var Mage_Catalog_Model_Resource_Product_Collection $_products */
        $_products = Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToSelect('hevelop_ean');

        /** @var Mage_Catalog_Model_Product_Action $productAction */
        $productAction = Mage::getSingleton('catalog/product_action');

        foreach ($_products as $_product) {
            if ($_product->getHevelopEan()) {
                continue;
            }
            $_ean = $eanHelper->ean13CheckDigit($_eanPrefix . str_pad($_product->getId(), (12 - $_eanPrefixLength), "0", STR_PAD_LEFT)); // generate ean13, pad product id to available digits

            if (strlen($_ean) != 13) throw new exception ('Generated EAN13 incorrect length.');

            $productAction->updateAttributes(array($_product->getId()), array('hevelop_ean' => $_ean), Mage_Core_Model_App::ADMIN_STORE_ID);
        }

        return $this;
    }
}