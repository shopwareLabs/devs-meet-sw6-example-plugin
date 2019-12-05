import Plugin from 'src/plugin-system/plugin.class';
import DomAccess from 'src/helper/dom-access.helper';
import HttpClient from 'src/service/http-client.service';

export default class ImageChanger extends Plugin {
    static options = {
        fetchRoute: '',
    };

    init() {
        this._client = new HttpClient(window.accessKey, window.contextToken);
        this._input = DomAccess.querySelector(this.el, '.personal-product-input');
        this._fetchButton = DomAccess.querySelector(this.el, '.personal-product-button-fetch');
        this._renderButton = DomAccess.querySelector(this.el, '.personal-product-button-render');
        this.addEventListener();
    }

    addEventListener() {
        this._fetchButton.addEventListener('click', this.onClickFetch.bind(this));
        this._renderButton.addEventListener('click', this.publishChangedEvent.bind(this));
    }

    onClickFetch() {
        this._client.get(this.options.fetchRoute, this.onFetchedImage.bind(this));
    }

    onFetchedImage(response) {
        const url = JSON.parse(response).url;
        this._input.value = url;
        this.publishChangedEvent(url);
    }

    publishChangedEvent() {
        const url = this._input.value;
        this.$emitter.publish('imageChanged', url);
    }
}
