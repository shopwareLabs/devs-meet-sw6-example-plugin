<?php declare(strict_types=1);

namespace SwagPersonalProduct\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1574240795AddTable extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1574240795;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
        CREATE TABLE IF NOT EXISTS swag_personal_product_image
        (
            id BINARY(16) NOT NULL,
            url VARCHAR(255) NOT NULL,
            product_id BINARY(16) NOT NULL,
            created_at DATETIME(3) NOT NULL,
            updated_at DATETIME(3) NULL,
            CONSTRAINT `pk.swag_personal_product_image`
                PRIMARY KEY (id),
            CONSTRAINT `fk.swag_personal_product_image.product`
                foreign KEY (product_id) REFERENCES product (id)
                    ON UPDATE CASCADE ON DELETE CASCADE
        );');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
