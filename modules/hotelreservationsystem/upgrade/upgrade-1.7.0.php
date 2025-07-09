<?php
/**
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License version 3.0
* that is bundled with this package in the file LICENSE.md
* It is also available through the world-wide-web at this URL:
* https://opensource.org/license/osl-3-0-php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to support@qloapps.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to a newer
* versions in the future. If you wish to customize this module for your needs
* please refer to https://store.webkul.com/customisation-guidelines for more information.
*
* @author Webkul IN
* @copyright Since 2010 Webkul
* @license https://opensource.org/license/osl-3-0-php Open Software License version 3.0
*/

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_7_0($objModule)
{
	$objUpgrade = new UpgradeHotelReservationSystem170($objModule);

	return $objUpgrade->initUpgrade();
}

class UpgradeHotelReservationSystem170
{
	private $module;

    public function __construct($module)
    {
        $this->module = $module;
    }

    public function initUpgrade()
    {
        return $this->callInstallTab()
            && $this->updateDefaultConfiguration()
            && $this->updateTables()
            && $this->createHotelDefaultBedTypes();
    }

    public function callInstallTab()
    {
        return $this->installTab('AdminHotelBedTypes', 'Bed Types', 'AdminCatalog');
    }

    public function updateDefaultConfiguration()
    {
        Configuration::updateValue('WK_DISPLAY_CONTACT_PAGE_HOTEL_LIST', 0);
        Configuration::updateValue('PS_MIN_BOOKING_OFFSET', Configuration::get('GLOBAL_PREPARATION_TIME'));

        $dateToday = date('Y-m-d');
        $maxOrderDate = Configuration::get('MAX_GLOBAL_BOOKING_DATE');
        $checkoutOffset = $this->getNumberOfDays($dateToday, $maxOrderDate);

        Configuration::updateValue('PS_MAX_CHECKOUT_OFFSET', $checkoutOffset);

        $defaultDimensionUnitLang = array(
            'en' => 'ft',
            'nl' => 'ft',
            'fr' => 'pi',
            'de' => 'ft',
            'ru' => 'фут',
            'es' => 'ft',
        );
        $defaultDimensionUnit = array();
        $languages = Language::getLanguages(false);
        foreach ($languages as $lang) {
            if (isset($htlTagLineLang[$lang['iso_code']])) {
                $defaultDimensionUnit[$lang['id_lang']] = $defaultDimensionUnitLang[$lang['iso_code']];
            } else {
                $defaultDimensionUnit[$lang['id_lang']] = $defaultDimensionUnitLang['en'];
            }
        }

        Configuration::updateValue('WK_DIMENSION_UNIT', $defaultDimensionUnit);


        $keysToRemove = array(
            'WK_HOTEL_GLOBAL_ADDRESS',
            'WK_HOTEL_GLOBAL_CONTACT_EMAIL',
            'WK_HOTEL_GLOBAL_CONTACT_NUMBER',
            'MAX_GLOBAL_BOOKING_DATE',
            'GLOBAL_PREPARATION_TIME'
        );

        foreach ($keysToRemove as $keyToRemove) {
            Configuration::deleteByName($keyToRemove);
        }

        return true;
    }

    public function getNumberOfDays($dateFrom, $dateTo)
    {
        $startDate = new DateTime($dateFrom);
        $endDate = new DateTime($dateTo);
        $daysDifference = $startDate->diff($endDate)->days;

        return $daysDifference;
    }

