import PersonalProductViewer from './personal-product-viewer/personal-product-viewer.plugin';

const PluginManager = window.PluginManager;
PluginManager.register('PersonalProductViewer', PersonalProductViewer, '[data-personal-product-viewer]');

if (module.hot) {
    module.hot.accept();
}
