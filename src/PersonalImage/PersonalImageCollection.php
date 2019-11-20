<?php declare(strict_types=1);

namespace SwagPersonalProduct\PersonalImage;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                     add(PersonalImageEntity $entity)
 * @method void                     set(string $key, PersonalImageEntity $entity)
 * @method PersonalImageEntity[]    getIterator()
 * @method PersonalImageEntity[]    getElements()
 * @method PersonalImageEntity|null get(string $key)
 * @method PersonalImageEntity|null first()
 * @method PersonalImageEntity|null last()
 */
class PersonalImageCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return PersonalImageEntity::class;
    }
}
