(window.webpackJsonp=window.webpackJsonp||[]).push([["swag-personal-product"],{MOo8:function(e,t,n){"use strict";n.r(t);var o=n("FGIj"),i=n("gHbT");function r(e){return(r="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function a(e,t){for(var n=0;n<t.length;n++){var o=t[n];o.enumerable=o.enumerable||!1,o.configurable=!0,"value"in o&&(o.writable=!0),Object.defineProperty(e,o.key,o)}}function s(e,t){return!t||"object"!==r(t)&&"function"!=typeof t?function(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}(e):t}function u(e){return(u=Object.setPrototypeOf?Object.getPrototypeOf:function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}function c(e,t){return(c=Object.setPrototypeOf||function(e,t){return e.__proto__=t,e})(e,t)}var l,h,f,p=function(e){function t(){return function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,t),s(this,u(t).apply(this,arguments))}var n,r,l;return function(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),t&&c(e,t)}(t,o["a"]),n=t,(r=[{key:"init",value:function(){var e=this;this.PluginManager=window.PluginManager,this._canvas=i.a.querySelector(this.el,".personal-product-canvas"),this._canvasContext=this._canvas.getContext("2d"),this.subscribeImageChangedEvent(),this._baseImage=this.createImage(this.options.baseImage,function(){e.drawBaseImage(),e.drawOverlay()})}},{key:"subscribeImageChangedEvent",value:function(){var e=i.a.querySelector(document,"[data-image-changer]");this.PluginManager.getPluginInstanceFromElement(e,"ImageChanger").$emitter.subscribe("imageChanged",this.onChangeImage.bind(this))}},{key:"onChangeImage",value:function(e){var t=this,n=e.detail;this.resetCanvas();var o=this.createImage(n,function(){t.drawBaseImage(),t.drawOverlay(o)})}},{key:"createImage",value:function(e,t){var n=new Image;return n.addEventListener("load",t),n.src=e,n}},{key:"drawBaseImage",value:function(){this._canvasContext.drawImage(this._baseImage,0,0)}},{key:"drawOverlay",value:function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:null;e?this._canvasContext.drawImage(e,this.options.x0,this.options.y0,this.options.x1-this.options.x0,this.options.y1-this.options.y0):(this._canvasContext.fillStyle="rgba(69, 55, 194, 0.4)",this._canvasContext.fillRect(this.options.x0,this.options.y0,this.options.x1-this.options.x0,this.options.y1-this.options.y0))}},{key:"resetCanvas",value:function(){this._canvasContext.clearRect(0,0,this._canvas.width,this._canvas.height)}}])&&a(n.prototype,r),l&&a(n,l),t}();f={x0:0,y0:0,x1:10,y1:10,baseImage:null},(h="options")in(l=p)?Object.defineProperty(l,h,{value:f,enumerable:!0,configurable:!0,writable:!0}):l[h]=f;var y=n("k8s9");function b(e){return(b="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function d(e,t){for(var n=0;n<t.length;n++){var o=t[n];o.enumerable=o.enumerable||!1,o.configurable=!0,"value"in o&&(o.writable=!0),Object.defineProperty(e,o.key,o)}}function v(e,t){return!t||"object"!==b(t)&&"function"!=typeof t?function(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}(e):t}function g(e){return(g=Object.setPrototypeOf?Object.getPrototypeOf:function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}function m(e,t){return(m=Object.setPrototypeOf||function(e,t){return e.__proto__=t,e})(e,t)}var w=function(e){function t(){return function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,t),v(this,g(t).apply(this,arguments))}var n,r,a;return function(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),t&&m(e,t)}(t,o["a"]),n=t,(r=[{key:"init",value:function(){this._client=new y.a(window.accessKey,window.contextToken),this._input=i.a.querySelector(this.el,".personal-product-input"),this._button=i.a.querySelector(this.el,".personal-product-button"),this._idField=i.a.querySelector(this.el,"[name="+this.options.idFieldName+"]"),console.log(this._idField),this.addEventListener()}},{key:"addEventListener",value:function(){var e=this;this._button.addEventListener("click",this.onClickFetch.bind(this)),this._input.addEventListener("input",function(t){e.publishChangedEvent(t.target.value)})}},{key:"onClickFetch",value:function(){this._client.get(this.options.fetchRoute,this.onFetchedImage.bind(this))}},{key:"onFetchedImage",value:function(e){var t=JSON.parse(e);this._input.value=t.url,this._idField.value=t.id,this.publishChangedEvent(t.url)}},{key:"publishChangedEvent",value:function(e){this.$emitter.publish("imageChanged",e)}}])&&d(n.prototype,r),a&&d(n,a),t}();!function(e,t,n){t in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n}(w,"options",{fetchRoute:""});var _=window.PluginManager;_.register("ImageChanger",w,"[data-image-changer]"),_.register("PersonalProductViewer",p,"[data-personal-product-viewer]")}},[["MOo8","runtime","vendor-node","vendor-shared"]]]);