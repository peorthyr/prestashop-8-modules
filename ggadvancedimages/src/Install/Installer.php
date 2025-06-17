<?php

declare(strict_types=1);

namespace PrestaShop\Module\Ggadvancedimages\Install;

use Db;
use Module;
use PrestaShopBundle\Install\SqlLoader;

class Installer
{
    public function install(Module $module): bool
    {
        if (!$this->registerHooks($module)) {
            return false;
        }

        if (!$this->executeSqlFromFile($module->getLocalPath().'src/Install/install.sql')) {
            return false;
        }

        if (!is_dir(_PS_MODULE_DIR_.'ggadvancedimages/uploads/')) {
            mkdir(_PS_MODULE_DIR_.'ggadvancedimages/uploads/', 0755, true);
        }

        return true;
    }

    public function uninstall(Module $module): bool
    {
        return $this->executeSqlFromFile($module->getLocalPath().'src/Install/uninstall.sql');
    }

    private function registerHooks(Module $module): bool
    {
        $hooks = [
            'actionProductFormBuilderModifier',
            'actionAfterCreateProductFormHandler',
            'actionAfterUpdateProductFormHandler',
            'actionGetProductPropertiesAfter',
        ];

        return (bool) $module->registerHook($hooks);
    }

    private function executeSqlFromFile(string $file): bool
    {
        if (!file_exists($file)) {
            return true;
        }

        $allowedCollations = ['utf8mb4_general_ci', 'utf8mb4_unicode_ci'];
        $databaseCollation = Db::getInstance()->getValue('SELECT @@collation_database');
        $loader = new SqlLoader();
        $loader->setMetaData([
            'PREFIX_' => _DB_PREFIX_,
            'ENGINE_TYPE' => _MYSQL_ENGINE_,
            'COLLATION' => (empty($databaseCollation) || !in_array($databaseCollation, $allowedCollations)) ? '' : 'COLLATE ' . $databaseCollation,
        ]);

        return $loader->parseFile($file);
    }
}
