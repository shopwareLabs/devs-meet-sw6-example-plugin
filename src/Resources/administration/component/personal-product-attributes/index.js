import template from './personal-product-attributes.html.twig';
const { mapState, mapGetters } = Shopware.Component.getComponentHelper();
const { get } = Shopware.Utils;


Shopware.Component.register('personal-product-attributes', {
    template,

    data() {
        return {
            attributes: {}
        };
    },

    computed: {
        ...mapState('swProductDetail', [
            'product'
        ]),

        ...mapGetters('swProductDetail', [
            'isLoading'
        ]),
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
        },
    }
});
