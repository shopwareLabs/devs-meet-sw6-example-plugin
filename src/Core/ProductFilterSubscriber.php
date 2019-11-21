<?php declare(strict_types=1);

namespace SwagPersonalProduct\Core;

use Shopware\Core\Content\Product\Events\ProductListingCriteriaEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Aggregation\Bucket\FilterAggregation;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Aggregation\Metric\MaxAggregation;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductFilterSubscriber implements EventSubscriberInterface
{
    public const REQUEST_PARAMETER = 'customizable-only';
    public const FILTER_NAME = 'customizable-only-filter';
    public const PRODUCT_FILTER_FIELD = 'product.customFields.personal_product_customizable';

    public static function getSubscribedEvents(): array
    {
        return [
            ProductListingCriteriaEvent::class => 'handleFilter',
        ];
    }

    public function handleFilter(ProductListingCriteriaEvent $event): void
    {
        $request = $event->getRequest();

        $criteria = $event->getCriteria();
        $criteria->addAggregation(
            new FilterAggregation(
                self::FILTER_NAME,
                new MaxAggregation(self::REQUEST_PARAMETER, self::PRODUCT_FILTER_FIELD),
                [new EqualsFilter(self::PRODUCT_FILTER_FIELD, true)]
            )
        );

        $filtered = $request->get(self::REQUEST_PARAMETER);

        if (!$filtered) {
            return;
        }

        $criteria->addPostFilter(new EqualsFilter(self::PRODUCT_FILTER_FIELD, true));
    }
}
