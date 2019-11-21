<?php declare(strict_types=1);

namespace SwagPersonalProduct\Test\Core;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Product\Events\ProductListingCriteriaEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Aggregation\Bucket\FilterAggregation;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Aggregation\Metric\MaxAggregation;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use SwagPersonalProduct\Core\ProductFilterSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;

class ProductFilterSubscriberTest extends TestCase
{
    public function testImplementsEventSubscriber(): void
    {
        static::assertInstanceOf(EventSubscriberInterface::class, new ProductFilterSubscriber());
    }

    public function testItListensToCriteriaEvent(): void
    {
        static::assertArrayHasKey(ProductListingCriteriaEvent::class, ProductFilterSubscriber::getSubscribedEvents());
    }

    public function testItListensToOnlyOneEvents(): void
    {
        static::assertCount(1, ProductFilterSubscriber::getSubscribedEvents());
    }

    public function testItGetsTheRequestParameters(): void
    {
        $request = $this->createMock(Request::class);
        $request->expects(static::once())->method('get')->with(ProductFilterSubscriber::REQUEST_PARAMETER);

        $event = $this->createMock(ProductListingCriteriaEvent::class);
        $event->expects(static::once())->method('getRequest')->willReturn($request);

        (new ProductFilterSubscriber())->handleFilter($event);
    }

    public function testItAddsAggregationToTheCriteria(): void
    {
        $criteria = $this->createMock(Criteria::class);
        $criteria->expects(static::once())->method('addAggregation');

        $event = $this->createMock(ProductListingCriteriaEvent::class);
        $event->expects(static::once())->method('getCriteria')->willReturn($criteria);

        (new ProductFilterSubscriber())->handleFilter($event);
    }

    public function testAggregationIsFilterAggregation(): void
    {
        $criteria = new Criteria();

        $event = $this->createMock(ProductListingCriteriaEvent::class);
        $event->expects(static::once())->method('getCriteria')->willReturn($criteria);

        (new ProductFilterSubscriber())->handleFilter($event);

        static::assertCount(1, $criteria->getAggregations());
        static::assertArrayHasKey(ProductFilterSubscriber::FILTER_NAME, $criteria->getAggregations());
        static::assertInstanceOf(FilterAggregation::class, $criteria->getAggregations()[ProductFilterSubscriber::FILTER_NAME]);

        /** @var FilterAggregation $filterAggregation */
        $filterAggregation = $criteria->getAggregations()[ProductFilterSubscriber::FILTER_NAME];
        static::assertInstanceOf(MaxAggregation::class, $filterAggregation->getAggregation());
        static::assertCount(1, $filterAggregation->getFilter());

        /** @var MaxAggregation $maxAggregation */
        $maxAggregation = $filterAggregation->getAggregation();

        /** @var EqualsFilter $equalsFilter */
        $equalsFilter = $filterAggregation->getFilter()[0];

        static::assertInstanceOf(EqualsFilter::class, $equalsFilter);

        static::assertSame(ProductFilterSubscriber::PRODUCT_FILTER_FIELD, $maxAggregation->getField());
        static::assertSame($maxAggregation->getField(), $equalsFilter->getField());
    }

    public function testItDoesNotAddPostFilterIfParameterIsMissing(): void
    {
        $criteria = $this->createMock(Criteria::class);
        $criteria->expects(static::once())->method('addAggregation');
        $criteria->expects(static::never())->method('addPostFilter');

        $request = $this->createMock(Request::class);
        $request->expects(static::once())->method('get')->with(ProductFilterSubscriber::REQUEST_PARAMETER)->willReturn(null);

        $event = $this->createMock(ProductListingCriteriaEvent::class);
        $event->expects(static::once())->method('getCriteria')->willReturn($criteria);
        $event->expects(static::once())->method('getRequest')->willReturn($request);

        (new ProductFilterSubscriber())->handleFilter($event);
    }

    public function testItDoesNotAddPostFilterIfParameterIsFalse(): void
    {
        $criteria = $this->createMock(Criteria::class);
        $criteria->expects(static::once())->method('addAggregation');
        $criteria->expects(static::never())->method('addPostFilter');

        $request = $this->createMock(Request::class);
        $request->expects(static::once())->method('get')->with(ProductFilterSubscriber::REQUEST_PARAMETER)->willReturn(false);

        $event = $this->createMock(ProductListingCriteriaEvent::class);
        $event->expects(static::once())->method('getCriteria')->willReturn($criteria);
        $event->expects(static::once())->method('getRequest')->willReturn($request);

        (new ProductFilterSubscriber())->handleFilter($event);
    }

    public function testItDoesAddPostFilterIfParameterIsTrue(): void
    {
        $criteria = $this->createMock(Criteria::class);
        $criteria->expects(static::once())->method('addAggregation');
        $criteria->expects(static::once())->method('addPostFilter');

        $request = $this->createMock(Request::class);
        $request->expects(static::once())->method('get')->with(ProductFilterSubscriber::REQUEST_PARAMETER)->willReturn(true);

        $event = $this->createMock(ProductListingCriteriaEvent::class);
        $event->expects(static::once())->method('getCriteria')->willReturn($criteria);
        $event->expects(static::once())->method('getRequest')->willReturn($request);

        (new ProductFilterSubscriber())->handleFilter($event);
    }

    public function testPostFilterFiltersProductField(): void
    {
        $criteria = new Criteria();

        $request = $this->createMock(Request::class);
        $request->expects(static::once())->method('get')->with(ProductFilterSubscriber::REQUEST_PARAMETER)->willReturn(true);

        $event = $this->createMock(ProductListingCriteriaEvent::class);
        $event->expects(static::once())->method('getCriteria')->willReturn($criteria);
        $event->expects(static::once())->method('getRequest')->willReturn($request);

        (new ProductFilterSubscriber())->handleFilter($event);

        static::assertCount(1, $criteria->getPostFilters());
        static::assertInstanceOf(EqualsFilter::class, $criteria->getPostFilters()[0]);

        /** @var EqualsFilter $equalsFilter */
        $equalsFilter = $criteria->getPostFilters()[0];

        static::assertSame(ProductFilterSubscriber::PRODUCT_FILTER_FIELD, $equalsFilter->getField());
        static::assertTrue($equalsFilter->getValue());
    }
}
