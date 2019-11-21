<?php declare(strict_types=1);

namespace SwagPersonalProduct\Test;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\CustomField\CustomFieldTypes;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\Event\NestedEventCollection;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
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
}