    public function installTab($class_name, $tab_name, $tab_parent_name = false, $need_tab = true)
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = $class_name;
        $tab->name = array();

        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $tab_name;
        }

        if ($tab_parent_name) {
            $tab->id_parent = (int) Tab::getIdFromClassName($tab_parent_name);
        } elseif (!$need_tab) {
            $tab->id_parent = -1;
        } else {
            $tab->id_parent = 0;
        }

        $tab->module = $this->module->name;
        $res = $tab->add();

        return $res;
    }

    public function createHotelDefaultBedTypes()
    {
        $htlBedTypes = array(
            array(
                'length' => '6.25',
                'width'  => '3.16',
                'name' => array(
                    'en' => 'Twin Bed',
                    'nl' => 'Eenpersoonsbed',
                    'fr' => 'Lit simple',
                    'de' => 'Einzelbett',
                    'ru' => 'Односпальная кровать',
                    'es' => 'Cama individual',
                ),
            ),
            array(
                'length' => '6.66',
                'width'  => '3.16',
                'name' => array(
                    'en' => 'Twin XL Bed',
                    'nl' => 'Eenpersoonsbed XL',
                    'fr' => 'Lit simple XL',
                    'de' => 'Einzelbett XL',
                    'ru' => 'Односпальная кровать XL',
                    'es' => 'Cama individual XL',
                ),
            ),
            array(
                'length' => '6.25',
                'width'  => '4.5',
                'name' => array(
                    'en' => 'Full Bed',
                    'nl' => 'Tweepersoonsbed',
                    'fr' => 'Lit double',
                    'de' => 'Doppelbett',
                    'ru' => 'Двуспальная кровать',
                    'es' => 'Cama doble',
                ),
            ),
            array(
                'length' => '6.66',
                'width'  => '5',
                'name' => array(
                    'en' => 'Queen Bed',
                    'nl' => 'Queen size bed',
                    'fr' => 'Lit Queen',
                    'de' => 'Queen-Size-Bett',
                    'ru' => 'Кровать Queen Size',
                    'es' => 'Cama Queen',
                ),
            ),
            array(
                'length' => '6.66',
                'width'  => '6.33',
                'name' => array(
                    'en' => 'King Bed',
                    'nl' => 'King size bed',
                    'fr' => 'Lit King',
                    'de' => 'King-Size-Bett',
                    'ru' => 'Кровать King Size',
                    'es' => 'Cama King',
                ),
            ),
            array(
                'length' => '7',
                'width'  => '6',
                'name' => array(
                    'en' => 'California King Bed',
                    'nl' => 'California King bed',
                    'fr' => 'Lit California King',
                    'de' => 'California King-Bett',
                    'ru' => 'Калифорнийская кровать King Size',
                    'es' => 'Cama California King',
                ),
            ),
            array(
                'length' => '6.25',
                'width'  => '3.16',
                'name' => array(
                    'en' => 'Bunk Bed',
                    'nl' => 'Stapelbed',
                    'fr' => 'Lit superposé',
                    'de' => 'Etagenbett',
                    'ru' => 'Двухъярусная кровать',
                    'es' => 'Litera',
                ),
            ),
            array(
                'length' => '6.25',
                'width'  => '4.5',
                'name' => array(
                    'en' => 'Sofa Bed',
                    'nl' => 'Slaapbank',
                    'fr' => 'Canapé-lit',
                    'de' => 'Schlafsofa',
                    'ru' => 'Диван-кровать',
                    'es' => 'Sofá cama',
                ),
            ),
            array(
                'length' => '6.66',
                'width'  => '5',
                'name' => array(
                    'en' => 'Murphy Bed',
                    'nl' => 'Inklapbed',
                    'fr' => 'Lit escamotable',
                    'de' => 'Klappbett',
                    'ru' => 'Откидная кровать',
                    'es' => 'Cama abatible',
                ),
            ),
        );

        $languages = Language::getLanguages(true);
        foreach ($htlBedTypes as $htlBedType) {
            $objBedType = new HotelBedType();
            foreach ($languages as $lang) {
                if (isset($htlBedType['name'][$lang['iso_code']])) {
                    $objBedType->name[$lang['id_lang']] = $htlBedType['name'][$lang['iso_code']];
                } else {
                    $objBedType->name[$lang['id_lang']] = $htlBedType['name']['en'];
                }

                $objBedType->width = $htlBedType['width'];
                $objBedType->length = $htlBedType['length'];
                $objBedType->save();
            }
        }

        return true;
    }

    public function updateTables()
    {
        if ($sql = $this->getModuleSql()) {
            foreach ($sql as $query) {
                if ($query) {
                    if (!Db::getInstance()->execute(trim($query))) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    public function getModuleSql()
    {
        $sql = array(
            "ALTER TABLE `"._DB_PREFIX_."htl_branch_info`
                ADD COLUMN `fax` varchar(255) DEFAULT NULL AFTER `active_refund`;",

            "ALTER TABLE `"._DB_PREFIX_."htl_booking_detail`
                ADD COLUMN `planned_check_out` DATETIME NOT NULL AFTER `check_out`;",

            "UPDATE `"._DB_PREFIX_."htl_booking_detail`
                SET `planned_check_out` = `date_to`",

            "ALTER TABLE `"._DB_PREFIX_."htl_order_restrict_date`
                CHANGE COLUMN `use_global_max_order_date` `use_global_max_checkout_offset` TINYINT(1) NOT NULL,
                CHANGE COLUMN `use_global_preparation_time` `use_global_min_booking_offset` TINYINT(1) NOT NULL,
                CHANGE COLUMN `preparation_time` `min_booking_offset` INT(11) NOT NULL,
                ADD COLUMN `max_checkout_offset` INT(11) NOT NULL AFTER `max_order_date`;",

            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."htl_bed_type` (
                `id_bed_type` INT(11) NOT NULL AUTO_INCREMENT,
                `length` DECIMAL(20,6) NOT NULL DEFAULT '0.000000',
                `width` DECIMAL(20,6) NOT NULL DEFAULT '0.000000',
                PRIMARY KEY (`id_bed_type`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;",

            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."htl_bed_type_lang`(
                `id_bed_type` INT(11) NOT NULL,
                `name` VARCHAR(255) DEFAULT NULL,
                `id_lang` INT(11) NOT NULL,
                PRIMARY KEY (`id_bed_type`, `id_lang`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;",

            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."htl_room_type_bed_type` (
                `id_room_type_bed_type` INT(11) NOT NULL AUTO_INCREMENT,
                `id_product` INT(11) NOT NULL,
                `id_bed_type` INT(11) NOT NULL,
                PRIMARY KEY (`id_room_type_bed_type`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;",

            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."product_option` (
                `id_product_option` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `id_product` int(11) UNSIGNED NOT NULL,
                `price_impact` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY (`id_product_option`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;",

            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."product_option_lang` (
                `id_product_option` int(10) unsigned NOT NULL,
                `id_lang` int(10) unsigned NOT NULL,
                `name` varchar(255) character set utf8 NOT NULL,
                PRIMARY KEY (`id_product_option`, `id_lang`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8;",
        );

        $maxCheckoutOffset = Configuration::get('PS_MAX_CHECKOUT_OFFSET');
        $dateToday = date('Y-m-d');
        if ($hotelRestrictInfos = Db::getInstance()->executeS("SELECT *  FROM `"._DB_PREFIX_."htl_order_restrict_date`")) {
            $restrictSql = 'UPDATE `'._DB_PREFIX_.'htl_order_restrict_date` SET max_checkout_offset = CASE';
            foreach ($hotelRestrictInfos as $hotelRestrictInfo) {
                if ($hotelRestrictInfo['use_global_max_checkout_offset']) {
                    $restrictSql .= ' WHEN `id` = '.(int)$hotelRestrictInfo['id'].' AND `id_hotel` = '.(int)$hotelRestrictInfo['id_hotel'].'
                        THEN '.(int) $maxCheckoutOffset;
                } else {
                    $checkoutOffset = $this->getNumberOfDays($dateToday, $hotelRestrictInfo['max_order_date']);
                    $restrictSql .= ' WHEN `id` = '.(int)$hotelRestrictInfo['id'].' AND `id_hotel` = '.(int)$hotelRestrictInfo['id_hotel'].'
                        THEN '.(int) $checkoutOffset;
                }
            }

            $restrictSql .= ' END';
            $sql[] = $restrictSql;
        }

        $sql[] = "ALTER TABLE `"._DB_PREFIX_."htl_order_restrict_date`
            DROP COLUMN `max_order_date`";

        $sql[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."htl_room_type_feature_pricing_restriction` (
            `id_feature_price_restriction` int(11) NOT NULL AUTO_INCREMENT,
            `id_feature_price` int(11) NOT NULL,
            `is_special_days_exists` tinyint(1) NOT NULL,
            `date_selection_type` tinyint(1) NOT NULL,
            `special_days` text,
            `date_from` date NOT NULL,
            `date_to` date NOT NULL,
            `date_add` datetime NOT NULL,
            `date_upd` datetime NOT NULL,
            PRIMARY KEY (`id_feature_price_restriction`)
        ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";

        $sql[] = "INSERT INTO `"._DB_PREFIX_."htl_room_type_feature_pricing_restriction`
            (`id_feature_price`, `is_special_days_exists`, `date_selection_type`, `special_days`, `date_from`, `date_to`, `date_add`, `date_upd`)
            SELECT `id_feature_price`, `is_special_days_exists`, `date_selection_type`, `special_days`, `date_from`, `date_to`, `date_add`, `date_upd`
            FROM `"._DB_PREFIX_."htl_room_type_feature_pricing`";

        $sql[] = "ALTER TABLE `"._DB_PREFIX_."htl_room_type_feature_pricing`
            DROP COLUMN `date_from`,
            DROP COLUMN `date_to`,
            DROP COLUMN `is_special_days_exists`,
            DROP COLUMN `date_selection_type`,
            DROP COLUMN `special_days`";

        $sql[] = "RENAME TABLE `"._DB_PREFIX_."htl_room_type_service_product_order_detail` TO `"._DB_PREFIX_."service_product_order_detail`;";

        $sql[] = "ALTER TABLE `"._DB_PREFIX_."service_product_order_detail`
            CHANGE `id_room_type_service_product_order_detail` `id_service_product_order_detail` INT(11) NOT NULL AUTO_INCREMENT;";

        $sql[] = "ALTER TABLE `"._DB_PREFIX_."service_product_order_detail`
            ADD `id_hotel` INT(11) NOT NULL AFTER `id_cart`,
            ADD `id_product_option` INT(11) NOT NULL AFTER `id_htl_booking_detail`,
            ADD `tax_computation_method` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `id_product_option`,
            ADD `id_tax_rules_group` INT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER `tax_computation_method`,
            ADD `option_name` VARCHAR(255) DEFAULT NULL AFTER `name`,
            ADD `hotel_name` VARCHAR(255) DEFAULT NULL AFTER `option_name`,
            ADD `is_refunded` TINYINT(1) NOT NULL DEFAULT '0' AFTER `auto_added`,
            ADD `is_cancelled` TINYINT(1) NOT NULL DEFAULT '0' AFTER `is_refunded`;";

        $sql[] = "RENAME TABLE `"._DB_PREFIX_."htl_room_type_service_product_cart_detail` TO `"._DB_PREFIX_."service_product_cart_detail`;";

        $sql[] = "ALTER TABLE `"._DB_PREFIX_."service_product_cart_detail`
            CHANGE `id_room_type_service_product_cart_detail` `id_service_product_cart_detail` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT;";

        $sql[] = "ALTER TABLE `"._DB_PREFIX_."service_product_cart_detail`
            ADD `id_hotel` INT(11) UNSIGNED NOT NULL AFTER `id_cart`,
            ADD `id_product_option` INT(11) UNSIGNED NOT NULL AFTER `htl_cart_booking_id`;";

        $sql[] = "DROP TABLE IF EXISTS `"._DB_PREFIX_."htl_hotel_service_product_cart_detail`;";

        return $sql;
    }
}
