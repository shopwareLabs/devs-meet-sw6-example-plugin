<?php declare(strict_types=1);

namespace SwagPersonalProduct\Test\Exception;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Uuid\Uuid;
use SwagPersonalProduct\Exception\ProductNotCustomizable;
use Symfony\Component\HttpFoundation\Response;

class ProductNotCustomizableTest extends TestCase
{
    public function testExceptionStatusCodeIsBadRequest(): void
    {
        $exception = new ProductNotCustomizable(Uuid::randomHex());
        static::assertSame(Response::HTTP_BAD_REQUEST, $exception->getStatusCode());
    }

    public function testExceptionErrorCode(): void
    {
        $exception = new ProductNotCustomizable(Uuid::randomHex());
        static::assertSame('PERSONAL_PRODUCT__PRODUCT_NOT_CUSTOMIZABLE', $exception->getErrorCode());
    }

    public function testExceptionMessageContainsId(): void
    {
        $id = Uuid::randomHex();
        $exception = new ProductNotCustomizable($id);
        static::assertTrue(
            static::stringContains($id)
                ->evaluate($exception->getMessage(), '', true)
        );
    }
}
