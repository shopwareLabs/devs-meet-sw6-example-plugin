import template from './sw-order-line-items-grid.html.twig';

const { Component } = Shopware;

Component.override('sw-order-line-items-grid', {
    template,

    data() {
        return {
            personalProduct: false
        };
    }
});
