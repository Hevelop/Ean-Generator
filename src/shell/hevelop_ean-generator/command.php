<?php

if (!defined('MAGE_BASE_DIR')) {
    chdir(__DIR__ . '/../../../../../../htdocs/');
    define('MAGE_BASE_DIR', getcwd());
}


require_once MAGE_BASE_DIR . '/shell/hevelop_ean-generator/abstract.php';


class Hevelop_EanGenerator_Shell_Command extends Hevelop_EanGenerator_Shell_Abstract
{


    /**
     * Regen all EAN code
     *
     */
    public function eanRegenAction()
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
            ->getCollection();

        /** @var Mage_Catalog_Model_Product_Action $productAction */
        $productAction = Mage::getSingleton('catalog/product_action');

        foreach ($_products as $_product) {

            $_ean = $eanHelper->ean13CheckDigit($_eanPrefix . str_pad($_product->getId(), (12 - $_eanPrefixLength), "0", STR_PAD_LEFT)); // generate ean13, pad product id to available digits

            if (strlen($_ean) != 13) {
                throw new exception ('Generated EAN13 incorrect length.');
            }

            $productAction->updateAttributes([$_product->getId()], ['hevelop_ean' => $_ean], Mage_Core_Model_App::ADMIN_STORE_ID);
        }

    }//end eanRegenAction()


}//end class Hevelop_EanGenerator_Shell_Command


$shell = new Hevelop_EanGenerator_Shell_Command();
$shell->run();
