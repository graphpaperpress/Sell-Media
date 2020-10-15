/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./src/index.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./src/index.js":
/*!**********************!*\
  !*** ./src/index.js ***!
  \**********************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/blocks */ "@wordpress/blocks");
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_editor__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/editor */ "@wordpress/editor");
/* harmony import */ var _wordpress_editor__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_editor__WEBPACK_IMPORTED_MODULE_3__);




var _wp$i18n = wp.i18n,
    __ = _wp$i18n.__,
    _x = _wp$i18n._x,
    sprintf = _wp$i18n.sprintf;
var _wp$components = wp.components,
    ServerSideRender = _wp$components.ServerSideRender,
    RadioControl = _wp$components.RadioControl,
    PanelBody = _wp$components.PanelBody,
    ToggleControl = _wp$components.ToggleControl,
    TextControl = _wp$components.TextControl,
    SelectControl = _wp$components.SelectControl;
var InspectorControls = wp.editor.InspectorControls;
Object(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_1__["registerBlockType"])('sellmedia/sell-media-all-items', {
  title: 'Sell Media Items',
  description: __('Block showing a Sell Media Items with different settings'),
  icon: 'grid-view',
  category: 'sellmedia-blocks',
  attributes: {
    per_page: {
      type: 'string',
      default: 24
    },
    show_title: {
      type: 'boolean',
      default: 1
    },
    quick_view: {
      type: 'boolean',
      default: 1
    },
    thumbnail_crop: {
      type: 'string',
      default: "medium"
    },
    thumbnail_layout: {
      type: 'string',
      default: "sell-media-three-col"
    }
  },
  edit: function edit(props) {
    var _props$attributes = props.attributes,
        per_page = _props$attributes.per_page,
        show_title = _props$attributes.show_title,
        quick_view = _props$attributes.quick_view,
        thumbnail_crop = _props$attributes.thumbnail_crop,
        thumbnail_layout = _props$attributes.thumbnail_layout,
        setAttributes = props.setAttributes;
    var panelbody_header = Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(PanelBody, {
      title: __('Settings', 'sell_media')
    }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(TextControl, {
      label: __('Per Page', 'sell_media'),
      value: per_page || '',
      type: 'number',
      onChange: function onChange(per_page) {
        setAttributes({
          per_page: per_page
        });
        macy_init(thumbnail_layout);
      }
    }), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(SelectControl, {
      key: "thumbnail_crop",
      label: __('Thumbnail Crop', 'sell_media'),
      value: thumbnail_crop,
      options: [{
        label: __('No Crop', 'sell_media'),
        value: 'medium'
      }, {
        label: __('Square Crop', 'sell_media'),
        value: 'sell_media_square'
      }],
      onChange: function onChange(thumbnail_crop) {
        setAttributes({
          thumbnail_crop: thumbnail_crop
        });
        macy_init(thumbnail_layout);
      }
    }), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(SelectControl, {
      key: "thumbnail_layout",
      label: __('Thumbnail Layout', 'sell_media'),
      value: thumbnail_layout,
      options: [{
        label: __('One Column', 'sell_media'),
        value: 'sell-media-one-col'
      }, {
        label: __('Two Columns', 'sell_media'),
        value: 'sell-media-two-col'
      }, {
        label: __('Three Columns', 'sell_media'),
        value: 'sell-media-three-col'
      }, {
        label: __('Four Columns', 'sell_media'),
        value: 'sell-media-four-col'
      }, {
        label: __('Five Columns', 'sell_media'),
        value: 'sell-media-five-col'
      }, {
        label: __('Masonry Layout', 'sell_media'),
        value: 'sell-media-masonry'
      }, {
        label: __('Horizontal Masonry Layout', 'sell_media'),
        value: 'sell-media-horizontal-masonry'
      }],
      onChange: function onChange(thumbnail_layout) {
        setAttributes({
          thumbnail_layout: thumbnail_layout
        });
        macy_init(thumbnail_layout);
      }
    }), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(ToggleControl, {
      label: __('Show Title', 'sell_media'),
      checked: !!show_title,
      onChange: function onChange(show_title) {
        setAttributes({
          show_title: show_title
        });
        macy_init(thumbnail_layout);
      }
    }), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(ToggleControl, {
      label: __('Quick View', 'sell_media'),
      checked: !!quick_view,
      onChange: function onChange(quick_view) {
        setAttributes({
          quick_view: quick_view
        });
        macy_init(thumbnail_layout);
      }
    }));
    var inspectorControls = Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(InspectorControls, null, panelbody_header);

    function do_serverside_render(attributes) {
      macy_init(thumbnail_layout);
      return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(ServerSideRender, {
        block: "sellmedia/sell-media-all-items",
        attributes: attributes
      });
    }

    return [inspectorControls, do_serverside_render(props.attributes)];
  },
  save: function save(props) {}
});

function macy_init(thumbnail_layout) {
  if (thumbnail_layout == "sell-media-masonry") {
    setTimeout(function () {
      Macy.init({
        container: ".sell-media-grid-item-masonry-container",
        trueOrder: false,
        waitForImages: false,
        margin: 10,
        columns: 4,
        breakAt: {
          940: 3,
          768: 2,
          420: 1
        }
      });
    }, 2000);
  }
}

/***/ }),

/***/ "@wordpress/blocks":
/*!*****************************************!*\
  !*** external {"this":["wp","blocks"]} ***!
  \*****************************************/
/*! no static exports found */
/***/ (function(module, exports) {

(function() { module.exports = this["wp"]["blocks"]; }());

/***/ }),

/***/ "@wordpress/components":
/*!*********************************************!*\
  !*** external {"this":["wp","components"]} ***!
  \*********************************************/
/*! no static exports found */
/***/ (function(module, exports) {

(function() { module.exports = this["wp"]["components"]; }());

/***/ }),

/***/ "@wordpress/editor":
/*!*****************************************!*\
  !*** external {"this":["wp","editor"]} ***!
  \*****************************************/
/*! no static exports found */
/***/ (function(module, exports) {

(function() { module.exports = this["wp"]["editor"]; }());

/***/ }),

/***/ "@wordpress/element":
/*!******************************************!*\
  !*** external {"this":["wp","element"]} ***!
  \******************************************/
/*! no static exports found */
/***/ (function(module, exports) {

(function() { module.exports = this["wp"]["element"]; }());

/***/ })

/******/ });
//# sourceMappingURL=index.js.map