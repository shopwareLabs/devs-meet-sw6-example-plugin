<?php declare(strict_types=1);

namespace SwagPersonalProduct;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\System\CustomField\CustomFieldTypes;

class SwagPersonalProduct extends Plugin
{
    public const PRODUCT_CUSTOMIZABLE = 'personal_product_customizable';
    public const PRODUCT_CANVAS_X0 = 'personal_product_canvasX0';
    public const PRODUCT_CANVAS_Y0 = 'personal_product_canvasY0';
    public const PRODUCT_CANVAS_X1 = 'personal_product_canvasX1';
    public const PRODUCT_CANVAS_Y1 = 'personal_product_canvasY1';

    public function activate(ActivateContext $activateContext): void
    {
        /** @var EntityRepository $repo */
        $repo = $this->container->get('custom_field.repository');

        // Check if custom fields already exist
        $result = $repo->searchIds((new Criteria())->addFilter(new EqualsAnyFilter('name', [
            self::PRODUCT_CUSTOMIZABLE,
            self::PRODUCT_CANVAS_X0,
            self::PRODUCT_CANVAS_Y0,
            self::PRODUCT_CANVAS_X1,
            self::PRODUCT_CANVAS_Y1
        ])), $activateContext->getContext());

        if ($result->getTotal() > 0 ) {
            return;
        }

        /* @var EntityRepository */
        $repo->create([
            [
                'name' => self::PRODUCT_CUSTOMIZABLE,
                'type' => CustomFieldTypes::BOOL,
            ], [
                'name' => self::PRODUCT_CANVAS_X0,
                'type' => CustomFieldTypes::INT,
            ], [
                'name' => self::PRODUCT_CANVAS_Y0,
                'type' => CustomFieldTypes::INT,
            ], [
                'name' => self::PRODUCT_CANVAS_X1,
                'type' => CustomFieldTypes::INT,
            ], [
                'name' => self::PRODUCT_CANVAS_Y1,
                'type' => CustomFieldTypes::INT,
            ],
        ], $activateContext->getContext());
    }
}
