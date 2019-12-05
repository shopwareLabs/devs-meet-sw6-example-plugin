<?php declare(strict_types=1);

namespace SwagPersonalProduct\Service;

use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use SwagPersonalProduct\Exception\ProductNotCustomizable;
use SwagPersonalProduct\PersonalImage\PersonalImageEntity;
use SwagPersonalProduct\SwagPersonalProduct;

class ImageService
{
    /**
     * @var EntityRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ImageGuesser
     */
    private $imageGuesser;

    /**
     * @var EntityRepositoryInterface
     */
    private $imageRepository;

    public function __construct(
        EntityRepositoryInterface $productRepository,
        ImageGuesser $imageGuesser,
        EntityRepositoryInterface $imageRepository
    ) {
        $this->productRepository = $productRepository;
        $this->imageGuesser = $imageGuesser;
        $this->imageRepository = $imageRepository;
    }

    public function getRandomPersonalImageByProductId(string $productId, SalesChannelContext $salesChannelContext): PersonalImageEntity
    {
        $criteria = new Criteria([$productId]);

        /** @var ProductEntity $product */
        $product = $this->productRepository->search($criteria, $salesChannelContext->getContext())->first();

        $width = $this->getPersonalImageWidth($product);

        $height = $this->getPersonalImageHeight($product);

        $url = $this->imageGuesser->fetchRandomImageUrl($width, $height);

        return $this->getImageUrl($productId, $url, $salesChannelContext);
    }

    public function getImageUrl(string $productId, string $imageUrl, SalesChannelContext $context): PersonalImageEntity
    {
        $criteria = new Criteria();
        $criteria->setLimit(1)->addFilter(new EqualsFilter('productId', $productId), new EqualsFilter('url', $imageUrl));
        $image = $this->imageRepository->search($criteria, $context->getContext())->first();

        if ($image !== null) {
            return $image;
        }

        $id = Uuid::randomHex();

        $this->imageRepository->create([
            [
                'id' => $id,
                'productId' => $productId,
                'url' => $imageUrl,
            ],
        ], $context->getContext());

        $image = new PersonalImageEntity();
        $image->setId($id);
        $image->setUrl($imageUrl);

        return $image;
    }

    public function getPersonalImageWidth(ProductEntity $product): int
    {
        $customFields = $product->getCustomFields();

        $customizable = $customFields[SwagPersonalProduct::PRODUCT_CUSTOMIZABLE] ?? null;
        $x0 = $customFields[SwagPersonalProduct::PRODUCT_CANVAS_X0] ?? null;
        $x1 = $customFields[SwagPersonalProduct::PRODUCT_CANVAS_X1] ?? null;

        if ($customizable !== true || $x0 === null || $x1 === null) {
            throw new ProductNotCustomizable($product->getId());
        }

        $width = abs($x1 - $x0);

        return $this->roundToTens($width);
    }

    public function getPersonalImageHeight(ProductEntity $product): int
    {
        $customFields = $product->getCustomFields();

        $customizable = $customFields[SwagPersonalProduct::PRODUCT_CUSTOMIZABLE] ?? null;
        $y0 = $customFields[SwagPersonalProduct::PRODUCT_CANVAS_Y0] ?? null;
        $y1 = $customFields[SwagPersonalProduct::PRODUCT_CANVAS_Y1] ?? null;

        if ($customizable !== true || $y0 === null || $y1 === null) {
            throw new ProductNotCustomizable($product->getId());
        }

        $height = abs($y1 - $y0);

        return $this->roundToTens($height);
    }

    private function roundToTens($n): int
    {
        return (int) round($n, -1);
    }
}
