/** Import JavaScript plugin classes */

import ImageChanger from './js/image-changer.plugin';

/** Register plugins in the plugin manager */
const PluginManager = window.PluginManager;
PluginManager.register('ImageChanger', ImageChanger, '[data-image-changer]');
PluginManager.register('PersonalProductViewer', PersonalProductViewer, '[data-personal-product-viewer]');
