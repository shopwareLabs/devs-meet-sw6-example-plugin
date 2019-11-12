import template from './personal-product-canvas.html.twig';
import './personal-product-canvas.scss';
const { mapState, mapGetters } = Shopware.Component.getComponentHelper();
const { get } = Shopware.Utils;


Shopware.Component.register('personal-product-canvas', {
    template,

    data() {
        return {
            setPosKey: 0
        };
    },

    computed: {
        ...mapState('swProductDetail', [
            'product'
        ]),

        ...mapGetters('swProductDetail', [
            'isLoading'
        ]),

        isCustomizable: {
            get() {
                return this.product.customFields.personal_product_customizable || false;
            },
            set(value) {
                this.$set(this.product.customFields, 'personal_product_customizable', value);
            }
        }
    },

    methods: {
        setCustomField(x,y, position) {
            this.$set(this.product.customFields, `personal_product_canvasX${position}`, x);
            this.$set(this.product.customFields, `personal_product_canvasY${position}`, y);
        },

        onClickCanvas(e) {
            // Set canvas attributes to the actual picture size
            const meta = this.product.media.first().media.metaData;
            this.$refs.canvas.width = meta.width;
            this.$refs.canvas.height = meta.height;
            const rect = this.$refs.canvas.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            // calculate the ratio, because the image could be scaled in this viewport
            const width = this.$refs.canvas.offsetWidth;
            const height = this.$refs.canvas.offsetHeight;
            const ratioX = this.$refs.canvas.width / width;
            const ratioY = this.$refs.canvas.height / height;

            this.setPosition(x*ratioX, y*ratioY);
        },

        setPosition(x, y) {
            this.setCustomField(Math.ceil(x), Math.ceil(y), this.setPosKey);
            this.setPosKey = + !this.setPosKey;

            this.updateCanvasRect();
        },

        updateCanvasRect() {
            const context = this.$refs.canvas.getContext('2d');
            // clear canvas
            context.clearRect(0, 0, this.$refs.canvas.width, this.$refs.canvas.height);

            // draw a rect for the selected size
            context.fillStyle = "rgba(69,55,194,0.4)";
            context.fillRect(
                this.product.customFields.personal_product_canvasX0,
                this.product.customFields.personal_product_canvasY0,
                this.product.customFields.personal_product_canvasX1 - this.product.customFields.personal_product_canvasX0,
                this.product.customFields.personal_product_canvasY1 - this.product.customFields.personal_product_canvasY0
            );
        },

        onClickReset() {
            this.setCustomField(0, 0, 0);
            this.setCustomField(0, 0, 1);
            this.updateCanvasRect();
        }
    }
});
