import Plugin from 'src/script/plugin-system/plugin.class';

export default class PersonalProductViewer extends Plugin {
    static options = {
        x0: 0.0,
        y0: 0.0,
        x1: 10,
        y1: 10,
        baseImage: null,
    };

    init() {
        const canvas = this.el.querySelector('[data-personal-overlay]');
        console.log(canvas);
        const ctx = canvas.getContext('2d');
        const baseImg = new Image();
        baseImg.onload = () => {
            ctx.fillStyle = 'rgba(200, 20, 100, 0.9)';
            ctx.drawImage(baseImg,0, 0);
            ctx.fillRect(
                this.options.x0,
                this.options.y0,
                this.options.x1 - this.options.x0,
                this.options.y1 - this.options.y0,
            );

            this.urlInput = document.querySelector('input[name=\'personal-product-unsplash-url\']');

            this.urlInput.oninput = (ev) => {
                const img = new Image();
                img.onload = () => {
                    ctx.drawImage(
                        img,
                        this.options.x0,
                        this.options.y0,
                        this.options.x1 - this.options.x0,
                        this.options.y1 - this.options.y0,
                    );
                };
                console.log(ev);
                img.src = ev.target.value;
            }
        }
        baseImg.src = this.options.baseImage;
    }
}
