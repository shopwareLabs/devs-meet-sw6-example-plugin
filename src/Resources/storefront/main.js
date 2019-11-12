import PersonalProductViewer from './personal-product-viewer/personal-product-viewer.plugin';
import ImageChanger from './src/image-changer.plugin';

const PluginManager = window.PluginManager;
PluginManager.register('PersonalProductViewer', PersonalProductViewer, '[data-personal-product-viewer]');
PluginManager.register('ImageChanger', ImageChanger, '[data-image-changer]');
