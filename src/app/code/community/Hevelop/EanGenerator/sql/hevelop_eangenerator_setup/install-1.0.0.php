<?php
/** @var Hevelop_EanGenerator_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();

$productTypes = array(
    Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
    Mage_Catalog_Model_Product_Type::TYPE_BUNDLE,
    Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE,
    Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL
);

$productTypes = join(',', $productTypes);

$installer->addAttribute('catalog_product', 'hevelop_ean', array(
    'group' => 'General',
    'type' => Varien_Db_Ddl_Table::TYPE_VARCHAR,
    'label' => 'Ean',
    'input' => 'text',
    'class' => '',
    'source' => '',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible' => true,
    'required' => false,
    'user_defined' => true,
    'default' => '',
    'searchable' => true,
    'filterable' => true,
    'comparable' => true,
    'visible_on_front' => true,
    'unique' => true,
    'apply_to' => $productTypes,
    'is_configurable' => false
));

$installer->endSetup();
