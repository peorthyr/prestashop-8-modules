<?php

declare(strict_types=1);

namespace PrestaShop\Module\Ggadvancedimages\Repository;

use Db;

class AdvancedImageRepository
{
    public function upsert(int $productId, int $langId, bool $isGuest, string $imageName): void
    {
        $sql = 'REPLACE INTO `'._DB_PREFIX_.'ggadvancedimages` (`id_product`,`id_lang`,`is_guest`,`image_name`)' .
            ' VALUES (' . (int)$productId . ',' . (int)$langId . ',' . (int)$isGuest . ',\'' . pSQL($imageName) . '\')';
        Db::getInstance()->execute($sql);
    }

    public function find(int $productId, int $langId, bool $isGuest): ?array
    {
        return Db::getInstance()->getRow(
            'SELECT * FROM `'._DB_PREFIX_.'ggadvancedimages` WHERE id_product='.(int)$productId.
            ' AND id_lang='.(int)$langId.' AND is_guest='.(int)$isGuest
        );
    }
}
