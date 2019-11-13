<?php declare(strict_types=1);

namespace SwagPersonalProduct;

use Shopware\Core\Framework\CustomField\CustomFieldTypes;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;

class SwagPersonalProduct extends Plugin
{
    public function activate(ActivateContext $activateContext): void
    {
        $repo = $this->container->get('custom_field.repository');

        /** @var EntityRepository */
        $repo->create([
            [
                'name' => 'personal_product_customizable',
                'type' => CustomFieldTypes::BOOL
            ], [
                'name' => 'personal_product_canvasX0',
                'type' => CustomFieldTypes::INT
            ], [
                'name' => 'personal_product_canvasY0',
                'type' => CustomFieldTypes::INT
            ], [
                'name' => 'personal_product_canvasX1',
                'type' => CustomFieldTypes::INT
            ], [
                'name' => 'personal_product_canvasY1',
                'type' => CustomFieldTypes::INT
            ]
        ], $activateContext->getContext());
    }
}
