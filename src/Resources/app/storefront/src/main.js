// import js plugins
import PersonalProductViewer from './js/personal-product-viewer.plugin';
import ImageChanger from './js/image-changer.plugin';

// register plugins in the plugin manager
const PluginManager = window.PluginManager;
PluginManager.register('ImageChanger', ImageChanger, '[data-image-changer]');
PluginManager.register('PersonalProductViewer', PersonalProductViewer, '[data-personal-product-viewer]');
