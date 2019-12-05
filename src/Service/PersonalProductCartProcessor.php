<?php declare(strict_types=1);

namespace SwagPersonalProduct\Service;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartDataCollectorInterface;
use Shopware\Core\Checkout\Cart\CartProcessorInterface;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Checkout\Cart\Price\QuantityPriceCalculator;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Checkout\Cart\Tax\Struct\TaxRuleCollection;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class PersonalProductCartProcessor implements CartDataCollectorInterface, CartProcessorInterface
{
    public const PERSONAL_PRODUCT_LINE_ITEM_TYPE = 'personal-image';

    /**
     * @var EntityRepositoryInterface
     */
    private $personalImageRepository;

    /**
     * @var QuantityPriceCalculator
     */
    private $calculator;

    /**
     * @var ImageService
     */
    private $imageService;

    public function __construct(EntityRepositoryInterface $personalImageRepository, QuantityPriceCalculator $calculator, ImageService $imageService)
    {
        $this->personalImageRepository = $personalImageRepository;
        $this->calculator = $calculator;
        $this->imageService = $imageService;
    }

    public function collect(
        CartDataCollection $data,
        Cart $original,
        SalesChannelContext $context,
        CartBehavior $behavior
    ): void {
        $lineItems = $original
            ->getLineItems()
            ->filterType(LineItem::PRODUCT_LINE_ITEM_TYPE);

        $notCompleted = $this->getNotCompleted($lineItems);

        $this->enrich($notCompleted, $data, $context);
    }

    private function getNotCompleted(LineItemCollection $lineItems): LineItemCollection
    {
        foreach ($lineItems as $lineItem) {
            if (!$lineItem->hasPayloadValue('url')) {
                continue;
            }

            $lineItems->remove($lineItem->getReferencedId());
        }

        return $lineItems;
    }

    private function getIds(LineItemCollection $lineItemCollection): array
    {
        $ids = [];

        foreach ($lineItemCollection as $lineItem) {
            $ids[] = $lineItem->getId();
        }

        return $ids;
    }

    private function enrich(LineItemCollection $lineItems, CartDataCollection $data, SalesChannelContext $salesChannelContext): void
    {
        $images = $this->personalImageRepository->search(new Criteria($this->getIds($lineItems)), $salesChannelContext->getContext());

        foreach ($lineItems as $lineItem) {
            /** @var ProductEntity $product */
            $product = $data->get('product-' . $lineItem->getReferencedId());
            $image = $images->get($lineItem->getId());

            $height = (string) $this->imageService->getPersonalImageHeight($product);
            $width = (string) $this->imageService->getPersonalImageWidth($product);

            $lineItem->setPayloadValue('url', $image->getUrl())->setPayloadValue('height', $height)->setPayloadValue('width', $width);
        }
    }

    public function process(CartDataCollection $data, Cart $original, Cart $toCalculate, SalesChannelContext $context, CartBehavior $behavior): void
    {
        if ($behavior->isRecalculation()) {
            return;
        }

        $lineItems = $original
            ->getLineItems()
            ->filterType(LineItem::PRODUCT_LINE_ITEM_TYPE);

        $price = 0;
        foreach ($lineItems as $lineItem) {
            if (!$lineItem->hasPayloadValue('url')
                || !$lineItem->hasPayloadValue('width')
                || !$lineItem->hasPayloadValue('height')
            ) {
                continue;
            }

            $price += ((int) $lineItem->getPayloadValue('width') * (int) $lineItem->getPayloadValue('height')) / 10000;
        }

        if (!$price) {
            return;
        }

        $lineItem = new LineItem(Uuid::randomHex(), self::PERSONAL_PRODUCT_LINE_ITEM_TYPE, Uuid::randomHex());
        $priceDefinition = new QuantityPriceDefinition($price, new TaxRuleCollection(), 2, 1);
        $lineItem->setPrice($this->calculator->calculate($priceDefinition, $context));
        $lineItem->setLabel('Personal Product Surcharge');
        $toCalculate->add($lineItem);
    }
}
