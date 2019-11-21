<?php declare(strict_types=1);

namespace SwagPersonalProduct\Test\Extension;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\ExtensionRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Test\TestCaseHelper\ReflectionHelper;
use SwagPersonalProduct\Extension\ProductExtension;
use SwagPersonalProduct\PersonalImage\PersonalImageDefinition;

class ProductExtensionTest extends TestCase
{
    use IntegrationTestBehaviour;

    public function testExtensionIsForProductEntity(): void
    {
        $extension = new ProductExtension();

        static::assertSame(ProductDefinition::class, $extension->getDefinitionClass());
    }

    public function testExtensionAddsAssociation(): void
    {
        $extension = new ProductExtension();
        $fieldCollection = new FieldCollection();

        $extension->extendFields($fieldCollection);
        /** @var OneToManyAssociationField $association */
        $association = $fieldCollection->first();

        static::assertCount(1, $fieldCollection);
        static::assertInstanceOf(OneToManyAssociationField::class, $association);

        static::assertSame('id', $association->getReferenceField());

        $referenceClass = ReflectionHelper::getProperty(OneToManyAssociationField::class, 'referenceClass')->getValue($association);
        static::assertSame(PersonalImageDefinition::class, $referenceClass);
    }

    public function testServiceHasTag(): void
    {
        $registry = $this->getContainer()->get(ExtensionRegistry::class);

        $classes = [];
        foreach ($registry->getExtensions() as $extension) {
            $classes[get_class($extension)] = true;
        }

        static::assertArrayHasKey(ProductExtension::class, $classes, 'Extension is not injected to the Registry (Tag missing?)');
    }
}
