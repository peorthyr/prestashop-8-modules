<?php

declare(strict_types=1);

use PrestaShop\Module\Ggadvancedimages\Form\Modifier\ProductFormModifier;
use PrestaShop\Module\Ggadvancedimages\Install\Installer;
use PrestaShop\Module\Ggadvancedimages\Repository\AdvancedImageRepository;
use PrestaShop\Module\Ggadvancedimages\Uploader\AdvancedImageUploader;
use Symfony\Component\HttpFoundation\File\UploadedFile;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__.'/vendor/autoload.php';

class Ggadvancedimages extends Module
{
    public function __construct()
    {
        $this->name = 'ggadvancedimages';
        $this->author = 'Example';
        $this->version = '1.0.0';
        $this->ps_versions_compliancy = ['min' => '9.0.0', 'max' => '9.99.99'];
        parent::__construct();

        $this->displayName = $this->l('GG Advanced Images');
        $this->description = $this->l('Adds language and user specific product images.');
    }

    public function install(): bool
    {
        if (!parent::install()) {
            return false;
        }

        $installer = new Installer();
        return $installer->install($this);
    }

    public function uninstall(): bool
    {
        $installer = new Installer();
        return $installer->uninstall($this) && parent::uninstall();
    }

    public function hookActionProductFormBuilderModifier(array $params): void
    {
        /** @var ProductFormModifier $modifier */
        $modifier = $this->get(ProductFormModifier::class);
        $modifier->modify($params['form_builder']);
    }

    public function hookActionAfterCreateProductFormHandler(array $params): void
    {
        $this->handleImageUpload($params);
    }

    public function hookActionAfterUpdateProductFormHandler(array $params): void
    {
        $this->handleImageUpload($params);
    }

    private function handleImageUpload(array $params): void
    {
        /** @var UploadedFile $file */
        $file = $params['form_data']['gg_image_file'] ?? null;
        $lang = (int)($params['form_data']['gg_image_lang'] ?? 0);
        $isGuest = (bool)($params['form_data']['gg_image_user'] ?? 1);

        if ($file instanceof UploadedFile) {
            $repo = new AdvancedImageRepository();
            $uploader = new AdvancedImageUploader($repo);
            $uploader->upload((int)$params['id'], $file, $lang, $isGuest);
        }
    }

    public function hookActionGetProductPropertiesAfter(array $params): void
    {
        $product = &$params['product'];
        $repo = new AdvancedImageRepository();
        $isGuest = $this->context->customer->isLogged() ? 0 : 1;
        $record = $repo->find((int)$product['id_product'], (int)$this->context->language->id, (bool)$isGuest);
        if ($record) {
            $product['cover'] = _MODULE_DIR_.'ggadvancedimages/uploads/'.$record['image_name'];
        }
    }
}
