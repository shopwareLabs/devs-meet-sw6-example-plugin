import Plugin from 'src/plugin-system/plugin.class';
import DomAccess from 'src/helper/dom-access.helper';
import HttpClient from 'src/service/http-client.service';

export default class ImageChanger extends Plugin {
    static options = {
        fetchRoute: '',
        idFieldName: '',
        csrfToken: '',
    };

    init() {
        this._client = new HttpClient(window.accessKey, window.contextToken);
        this._input = DomAccess.querySelector(this.el, '.personal-product-input');
        this._fetchButton = DomAccess.querySelector(this.el, '.personal-product-button-fetch');
        this._renderButton = DomAccess.querySelector(this.el, '.personal-product-button-render');
        this._idField = DomAccess.querySelector(document, `[name="${this.options.idFieldName}"]`);
        this.addEventListener();
    }

    addEventListener() {
        this._fetchButton.addEventListener('click', this.onClickFetchRandom.bind(this));
        this._renderButton.addEventListener('click', this.onClickFetch.bind(this));
    }

    onClickFetchRandom() {
        this._client.get(this.options.fetchRoute, this.onFetchedImage.bind(this));
    }

    onClickFetch() {
        const url = this._input.value;

        this._client.post(this.options.fetchRoute, JSON.stringify({ 'url': url, '_csrf_token': this.options.csrfToken }), this.onFetchedImage.bind(this));
    }

    onFetchedImage(response) {
        const parsed = JSON.parse(response);
        this._input.value = parsed.url;
        this._idField.value = parsed.id;
        this.publishChangedEvent(parsed.url);
    }

    publishChangedEvent() {
        const url = this._input.value;
        this.$emitter.publish('imageChanged', url);
    }
}
