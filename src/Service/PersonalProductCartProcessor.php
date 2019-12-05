<?php declare(strict_types=1);

namespace SwagPersonalProduct\Service;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartProcessorInterface;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class PersonalProductCartProcessor implements CartProcessorInterface
{
    public const PERSONAL_PRODUCT_LINE_ITEM_TYPE = 'personal-product';

    /**
     * @var EntityRepositoryInterface
     */
    private $personalImageRepository;

    public function __construct(EntityRepositoryInterface $personalImageRepository)
    {
        $this->personalImageRepository = $personalImageRepository;
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

        $this->enrich($notCompleted, $context);
    }

    public function process(CartDataCollection $data, Cart $original, Cart $toCalculate, SalesChannelContext $context, CartBehavior $behavior): void
    {
        // nothing special to do here
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

    private function enrich(LineItemCollection $lineItems, SalesChannelContext $salesChannelContext): void
    {
        $images = $this->personalImageRepository->search(new Criteria($this->getIds($lineItems)), $salesChannelContext->getContext());

        foreach ($lineItems as $lineItem) {
            $image = $images->get($lineItem->getId());

            $lineItem->setPayloadValue('url', $image->getUrl());
        }
    }
}
