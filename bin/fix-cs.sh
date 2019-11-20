#!/usr/bin/env bash
echo "Fix php files"
php ../../../dev-ops/analyze/vendor/bin/php-cs-fixer fix --config=../../../vendor/shopware/platform/.php_cs.dist -vv .

echo "Fix javascript files"
../../../vendor/shopware/platform/src/Administration/Resources/app/administration/node_modules/.bin/eslint --ignore-path .eslintignore --config ../../../vendor/shopware/platform/src/Administration/Resources/app/administration/.eslintrc.js --ext .js,.vue --fix ./src/Resources/app/administration

../../../vendor/shopware/platform/src/Storefront/Resources/app/storefront/node_modules/.bin/eslint --ignore-path .eslintignore --config ../../../vendor/shopware/platform/src/Storefront/Resources/app/storefront/.eslintrc.js --ext .js,.vue --fix ./src/Resources/app/storefront
