import template from './personal-product-canvas.html.twig';
import './personal-product-canvas.scss';

const { mapState, mapGetters } = Shopware.Component.getComponentHelper();

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
                if (value) this.initializeCanvas();
            }
        },

        canvasX0: {
            get() { return this.get('X0'); }, set(value) { this.set(value, 'X0'); }
        },

        canvasX1: {
            get() { return this.get('X1'); }, set(value) { this.set(value, 'X1'); }
        },

        canvasY0: {
            get() { return this.get('Y0'); }, set(value) { this.set(value, 'Y0'); }
        },

        canvasY1: {
            get() { return this.get('Y1'); }, set(value) { this.set(value, 'Y1'); }
        },

        canvasWidth: {
            get() { return this.canvasX1 - this.canvasX0 || 0; },
            set(value) {
                this.canvasX1 = this.canvasX0 + value;
            }
        },

        canvasHeight: {
            get() { return this.canvasY1 - this.canvasY0 || 0; },
            set(value) {
                this.canvasY1 = this.canvasY0 + value;
            }
        }
    },

    watch: {
        isLoading(newValue) {
            if (!newValue && this.isCustomizable) {
                this.initializeCanvas();
            }
        }
    },

    methods: {
        get(param) {
            return this.product.customFields[`personal_product_canvas${param}`] || 0;
        },

        set(value, param) {
            this.$set(this.product.customFields, `personal_product_canvas${param}`, value);
            this.updateCanvasRect();
        },

        initializeCanvas() {
            this.$nextTick(() => {
                // Set canvas attributes to the actual picture size
                const meta = this.product.media.first().media.metaData;
                this.$refs.canvas.width = meta.width;
                this.$refs.canvas.height = meta.height;
                this.updateCanvasRect();
            });
        },

        onClickCanvas(e) {
            const rect = this.$refs.canvas.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            // calculate the ratio, because the image could be scaled in this viewport
            const width = this.$refs.canvas.offsetWidth;
            const height = this.$refs.canvas.offsetHeight;
            const ratioX = this.$refs.canvas.width / width;
            const ratioY = this.$refs.canvas.height / height;

            this.setPosition(x * ratioX, y * ratioY);
        },

        setPosition(x, y) {
            this[`canvasX${this.setPosKey}`] = Math.ceil(x);
            this[`canvasY${this.setPosKey}`] = Math.ceil(y);

            this.setPosKey = +!this.setPosKey;
        },

        updateCanvasRect() {
            const context = this.$refs.canvas.getContext('2d');
            // clear canvas
            context.clearRect(0, 0, this.$refs.canvas.width, this.$refs.canvas.height);

            const img = new Image();
            img.onload = () => {
                context.drawImage(img, 0, 0);

                // draw a rect for the selected size
                context.fillStyle = 'rgba(69,55,194,0.4)';
                context.fillRect(
                    this.canvasX0,
                    this.canvasY0,
                    this.canvasX1 - this.canvasX0,
                    this.canvasY1 - this.canvasY0
                );
            };
            img.src = this.product.media.first().media.url;
        },

        onClickReset() {
            this.canvasX0 = 0;
            this.canvasX1 = 0;
            this.canvasY0 = 0;
            this.canvasY1 = 0;
            this.setPosKey = 0;
            this.updateCanvasRect();
        }
    }
});