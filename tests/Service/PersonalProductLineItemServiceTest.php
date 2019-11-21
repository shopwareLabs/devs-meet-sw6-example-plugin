<?php declare(strict_types=1);

namespace SwagPersonalProduct\Test\Service;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\IdSearchResult;
use Shopware\Core\Framework\Test\TestCaseHelper\ReflectionHelper;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use SwagPersonalProduct\Service\PersonalProductLineItemService;

class PersonalProductLineItemServiceTest extends TestCase
{
    public function testGetLineItemSearchesProduct(): void
    {
        $id = Uuid::randomHex();
        $url = Uuid::randomHex();
        $repo = $this->createMock(EntityRepositoryInterface::class);
        $lineItemService = new PersonalProductLineItemService($this->createMock(CartService::class), $repo);

        /** @var Criteria $criteria */
        $criteria = null;

        $repo->expects(static::once())->method('searchIds')->willReturnCallback(function ($param, $context) use (&$criteria) {
            $criteria = $param;

            return new IdSearchResult(0, [], $param, $context);
        });

        ReflectionHelper::getMethod(PersonalProductLineItemService::class, 'getLineItemId')
            ->invoke($lineItemService, $id, $url, $this->createMock(SalesChannelContext::class));

        static::assertSame(1, $criteria->getLimit());
        static::assertCount(2, $criteria->getFilters());
        static::assertInstanceOf(EqualsFilter::class, $criteria->getFilters()[0]);
        static::assertSame('productId', $criteria->getFilters()[0]->getField());
        static::assertSame($id, $criteria->getFilters()[0]->getValue());
        static::assertInstanceOf(EqualsFilter::class, $criteria->getFilters()[1]);
        static::assertSame($url, $criteria->getFilters()[1]->getValue());
    }

    public function testGetLineItemExistingProduct(): void
    {
        $id = Uuid::randomHex();
        $url = Uuid::randomHex();
        $lineItemId = Uuid::randomHex();
        $repo = $this->createMock(EntityRepositoryInterface::class);
        $lineItemService = new PersonalProductLineItemService($this->createMock(CartService::class), $repo);

        $repo->expects(static::once())
            ->method('searchIds')
            ->willReturn(
                new IdSearchResult(
                    1,
                    [['primaryKey' => $lineItemId, 'data' => $lineItemId]],
                    $this->createMock(Criteria::class),
                    $this->createMock(Context::class)
                )
            );

        $return = ReflectionHelper::getMethod(PersonalProductLineItemService::class, 'getLineItemId')
            ->invoke($lineItemService, $id, $url, $this->createMock(SalesChannelContext::class));

        static::assertSame($lineItemId, $return);
    }

    public function testGetLineItemCreateProduct(): void
    {
        $id = Uuid::randomHex();
        $url = Uuid::randomHex();
        $data = null;
        $repo = $this->createMock(EntityRepositoryInterface::class);
        $lineItemService = new PersonalProductLineItemService($this->createMock(CartService::class), $repo);

        $repo->expects(static::once())
            ->method('searchIds')
            ->willReturn(
                new IdSearchResult(
                    0,
                    [],
                    $this->createMock(Criteria::class),
                    $this->createMock(Context::class)
                )
            );

        $repo->expects(static::once())->method('create')->willReturnCallback(function (array $param) use (&$data) {
            $data = $param;

            return $this->createMock(EntityWrittenContainerEvent::class);
        });

        $return = ReflectionHelper::getMethod(PersonalProductLineItemService::class, 'getLineItemId')
            ->invoke($lineItemService, $id, $url, $this->createMock(SalesChannelContext::class));

        static::assertArrayHasKey('url', $data[0]);
        static::assertArrayHasKey('productId', $data[0]);
        static::assertArrayHasKey('id', $data[0]);
        static::assertSame($id, $data[0]['productId']);
        static::assertSame($url, $data[0]['url']);
        static::assertSame($return, $data[0]['id']);
        static::assertNotNull($return);
    }

    public function testCreateLineItemCreatesLineItem(): void
    {
        $url = Uuid::randomHex();
        $id = Uuid::randomHex();
        $quantity = 42;

        $repo = $this->createMock(EntityRepositoryInterface::class);
        $lineItemService = new PersonalProductLineItemService($this->createMock(CartService::class), $repo);

        $return = ReflectionHelper::getMethod(PersonalProductLineItemService::class, 'createPersonalProductLineItem')
            ->invoke($lineItemService, $url, $id, $quantity, $this->createMock(SalesChannelContext::class));

        static::assertInstanceOf(LineItem::class, $return);
    }

