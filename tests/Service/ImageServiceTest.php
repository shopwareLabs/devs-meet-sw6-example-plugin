<?php declare(strict_types=1);

namespace SwagPersonalProduct\Test\Service;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\Test\TestCaseHelper\ReflectionHelper;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use SwagPersonalProduct\Exception\ProductNotCustomizable;
use SwagPersonalProduct\Service\ImageGuesser;
use SwagPersonalProduct\Service\ImageService;
use SwagPersonalProduct\SwagPersonalProduct;

class ImageServiceTest extends TestCase
{
    public function roundToTensDataProvider(): array
    {
        return [
            [42, 40],
            [45, 50],
            [40, 40],
            [49, 50],
            [44.99, 40],
            [-4, 0],
            [-5, -10],
            [45.0, 50],
            [49.9, 50],
            [50.01, 50],
            ['a', 0],
        ];
    }

    /**
     * @dataProvider roundToTensDataProvider
     */
    public function testRoundToTens($n, int $expected): void
    {
        $imageService = new ImageService($this->createMock(EntityRepositoryInterface::class), $this->createMock(ImageGuesser::class));

        $value = ReflectionHelper::getMethod(ImageService::class, 'roundToTens')->invoke($imageService, $n);

        static::assertSame($expected, $value);
    }

    public function testCalculateWidth(): void
    {
        $imageService = new ImageService($this->createMock(EntityRepositoryInterface::class), $this->createMock(ImageGuesser::class));
        $product = $this->createMock(ProductEntity::class);

        $customFields = [SwagPersonalProduct::PRODUCT_CANVAS_X0 => 0, SwagPersonalProduct::PRODUCT_CANVAS_X1 => 42, SwagPersonalProduct::PRODUCT_CUSTOMIZABLE => true];

        $product->expects(static::atLeastOnce())->method('getCustomFields')->willReturn($customFields);

        $width = ReflectionHelper::getMethod(ImageService::class, 'getPersonalImageWidth')->invoke($imageService, $product);

        static::assertSame(40, $width);
    }

    public function testCalculateWidthWithSecondLeftFromFirst(): void
    {
        $imageService = new ImageService($this->createMock(EntityRepositoryInterface::class), $this->createMock(ImageGuesser::class));
        $product = $this->createMock(ProductEntity::class);

        $customFields = [SwagPersonalProduct::PRODUCT_CANVAS_X0 => 42, SwagPersonalProduct::PRODUCT_CANVAS_X1 => 0, SwagPersonalProduct::PRODUCT_CUSTOMIZABLE => true];

        $product->expects(static::atLeastOnce())->method('getCustomFields')->willReturn($customFields);

        $width = ReflectionHelper::getMethod(ImageService::class, 'getPersonalImageWidth')->invoke($imageService, $product);

        static::assertSame(40, $width);
    }

    public function testCalculateHeight(): void
    {
        $imageService = new ImageService($this->createMock(EntityRepositoryInterface::class), $this->createMock(ImageGuesser::class));
        $product = $this->createMock(ProductEntity::class);

        $customFields = [SwagPersonalProduct::PRODUCT_CANVAS_Y0 => 0, SwagPersonalProduct::PRODUCT_CANVAS_Y1 => 42, SwagPersonalProduct::PRODUCT_CUSTOMIZABLE => true];

        $product->expects(static::atLeastOnce())->method('getCustomFields')->willReturn($customFields);

        $height = ReflectionHelper::getMethod(ImageService::class, 'getPersonalImageHeight')->invoke($imageService, $product);

        static::assertSame(40, $height);
    }

    public function testCalculateHeightWithSecondLeftFromFirst(): void
    {
        $imageService = new ImageService($this->createMock(EntityRepositoryInterface::class), $this->createMock(ImageGuesser::class));
        $product = $this->createMock(ProductEntity::class);

        $customFields = [SwagPersonalProduct::PRODUCT_CANVAS_Y0 => 42, SwagPersonalProduct::PRODUCT_CANVAS_Y1 => 0, SwagPersonalProduct::PRODUCT_CUSTOMIZABLE => true];

        $product->expects(static::atLeastOnce())->method('getCustomFields')->willReturn($customFields);

        $height = ReflectionHelper::getMethod(ImageService::class, 'getPersonalImageHeight')->invoke($imageService, $product);

        static::assertSame(40, $height);
    }

    public function widthExceptionDataProvider(): array
    {
        return [
            [[]],
            [[SwagPersonalProduct::PRODUCT_CUSTOMIZABLE => false]],
            [[SwagPersonalProduct::PRODUCT_CUSTOMIZABLE => '1']],
            [[SwagPersonalProduct::PRODUCT_CUSTOMIZABLE => true]],
            [[SwagPersonalProduct::PRODUCT_CUSTOMIZABLE => true, SwagPersonalProduct::PRODUCT_CANVAS_X0 => 0]],
            [[SwagPersonalProduct::PRODUCT_CUSTOMIZABLE => true, SwagPersonalProduct::PRODUCT_CANVAS_X1 => 0]],
            [[SwagPersonalProduct::PRODUCT_CUSTOMIZABLE => true, SwagPersonalProduct::PRODUCT_CANVAS_X0 => null]],
            [[SwagPersonalProduct::PRODUCT_CUSTOMIZABLE => true, SwagPersonalProduct::PRODUCT_CANVAS_X1 => null]],
            [[SwagPersonalProduct::PRODUCT_CUSTOMIZABLE => true, SwagPersonalProduct::PRODUCT_CANVAS_X0 => null, SwagPersonalProduct::PRODUCT_CANVAS_X1 => null]],
            [[SwagPersonalProduct::PRODUCT_CUSTOMIZABLE => true, SwagPersonalProduct::PRODUCT_CANVAS_X0 => 0, SwagPersonalProduct::PRODUCT_CANVAS_X1 => null]],
            [[SwagPersonalProduct::PRODUCT_CUSTOMIZABLE => true, SwagPersonalProduct::PRODUCT_CANVAS_X0 => null, SwagPersonalProduct::PRODUCT_CANVAS_X1 => 0]],
        ];
    }

