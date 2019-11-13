<?php declare(strict_types=1);

namespace SwagPersonalProduct\Core;

use Shopware\Core\Content\Product\Events\ProductListingCriteriaEvent;
use Shopware\Core\Content\Product\Events\ProductListingResultEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Aggregation\Bucket\FilterAggregation;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Aggregation\Metric\MaxAggregation;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductFilterSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ProductListingCriteriaEvent::class => 'handleFilter',
            ProductListingResultEvent::class => 'addCurrentFilter',
        ];
    }

    public function handleFilter(ProductListingCriteriaEvent $event): void
    {
        $request = $event->getRequest();

        $criteria = $event->getCriteria();
        $criteria->addAggregation(
            new FilterAggregation(
                'customizable-only-filter',
                new MaxAggregation('customizable-only', 'product.customFields.personal_product_customizable'),
                [new EqualsFilter('product.customFields.personal_product_customizable', true)]
            )
        );

        $filtered = $request->get('customizable-only');

        if (!$filtered) {
            return;
        }

        $criteria->addPostFilter(new EqualsFilter('product.customFields.personal_product_customizable', true));
    }

    public function addCurrentFilter(ProductListingResultEvent $event): void
    {
        $event->getResult()->addCurrentFilter('customizable-only', $event->getRequest()->get('customizable-only'));
    }
}