    public function testCreateLineItemPersonalProductIdNewId(): void
    {
        $url = Uuid::randomHex();
        $id = Uuid::randomHex();
        $personalProductId = Uuid::randomHex();
        $quantity = 42;

        $repo = $this->createMock(EntityRepositoryInterface::class);
        $lineItemService = new PersonalProductLineItemService($this->createMock(CartService::class), $repo);

        $repo->expects(static::once())
            ->method('searchIds')
            ->willReturn(
                new IdSearchResult(
                    1,
                    [['primaryKey' => $personalProductId, 'data' => $personalProductId]],
                    $this->createMock(Criteria::class),
                    $this->createMock(Context::class)
                )
            );

        /** @var LineItem $return */
        $return = ReflectionHelper::getMethod(PersonalProductLineItemService::class, 'createPersonalProductLineItem')
            ->invoke($lineItemService, $url, $id, $quantity, $this->createMock(SalesChannelContext::class));

        static::assertInstanceOf(LineItem::class, $return);
        static::assertSame($personalProductId, $return->getId());
    }

    public function testCreateLineItemIsStackable(): void
    {
        $url = Uuid::randomHex();
        $id = Uuid::randomHex();
        $quantity = 42;

        $repo = $this->createMock(EntityRepositoryInterface::class);
        $lineItemService = new PersonalProductLineItemService($this->createMock(CartService::class), $repo);

        /** @var LineItem $return */
        $return = ReflectionHelper::getMethod(PersonalProductLineItemService::class, 'createPersonalProductLineItem')
            ->invoke($lineItemService, $url, $id, $quantity, $this->createMock(SalesChannelContext::class));

        static::assertInstanceOf(LineItem::class, $return);
        static::assertTrue($return->isStackable());
    }

    public function testCreateLineItemIsRemovable(): void
    {
        $url = Uuid::randomHex();
        $id = Uuid::randomHex();
        $quantity = 42;

        $repo = $this->createMock(EntityRepositoryInterface::class);
        $lineItemService = new PersonalProductLineItemService($this->createMock(CartService::class), $repo);

        /** @var LineItem $return */
        $return = ReflectionHelper::getMethod(PersonalProductLineItemService::class, 'createPersonalProductLineItem')
            ->invoke($lineItemService, $url, $id, $quantity, $this->createMock(SalesChannelContext::class));

        static::assertInstanceOf(LineItem::class, $return);
        static::assertTrue($return->isRemovable());
    }

    public function testCreateLineItemQuantity(): void
    {
        $url = Uuid::randomHex();
        $id = Uuid::randomHex();
        $quantity = 42;

        $repo = $this->createMock(EntityRepositoryInterface::class);
        $lineItemService = new PersonalProductLineItemService($this->createMock(CartService::class), $repo);

        /** @var LineItem $return */
        $return = ReflectionHelper::getMethod(PersonalProductLineItemService::class, 'createPersonalProductLineItem')
            ->invoke($lineItemService, $url, $id, $quantity, $this->createMock(SalesChannelContext::class));

        static::assertInstanceOf(LineItem::class, $return);
        static::assertSame($quantity, $return->getQuantity());
    }

    public function testCreateLineItemReferencesProduct(): void
    {
        $url = Uuid::randomHex();
        $id = Uuid::randomHex();
        $quantity = 42;

        $repo = $this->createMock(EntityRepositoryInterface::class);
        $lineItemService = new PersonalProductLineItemService($this->createMock(CartService::class), $repo);

        /** @var LineItem $return */
        $return = ReflectionHelper::getMethod(PersonalProductLineItemService::class, 'createPersonalProductLineItem')
            ->invoke($lineItemService, $url, $id, $quantity, $this->createMock(SalesChannelContext::class));

        static::assertInstanceOf(LineItem::class, $return);
        static::assertSame($id, $return->getReferencedId());
    }

    public function testCreateLineItemHasUrlPayload(): void
    {
        $url = Uuid::randomHex();
        $id = Uuid::randomHex();
        $quantity = 42;

        $repo = $this->createMock(EntityRepositoryInterface::class);
        $lineItemService = new PersonalProductLineItemService($this->createMock(CartService::class), $repo);

        /** @var LineItem $return */
        $return = ReflectionHelper::getMethod(PersonalProductLineItemService::class, 'createPersonalProductLineItem')
            ->invoke($lineItemService, $url, $id, $quantity, $this->createMock(SalesChannelContext::class));

        static::assertInstanceOf(LineItem::class, $return);
        static::assertSame($url, $return->getPayloadValue('url'));
    }

    public function testLineItemIsAddedToCart(): void
    {
        $url = Uuid::randomHex();
        $productId = Uuid::randomHex();
        $repo = $this->createMock(EntityRepositoryInterface::class);
        $cartService = $this->createMock(CartService::class);
        $lineItemService = new PersonalProductLineItemService($cartService, $repo);
        $cart = $this->createMock(Cart::class);

        $lineItem = null;
        $newCart = null;

        $cartService->expects(static::once())->method('add')->willReturnCallback(function ($cartParam, $lineItemParam) use (&$lineItem, &$newCart) {
            $lineItem = $lineItemParam;
            $newCart = $cartParam;

            return $cartParam;
        });

        $lineItemService->add($cart, $url, $productId, 4, $this->createMock(SalesChannelContext::class));

        static::assertSame($cart, $newCart);
        static::assertInstanceOf(LineItem::class, $lineItem);
        static::assertSame($productId, $lineItem->getReferencedId());
    }
}
