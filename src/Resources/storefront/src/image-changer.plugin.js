import Plugin from 'src/script/plugin-system/plugin.class';
import DomAccess from 'src/script/helper/dom-access.helper';
import HttpClient from 'src/script/service/http-client.service';

export default class ImageChanger extends Plugin {

    init() {
        this.client = new HttpClient(window.accessKey, window.contextToken);
        this.addEventListener();
    }

    addEventListener() {
        const button = DomAccess.querySelector(this.el, '.fetch-button');

        button.addEventListener('click', this.onClickFetch.bind(this));
    }

    onClickFetch() {
        console.log('button click : ');
    }

}
