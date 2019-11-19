import Plugin from 'src/plugin-system/plugin.class';
import DomAccess from 'src/helper/dom-access.helper';

export default class PersonalProductViewer extends Plugin {
    static options = {
        x0: 0.0,
        y0: 0.0,
        x1: 10,
        y1: 10,
        baseImage: null
    };

    init() {
        this.PluginManager = window.PluginManager;
        this._canvas = DomAccess.querySelector(this.el, '.personal-product-canvas');
        this._canvasContext = this._canvas.getContext('2d');

        this.subscribeImageChangedEvent();

        this._baseImage = this.createImage(this.options.baseImage, () => {
            this.drawBaseImage();
            this.drawOverlay();
        });
    }

    subscribeImageChangedEvent() {
        // Subscribe to image changer plugin event
        const imageChangerEl = DomAccess.querySelector(document, '[data-image-changer]');
        const imageChangerInstance = this.PluginManager.getPluginInstanceFromElement(imageChangerEl, 'ImageChanger');
        imageChangerInstance.$emitter.subscribe('imageChanged', this.onChangeImage.bind(this));
    }

    onChangeImage({ detail }) {
        this.resetCanvas();
        const overlayImage = this.createImage(detail, () => {
            this.drawBaseImage();
            this.drawOverlay(overlayImage);
        });
    }

    createImage(imageSrc, loadedCallbackFn) {
        const image = new Image();
        image.addEventListener('load', loadedCallbackFn);
        image.src = imageSrc;

        return image;
    }

    drawBaseImage() {
        // Put the image in the canvas
        this._canvasContext.drawImage(this._baseImage, 0, 0);
    }

    drawOverlay(image = null) {
        if (image) {
            // Draw the overlay image to the canvas
            this._canvasContext.drawImage(
                image,
                this.options.x0,
                this.options.y0,
                this.options.x1 - this.options.x0,
                this.options.y1 - this.options.y0,
            );
        } else {
            // Draw a placeholder overlay to the canvas
            this._canvasContext.fillStyle = 'rgba(69, 55, 194, 0.4)';
            this._canvasContext.fillRect(
                this.options.x0,
                this.options.y0,
                this.options.x1 - this.options.x0,
                this.options.y1 - this.options.y0,
            );
        }
    }

    resetCanvas() {
        this._canvasContext.clearRect(0, 0, this._canvas.width, this._canvas.height);
    }
}
