<?php declare(strict_types=1);

namespace SwagPersonalProduct\Test;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\IdSearchResult;
use Shopware\Core\Framework\Event\NestedEventCollection;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\System\CustomField\CustomFieldTypes;
use SwagPersonalProduct\SwagPersonalProduct;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SwagPersonalProductTest extends TestCase
{
    public function testIsPlugin(): void
    {
        static::assertInstanceOf(Plugin::class, new SwagPersonalProduct(true, ''));
    }

    public function testItGetsCustomsFieldRepository(): void
    {
        $repo = $this->createMock(EntityRepositoryInterface::class);
        $container = $this->createMock(ContainerInterface::class);

        $container->expects(static::once())->method('get')->with('custom_field.repository')->willReturn($repo);

        $plugin = new SwagPersonalProduct(true, '');
        $plugin->setContainer($container);

        $plugin->activate($this->createMock(ActivateContext::class));
    }

    public function testItAddsCustomFields(): void
    {
        $customFields = [];
        $repo = $this->createMock(EntityRepositoryInterface::class);
        $repo->expects(static::once())->method('create')->willReturnCallback(function (array $data, $context) use (&$customFields) {
            $customFields = $data;

            return new EntityWrittenContainerEvent($context, new NestedEventCollection(), []);
        });

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(static::once())->method('get')->with('custom_field.repository')->willReturn($repo);

        $plugin = new SwagPersonalProduct(true, '');
        $plugin->setContainer($container);

        $plugin->activate($this->createMock(ActivateContext::class));

        static::assertCount(5, $customFields);

        $booleanTypes = array_filter($customFields, function (array $field) { return $field['type'] === CustomFieldTypes::BOOL; });
        $intTypes = array_filter($customFields, function (array $field) { return $field['type'] === CustomFieldTypes::INT; });

        static::assertCount(1, $booleanTypes);
        static::assertCount(4, $intTypes);
    }

    public function testCustomFieldsAlreadyExists(): void
    {
        $idResult = $this->createMock(IdSearchResult::class);
        $idResult->expects(static::once())->method('getTotal')->willReturn(4);
        /** @var Criteria $criteria */
        $criteria = null;
        $repo = $this->createMock(EntityRepositoryInterface::class);
        $repo->expects(static::once())->method('searchIds')->willReturnCallback(function (Criteria $data, $context) use (&$idResult, &$criteria) {
            $criteria = $data;

            return $idResult;
        });

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(static::once())->method('get')->with('custom_field.repository')->willReturn($repo);
        $plugin = new SwagPersonalProduct(true, '');
        $plugin->setContainer($container);

        $plugin->activate($this->createMock(ActivateContext::class));

        static::assertInstanceOf(EqualsAnyFilter::class, $criteria->getFilters()[0]);
    }
}
