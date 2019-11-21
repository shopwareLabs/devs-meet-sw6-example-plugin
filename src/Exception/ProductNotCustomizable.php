<?php declare(strict_types=1);

namespace SwagPersonalProduct\Exception;

use Shopware\Core\Framework\ShopwareHttpException;
use Symfony\Component\HttpFoundation\Response;

class ProductNotCustomizable extends ShopwareHttpException
{
    public function __construct(string $id)
    {
        parent::__construct(
            'Product with id `{{ id }}` is not customizable.',
            ['id' => $id]
        );
    }

    public function getErrorCode(): string
    {
        return 'PERSONAL_PRODUCT__PRODUCT_NOT_CUSTOMIZABLE';
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }
}
