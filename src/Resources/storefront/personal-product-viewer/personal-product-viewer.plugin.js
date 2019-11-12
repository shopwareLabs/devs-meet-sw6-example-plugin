import Plugin from 'src/script/plugin-system/plugin.class';

export default class PersonalProductViewer extends Plugin {
    static options={
        x0: 0.0,
        y0: 0.0,
        x1: 10,
        y1: 10,
    };

    init() {
        const canvas = this.el.querySelector('[data-personal-overlay]');
        console.log(canvas);
        const ctx = canvas.getContext('2d');
        ctx.fillStyle = 'rgba(255, 255, 0, 0.9)';
        ctx.fillRect(
            this.options.x0,
            this.options.y0,
            this.options.x1 - this.options.y0,
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
                    this.options.x1 - this.options.y0,
                    this.options.y1 - this.options.y0,
                );
            };
            console.log(ev);
            img.src=ev.target.value;
        }
    }
}
