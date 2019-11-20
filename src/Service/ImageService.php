<?php declare(strict_types=1);

namespace SwagPersonalProduct\Service;

use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

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

    public function __construct(EntityRepositoryInterface $productRepository, ImageGuesser $imageGuesser)
    {
        $this->productRepository = $productRepository;
        $this->imageGuesser = $imageGuesser;
    }

    public function getRandomUrlByProductId(string $productId, SalesChannelContext $salesChannelContext): string
    {
        $criteria = new Criteria([$productId]);

        /** @var ProductEntity $product */
        $product = $this->productRepository->search($criteria, $salesChannelContext->getContext())->first();

        $width = $this->getPersonalImageWidth($product);

        $height = $this->getPersonalImageHeight($product);

        return $this->imageGuesser->fetchRandomImageUrl($width, $height);
    }

    private function getPersonalImageWidth(ProductEntity $product): int
    {
        $customFields = $product->getCustomFields();

        $x0 = $customFields['personal_product_canvasX0'];
        $x1 = $customFields['personal_product_canvasX1'];
        $width = abs($x1 - $x0);

        return $this->roundToTens($width);
    }

    private function getPersonalImageHeight(ProductEntity $product): int
    {
        $customFields = $product->getCustomFields();

        $y0 = $customFields['personal_product_canvasY0'];
        $y1 = $customFields['personal_product_canvasY1'];
        $height = abs($y1 - $y0);

        return $this->roundToTens($height);
    }

    private function roundToTens($n): int
    {
        $n = $n / 10;

        return (int) (round($n) * 10);
    }
}
