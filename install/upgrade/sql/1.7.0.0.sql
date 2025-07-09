SET NAMES 'utf8';

CREATE TABLE `PREFIX_cart_customer_guest` (
    `id_customer_guest_detail` INT(10) UNSIGNED NOT NULL,
    `id_cart` INT(10) UNSIGNED NOT NULL,
    PRIMARY KEY (`id_customer_guest_detail`, `id_cart`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8;

INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES
	('PS_ALLOW_ADD_ALL_SERVICES_IN_BOOKING', '1', NOW(), NOW()),
	('PS_ALLOW_CREATE_CUSTOM_SERVICES_IN_BOOKING', '1', NOW(), NOW()),
    ('PS_MAIL_SUBJECT_PREFIX', '1', NOW(), NOW()),
    ('PS_CUSTOMER_SERVICE_REQUIRED_NAME', '1', NOW(), NOW()),
    ('PS_CUSTOMER_SERVICE_DISPLAY_NAME', '1', NOW(), NOW()),
    ('PS_CUSTOMER_SERVICE_REQUIRED_PHONE', '1', NOW(), NOW()),
    ('PS_CUSTOMER_SERVICE_DISPLAY_PHONE', '1', NOW(), NOW()),
    ('PS_CUSTOMER_SERVICE_DISPLAY_CONTACT', '1', NOW(), NOW()),
    ('PS_STANDARD_PRODUCT_ORDER_ADDRESS_PREFRENCE', '1', NOW(), NOW()),
    ('PS_CUSTOMER_GUEST_MAX_LIMIT', '1', NOW(), NOW()),
    ('QLO_VERSION_DB', '1.7.0.0', NOW(), NOW()),
    ('QLO_INSTALL_VERSION', '1.7.0.0', NOW(), NOW());

ALTER TABLE `PREFIX_customer`
    ADD `phone` VARCHAR(32) NULL AFTER `ape`;

RENAME TABLE `PREFIX_cart_customer_guest_detail` TO `PREFIX_customer_guest_detail`;

UPDATE `PREFIX_customer` c
    JOIN `PREFIX_customer_guest_detail` cgd ON c.`email` = cgd.`email`
    SET c.`phone` = cgd.`phone` WHERE cgd.`id_cart` = 0;

DELETE FROM `PREFIX_customer_guest_detail` WHERE `id_cart` = 0;

INSERT INTO `PREFIX_cart_customer_guest` (`id_customer_guest_detail`, `id_cart`)
    SELECT `id_customer_guest_detail`, `id_cart` FROM `PREFIX_customer_guest_detail`;

ALTER TABLE `PREFIX_customer_guest_detail`
    ADD `id_customer` INT(10) unsigned NOT NULL AFTER `id_customer_guest_detail`;

UPDATE `PREFIX_customer_guest_detail` cgd
    LEFT JOIN `PREFIX_cart` c ON c.`id_cart` = cgd.`id_cart`
    SET cgd.`id_customer` = c.`id_customer`;

ALTER TABLE `PREFIX_customer_guest_detail`
    DROP COLUMN `id_cart`;

ALTER TABLE `PREFIX_customer_message`
    ADD `id_product` INT(10) UNSIGNED NULL AFTER `message`;

ALTER TABLE `PREFIX_customer_thread`
    DROP INDEX `id_product`,
    DROP COLUMN `id_product`,
    ADD COLUMN `id_employee` INT(10) UNSIGNED DEFAULT NULL AFTER `id_customer`,
    ADD COLUMN `user_name` VARCHAR(128) DEFAULT NULL AFTER `id_order`,
    ADD COLUMN `phone` VARCHAR(32) DEFAULT NULL AFTER `user_name`,
    ADD COLUMN `subject` TEXT DEFAULT NULL AFTER `phone`,
    ADD COLUMN `status_int` INT(10) NOT NULL DEFAULT 1;

UPDATE `PREFIX_customer_thread`
SET `status_int` =
    CASE `status`
        WHEN 'open' THEN 1
        WHEN 'closed' THEN 2
        WHEN 'pending1' THEN 3
        WHEN 'pending2' THEN 4
        ELSE 1
    END;

ALTER TABLE `PREFIX_customer_thread`
    DROP COLUMN `status`,
    CHANGE COLUMN `status_int` `status` INT(10) NOT NULL DEFAULT 1;

ALTER TABLE `PREFIX_order_detail`
    CHANGE COLUMN `product_service_type` `selling_preference_type` TINYINT(1) NOT NULL DEFAULT '1';

ALTER TABLE `PREFIX_order_return_detail`
    ADD COLUMN `id_service_product_order_detail` INT(10) NOT NULL AFTER `id_htl_booking`,
    ADD KEY `id_service_product_order_detail` (`id_service_product_order_detail`);

ALTER TABLE `PREFIX_order_slip_detail`
    DROP PRIMARY KEY,
    ADD COLUMN `id_service_product_order_detail` INT(10) UNSIGNED NOT NULL AFTER `id_htl_booking`,
    ADD PRIMARY KEY (`id_order_slip`, `id_htl_booking`, `id_service_product_order_detail`);

ALTER TABLE `PREFIX_product`
    CHANGE COLUMN `service_product_type` `selling_preference_type` TINYINT(1) NOT NULL DEFAULT '1';

ALTER TABLE `PREFIX_specific_price`
    DROP INDEX `id_product_2`,
    ADD COLUMN `id_htl_cart_booking` INT UNSIGNED NOT NULL,
    ADD UNIQUE KEY `id_product_2` (`id_product`,`id_product_attribute`,`id_customer`,`id_cart`,`from`,`to`,`id_shop`,`id_shop_group`,`id_currency`,`id_country`,`id_group`,`from_quantity`,`id_specific_price_rule`, `id_htl_cart_booking`);

ALTER TABLE `PREFIX_shop_url`
    MODIFY COLUMN `physical_uri` VARCHAR(256) NOT NULL,
    MODIFY COLUMN `virtual_uri` VARCHAR(256) NOT NULL;


/* PHP:add_our_properties_meta_page_170(); */;
/* PHP:clean_hotel_description_170(); */;
/* PHP:update_tabs_170(); */;
