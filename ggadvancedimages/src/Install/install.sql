CREATE TABLE IF NOT EXISTS `PREFIX_ggadvancedimages` (
    `id_image` INT(11) NOT NULL AUTO_INCREMENT,
    `id_product` INT(11) NOT NULL,
    `id_lang` INT(11) NOT NULL,
    `is_guest` TINYINT(1) NOT NULL DEFAULT 1,
    `image_name` VARCHAR(255) NOT NULL,
    UNIQUE KEY `image_unique` (`id_product`,`id_lang`,`is_guest`),
    PRIMARY KEY (`id_image`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8;