    /**
     * @dataProvider widthExceptionDataProvider
     */
    public function testCalculateWidthThrowsExceptionOnNotCustomizableProduct(array $customFields): void
    {
        $imageService = new ImageService($this->createMock(EntityRepositoryInterface::class), $this->createMock(ImageGuesser::class));
        $product = $this->createMock(ProductEntity::class);

        $product->expects(static::atLeastOnce())->method('getCustomFields')->willReturn($customFields);

        $this->expectException(ProductNotCustomizable::class);
        ReflectionHelper::getMethod(ImageService::class, 'getPersonalImageWidth')->invoke($imageService, $product);
    }

    public function heightExceptionDataProvider(): array
    {
        return [
            [[]],
            [[SwagPersonalProduct::PRODUCT_CUSTOMIZABLE => false]],
            [[SwagPersonalProduct::PRODUCT_CUSTOMIZABLE => '1']],
            [[SwagPersonalProduct::PRODUCT_CUSTOMIZABLE => true]],
            [[SwagPersonalProduct::PRODUCT_CUSTOMIZABLE => true, SwagPersonalProduct::PRODUCT_CANVAS_Y0 => 0]],
            [[SwagPersonalProduct::PRODUCT_CUSTOMIZABLE => true, SwagPersonalProduct::PRODUCT_CANVAS_Y1 => 0]],
            [[SwagPersonalProduct::PRODUCT_CUSTOMIZABLE => true, SwagPersonalProduct::PRODUCT_CANVAS_Y0 => null]],
            [[SwagPersonalProduct::PRODUCT_CUSTOMIZABLE => true, SwagPersonalProduct::PRODUCT_CANVAS_Y1 => null]],
            [[SwagPersonalProduct::PRODUCT_CUSTOMIZABLE => true, SwagPersonalProduct::PRODUCT_CANVAS_Y0 => null, SwagPersonalProduct::PRODUCT_CANVAS_Y1 => null]],
            [[SwagPersonalProduct::PRODUCT_CUSTOMIZABLE => true, SwagPersonalProduct::PRODUCT_CANVAS_Y0 => 0, SwagPersonalProduct::PRODUCT_CANVAS_Y1 => null]],
            [[SwagPersonalProduct::PRODUCT_CUSTOMIZABLE => true, SwagPersonalProduct::PRODUCT_CANVAS_Y0 => null, SwagPersonalProduct::PRODUCT_CANVAS_Y1 => 0]],
        ];
    }

    /**
     * @dataProvider heightExceptionDataProvider
     */
    public function testCalculateHeightThrowsExceptionOnNotCustomizableProduct(array $customFields): void
    {
        $imageService = new ImageService($this->createMock(EntityRepositoryInterface::class), $this->createMock(ImageGuesser::class));
        $product = $this->createMock(ProductEntity::class);

        $product->expects(static::atLeastOnce())->method('getCustomFields')->willReturn($customFields);

        $this->expectException(ProductNotCustomizable::class);
        ReflectionHelper::getMethod(ImageService::class, 'getPersonalImageHeight')->invoke($imageService, $product);
    }

    public function testGetRandomUrlCallsSearchWithId(): void
    {
        $repository = $this->createMock(EntityRepositoryInterface::class);
        $imageGuesser = $this->createMock(ImageGuesser::class);
        $imageServiceMock = new ImageService($repository, $imageGuesser);

        $product = $this->createMock(ProductEntity::class);
        $customFields = [
            SwagPersonalProduct::PRODUCT_CANVAS_Y0 => 42, SwagPersonalProduct::PRODUCT_CANVAS_Y1 => 0,
            SwagPersonalProduct::PRODUCT_CANVAS_X0 => 42, SwagPersonalProduct::PRODUCT_CANVAS_X1 => 0,
            SwagPersonalProduct::PRODUCT_CUSTOMIZABLE => true,
        ];
        $product->expects(static::atLeastOnce())->method('getCustomFields')->willReturn($customFields);

        /** @var Criteria|null $criteria */
        $criteria = null;

        $repository->expects(static::once())->method('search')
            ->willReturnCallback(function ($param1, $context) use (&$criteria, $product) {
                $criteria = $param1;

                return new EntitySearchResult(1, new ProductCollection([$product]), null, $criteria, $context);
            });

        $url = 'http://localhost/';

        $imageGuesser->expects(static::once())->method('fetchRandomImageUrl')->with(40, 40)->willReturn($url);

        $return = $imageServiceMock->getRandomUrlByProductId('123', $this->createMock(SalesChannelContext::class));

        static::assertInstanceOf(Criteria::class, $criteria);
        static::assertCount(1, $criteria->getIds());
        static::assertSame('123', $criteria->getIds()[0]);
        static::assertSame($url, $return);
    }
}
