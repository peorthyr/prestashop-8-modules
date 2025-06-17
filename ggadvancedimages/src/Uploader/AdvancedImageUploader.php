<?php

declare(strict_types=1);

namespace PrestaShop\Module\Ggadvancedimages\Uploader;

use PrestaShop\Module\Ggadvancedimages\Repository\AdvancedImageRepository;
use PrestaShop\PrestaShop\Core\Image\Uploader\Exception\ImageOptimizationException;
use PrestaShop\PrestaShop\Core\Image\Uploader\Exception\ImageUploadException;
use PrestaShop\PrestaShop\Core\Image\Uploader\Exception\MemoryLimitException;
use PrestaShop\PrestaShop\Core\Image\Uploader\Exception\UploadedImageConstraintException;
use PrestaShop\PrestaShop\Core\Image\Uploader\ImageUploaderInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AdvancedImageUploader implements ImageUploaderInterface
{
    private const UPLOAD_PATH = _PS_MODULE_DIR_ . 'ggadvancedimages/uploads/';

    public function __construct(private readonly AdvancedImageRepository $repository)
    {
    }

    public function upload($productId, UploadedFile $image, int $langId, bool $isGuest): void
    {
        $this->checkImageIsAllowedForUpload($image);
        $tmp = $this->createTemporaryImage($image);
        $name = $image->getClientOriginalName();
        $destination = self::UPLOAD_PATH . $name;
        $this->uploadFromTemp($tmp, $destination);
        $this->repository->upsert((int)$productId, $langId, $isGuest, $name);
    }

    protected function createTemporaryImage(UploadedFile $image): string
    {
        $tmpName = tempnam(_PS_TMP_IMG_DIR_, 'PS');
        if (!$tmpName || !move_uploaded_file($image->getPathname(), $tmpName)) {
            throw new ImageUploadException('Failed to create temporary image file');
        }

        return $tmpName;
    }

    protected function uploadFromTemp(string $tmpName, string $destination): void
    {
        if (!\ImageManager::checkImageMemoryLimit($tmpName)) {
            throw new MemoryLimitException('Cannot upload image due to memory restrictions');
        }

        if (!\ImageManager::resize($tmpName, $destination)) {
            throw new ImageOptimizationException('An error occurred while uploading the image.');
        }

        unlink($tmpName);
    }

    protected function checkImageIsAllowedForUpload(UploadedFile $image): void
    {
        $maxFileSize = \Tools::getMaxUploadSize();
        if ($maxFileSize > 0 && $image->getSize() > $maxFileSize) {
            throw new UploadedImageConstraintException('Max file size exceeded', UploadedImageConstraintException::EXCEEDED_SIZE);
        }

        if (!\ImageManager::isRealImage($image->getPathname(), $image->getClientMimeType())
            || !\ImageManager::isCorrectImageFileExt($image->getClientOriginalName())
            || preg_match('/\%00/', $image->getClientOriginalName())
        ) {
            throw new UploadedImageConstraintException('Image format not recognized', UploadedImageConstraintException::UNRECOGNIZED_FORMAT);
        }
    }
}
