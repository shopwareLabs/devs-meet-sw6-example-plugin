import PersonalProductViewer from './js/personal-product-viewer.plugin';
import ImageChanger from './js/image-changer.plugin';

const PluginManager = window.PluginManager;
PluginManager.register('PersonalProductViewer', PersonalProductViewer, '[data-personal-product-viewer]');
PluginManager.register('ImageChanger', ImageChanger, '[data-image-changer]');
