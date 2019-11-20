<?php declare(strict_types=1);

namespace SwagPersonalProduct\Service;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class PersonalProductLineItemService
{
    /**
     * @var CartService
     */
    private $cartService;

    /**
     * @var EntityRepositoryInterface
     */
    private $imageRepository;

    public function __construct(
        CartService $cartService,
        EntityRepositoryInterface $imageRepository
    ) {
        $this->cartService = $cartService;
        $this->imageRepository = $imageRepository;
    }

    public function add(Cart $cart, string $imageUrl, string $productId, int $productQuantity, SalesChannelContext $salesChannelContext): void
    {
        $lineItem = $this->createPersonalProductLineItem($imageUrl, $productId, $productQuantity, $salesChannelContext);

        $this->cartService->add($cart, $lineItem, $salesChannelContext);
    }

    private function createPersonalProductLineItem(
        string $imageUrl,
        string $productId,
        int $productQuantity,
        SalesChannelContext $context
    ): LineItem {
        $lineItemId = $this->getLineItemId($productId, $imageUrl, $context);

        $productLineItem = new LineItem(
            $lineItemId,
            LineItem::PRODUCT_LINE_ITEM_TYPE,
            $productId,
            $productQuantity
        );

        $productLineItem->setPayloadValue('url', $imageUrl)
            ->setRemovable(true)
            ->setStackable(true);

        return $productLineItem;
    }

    private function getLineItemId(string $productId, string $imageUrl, SalesChannelContext $context): string
    {
        $criteria = new Criteria();
        $criteria->setLimit(1)->addFilter(new EqualsFilter('productId', $productId), new EqualsFilter('url', $imageUrl));
        $id = $this->imageRepository->searchIds($criteria, $context->getContext())->firstId();

        if ($id !== null) {
            return $id;
        }

        $id = Uuid::randomHex();

        $this->imageRepository->create([
            [
                'id' => $id,
                'productId' => $productId,
                'url' => $imageUrl,
            ],
        ], $context->getContext());

        return $id;
    }
}
