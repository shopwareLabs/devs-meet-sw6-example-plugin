import Plugin from 'src/script/plugin-system/plugin.class';

export default class PersonalProductViewer extends Plugin {
    static options={
        x: 0.0,
        y: 0.0,
        width: 10,
        height: 10,
    };

    init() {
        const canvas = this.el.querySelector('[data-personal-overlay]');
        console.log(canvas);
        const ctx = canvas.getContext('2d');
        ctx.fillStyle = 'rgba(255, 255, 0, 0.9)';
        ctx.fillRect(
            this.options.x,
            this.options.y,
            this.options.width,
            this.options.height
        );

        this.urlInput = document.querySelector('input[name=\'personal-product-unsplash-url\']');

        this.urlInput.oninput = (ev) => {
            const img = new Image();
            img.onload = () => {
                ctx.drawImage(
                    img,
                    this.options.x,
                    this.options.y,
                    this.options.width,
                    this.options.height
                );
            };
            console.log(ev);
            img.src=ev.target.value;
        }
    }
}
