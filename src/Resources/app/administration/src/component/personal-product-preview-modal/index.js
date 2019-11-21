import template from './personal-product-preview-modal.html.twig';
import './personal-product-preview-modal.scss';

const { Component, Context } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('personal-product-preview-modal', {
    template,

    inject: [
        'repositoryFactory'
    ],

    props: {
        productId: {
            required: true,
            type: String
        },
        imageUrl: {
            required: true,
            type: String
        }
    },

    data() {
        return {
            product: null
        };
    },

    computed: {
        productRepository() {
            return this.repositoryFactory.create('product');
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            const criteria = new Criteria();
            criteria.setIds([this.productId]);
            criteria.addAssociation('media');

            this.productRepository.search(criteria, Context.api).then(products => {
                this.product = products.first();
                this.$nextTick(() => this.drawCanvas());
            });
        },

        drawCanvas() {
            const meta = this.product.media.first().media.metaData;
            this.$refs.canvas.width = meta.width;
            this.$refs.canvas.height = meta.height;

            const context = this.$refs.canvas.getContext('2d');
            // clear canvas
            context.clearRect(0, 0, this.$refs.canvas.width, this.$refs.canvas.height);
            const customFields = this.product.customFields;
            const img = new Image();
            const overlay = new Image();
            img.onload = () => {
                context.drawImage(img, 0, 0);
                overlay.src = this.imageUrl;
            };
            overlay.onload = () => {
                context.drawImage(
                    overlay,
                    customFields.personal_product_canvasX0,
                    customFields.personal_product_canvasY0,
                    customFields.personal_product_canvasX1 - customFields.personal_product_canvasX0,
                    customFields.personal_product_canvasY1 - customFields.personal_product_canvasY0,
                );
            };
            img.src = this.product.media.first().media.url;
        }
    }
});
