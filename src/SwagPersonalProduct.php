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
                'name' => 'personal_product_canvasX',
                'type' => CustomFieldTypes::INT
            ], [
                'name' => 'personal_product_canvasY',
                'type' => CustomFieldTypes::INT
            ], [
                'name' => 'personal_product_canvasWidth',
                'type' => CustomFieldTypes::INT
            ], [
                'name' => 'personal_product_canvasHeight',
                'type' => CustomFieldTypes::INT
            ]
        ], $activateContext->getContext());
    }

    public function getViewPaths(): array
    {
        $viewPaths = parent::getViewPaths();
        $viewPaths[] = 'Resources/views/storefront';

        return $viewPaths;
    }
}
