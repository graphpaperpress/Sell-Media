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

/***/ "./blocks/sell-media-all-items/index.js":
/*!**********************************************!*\
  !*** ./blocks/sell-media-all-items/index.js ***!
  \**********************************************/
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
    },
    align: {
      type: 'string',
      default: 'full'
    }
  },
  supports: {
    align: true
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

/***/ "./blocks/sell-media-filters/index.js":
/*!********************************************!*\
  !*** ./blocks/sell-media-filters/index.js ***!
  \********************************************/
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
Object(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_1__["registerBlockType"])('sellmedia/sell-media-filters', {
  title: 'Sell Media Filters',
  description: __('Block showing a Sell Media Items with different settings'),
  icon: 'search',
  category: 'sellmedia-blocks',
  attributes: {
    all: {
      type: 'boolean',
      default: 1
    },
    newest: {
      type: 'boolean',
      default: 0
    },
    most_popular: {
      type: 'boolean',
      default: 0
    },
    collections: {
      type: 'boolean',
      default: 0
    },
    keywords: {
      type: 'boolean',
      default: 0
    },
    align: {
      type: 'string',
      default: 'full'
    }
  },
  supports: {
    align: true
  },
  edit: function edit(props) {
    var _props$attributes = props.attributes,
        all = _props$attributes.all,
        newest = _props$attributes.newest,
        most_popular = _props$attributes.most_popular,
        collections = _props$attributes.collections,
        keywords = _props$attributes.keywords,
        setAttributes = props.setAttributes;

    function handle_all_option_otherevent(all) {
      if (all == true) {
        props.setAttributes({
          newest: 1
        });
        props.setAttributes({
          most_popular: 1
        });
        props.setAttributes({
          collections: 1
        });
        props.setAttributes({
          keywords: 1
        });
      }
    }

    function handle_other_option_allevent(newest, most_popular, collections, keywords) {
      if (newest == 1 || most_popular == 1 || collections == 1 || keywords == 1) {
        props.setAttributes({
          all: 0
        });
      }

      if (newest == 1 && most_popular == 1 && collections == 1 && keywords == 1) {
        props.setAttributes({
          all: 1
        });
        handle_all_option_otherevent(all);
      }
    }

    var panelbody_header = Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(PanelBody, {
      title: __('Settings', 'sell_media')
    }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(ToggleControl, {
      label: __('All', 'sell_media'),
      checked: !!all,
      onChange: function onChange(all) {
        handle_all_option_otherevent(all);
        setAttributes({
          all: all
        });
      }
    }), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(ToggleControl, {
      label: __('Newest', 'sell_media'),
      checked: !!newest,
      onChange: function onChange(newest) {
        handle_other_option_allevent(newest, most_popular, collections, keywords);
        setAttributes({
          newest: newest
        });
      }
    }), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(ToggleControl, {
      label: __('Most Popular', 'sell_media'),
      checked: !!most_popular,
      onChange: function onChange(most_popular) {
        handle_other_option_allevent(newest, most_popular, collections, keywords);
        setAttributes({
          most_popular: most_popular
        });
      }
    }), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(ToggleControl, {
      label: __('Collections', 'sell_media'),
      checked: !!collections,
      onChange: function onChange(collections) {
        handle_other_option_allevent(newest, most_popular, collections, keywords);
        setAttributes({
          collections: collections
        });
      }
    }), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(ToggleControl, {
      label: __('Keywords', 'sell_media'),
      checked: !!keywords,
      onChange: function onChange(keywords) {
        handle_other_option_allevent(newest, most_popular, collections, keywords);
        setAttributes({
          keywords: keywords
        });
      }
    }));
    var inspectorControls = Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(InspectorControls, null, panelbody_header);

    function do_serverside_render(attributes) {
      return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(ServerSideRender, {
        block: "sellmedia/sell-media-filters",
        attributes: attributes
      });
    }

    return [inspectorControls, do_serverside_render(props.attributes)];
  },
  save: function save(props) {}
});

/***/ }),

/***/ "./blocks/sell-media-items-slider/index.js":
/*!*************************************************!*\
  !*** ./blocks/sell-media-items-slider/index.js ***!
  \*************************************************/
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
    TextControl = _wp$components.TextControl;
var _wp$editor = wp.editor,
    InspectorControls = _wp$editor.InspectorControls,
    RichText = _wp$editor.RichText;
Object(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_1__["registerBlockType"])('sellmedia/sell-media-items-slider', {
  title: 'Sell Media Items Slider',
  description: __('Block showing a Sell Media Recent Items as a Slider'),
  icon: 'grid-view',
  category: 'sellmedia-blocks',
  attributes: {
    item_title: {
      type: 'string',
      default: __('Recent Products', 'sell_media')
    },
    total_items: {
      type: 'string',
      default: 10
    },
    total_visible_items: {
      type: 'string',
      default: 3
    },
    show_title: {
      type: 'boolean',
      default: 1
    },
    gutter: {
      type: 'string',
      default: 10
    },
    slider_controls: {
      type: 'boolean',
      default: 1
    },
    align: {
      type: 'string',
      default: 'full'
    }
  },
  supports: {
    align: true
  },
  edit: function edit(props) {
    var _props$attributes = props.attributes,
        total_items = _props$attributes.total_items,
        show_title = _props$attributes.show_title,
        slider_controls = _props$attributes.slider_controls,
        gutter = _props$attributes.gutter,
        item_title = _props$attributes.item_title,
        total_visible_items = _props$attributes.total_visible_items,
        setAttributes = props.setAttributes;
    var panelbody_header = Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(PanelBody, {
      title: __('Settings', 'sell_media')
    }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(TextControl, {
      label: __('Title', 'sell_media'),
      value: item_title,
      type: 'string',
      onChange: function onChange(item_title) {
        return setAttributes({
          item_title: item_title
        });
      }
    }), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(TextControl, {
      label: __('Total Items', 'sell_media'),
      value: total_items,
      type: 'number',
      onChange: function onChange(total_items) {
        return setAttributes({
          total_items: total_items
        });
      }
    }), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(TextControl, {
      label: __('Total Visible Items', 'sell_media'),
      value: total_visible_items,
      type: 'number',
      onChange: function onChange(total_visible_items) {
        return setAttributes({
          total_visible_items: total_visible_items
        });
      }
    }), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(ToggleControl, {
      label: __('Show Slider Controls', 'sell_media'),
      checked: !!slider_controls,
      onChange: function onChange(slider_controls) {
        return setAttributes({
          slider_controls: slider_controls
        });
      }
    }), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(ToggleControl, {
      label: __('Show Title', 'sell_media'),
      value: show_title,
      checked: !!show_title,
      onChange: function onChange(show_title) {
        return setAttributes({
          show_title: show_title
        });
      }
    }), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(TextControl, {
      label: __('Gutter', 'sell_media'),
      value: gutter,
      type: 'number',
      onChange: function onChange(gutter) {
        return setAttributes({
          gutter: gutter
        });
      }
    }));
    var inspectorControls = Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(InspectorControls, null, panelbody_header);

    function do_serverside_render(attributes) {
      return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(ServerSideRender, {
        block: "sellmedia/sell-media-items-slider",
        attributes: attributes
      });
    }

    return [inspectorControls, do_serverside_render(props.attributes), recent_items(total_visible_items, slider_controls, gutter)];
  },
  save: function save(props) {}
});

function recent_items(total_visible_items, slider_controls, gutter) {
  setTimeout(function () {
    var slider = tns({
      container: "#sell-media-recent-items",
      items: total_visible_items,
      navPosition: "bottom",
      controls: false,
      gutter: gutter,
      autoplay: true,
      nav: false,
      mouseDrag: true,
      autoplayButtonOutput: false
    });
  }, 2000);
}

/***/ }),

/***/ "./blocks/sell-media-list-all-collections/index.js":
/*!*********************************************************!*\
  !*** ./blocks/sell-media-list-all-collections/index.js ***!
  \*********************************************************/
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
var ServerSideRender = wp.components.ServerSideRender;
Object(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_1__["registerBlockType"])('sellmedia/sell-media-list-all-collections', {
  title: 'Sell Media Collection Items',
  description: __('Block showing a Sell Media Collection Items'),
  icon: 'grid-view',
  category: 'sellmedia-blocks',
  attributes: {
    align: {
      type: 'string',
      default: 'full'
    }
  },
  supports: {
    align: true
  },
  edit: function edit(props) {
    function do_serverside_render() {
      return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(ServerSideRender, {
        block: "sellmedia/sell-media-list-all-collections"
      });
    }

    return [do_serverside_render()];
  },
  save: function save(props) {}
});

/***/ }),

/***/ "./blocks/sell-media-search-form/index.js":
/*!************************************************!*\
  !*** ./blocks/sell-media-search-form/index.js ***!
  \************************************************/
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
/* harmony import */ var _wordpress_compose__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @wordpress/compose */ "@wordpress/compose");
/* harmony import */ var _wordpress_compose__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_wordpress_compose__WEBPACK_IMPORTED_MODULE_4__);





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
    TextareaControl = _wp$components.TextareaControl,
    FormFileUpload = _wp$components.FormFileUpload,
    ColorPicker = _wp$components.ColorPicker,
    SelectControl = _wp$components.SelectControl,
    Button = _wp$components.Button,
    Spinner = _wp$components.Spinner,
    ResponsiveWrapper = _wp$components.ResponsiveWrapper,
    ToolbarGroup = _wp$components.ToolbarGroup,
    ToolbarButton = _wp$components.ToolbarButton;
var _wp$editor = wp.editor,
    InspectorControls = _wp$editor.InspectorControls,
    MediaUpload = _wp$editor.MediaUpload,
    MediaUploadCheck = _wp$editor.MediaUploadCheck,
    BlockControls = _wp$editor.BlockControls,
    BlockAlignmentToolbar = _wp$editor.BlockAlignmentToolbar;
Object(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_1__["registerBlockType"])('sellmedia/sell-media-search-form', {
  title: 'Sell Media Search Form',
  description: __('Block showing a Sell Media Search Form with different settings', 'sell_media'),
  icon: 'search',
  category: 'sellmedia-blocks',
  attributes: {
    custom_label: {
      type: 'string',
      default: __('Search Form', 'sell_media')
    },
    custom_description: {
      type: 'string',
      default: __('You can search for the items based on keywords, different media files i.e images, videos, audios', 'sell_media')
    },
    custom_color: {
      type: 'string',
      default: '#ccc'
    },
    bgImage: {
      type: 'object'
    },
    bgImageId: {
      type: 'integer'
    },
    position_image: {
      type: 'string',
      default: 'wide'
    },
    align: {
      type: 'string',
      default: 'full'
    }
  },
  supports: {
    align: true
  },
  edit: function edit(props) {
    var _props$attributes = props.attributes,
        custom_label = _props$attributes.custom_label,
        custom_description = _props$attributes.custom_description,
        custom_color = _props$attributes.custom_color,
        bgImage = _props$attributes.bgImage,
        bgImageId = _props$attributes.bgImageId,
        position_image = _props$attributes.position_image,
        setAttributes = props.setAttributes;
    var instructions = Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])("p", null, __('To edit the background image, you need permission to upload media.', 'sell_media'));
    var ALLOWED_MEDIA_TYPES = ['image'];

    var onUpdateImage = function onUpdateImage(image) {
      setAttributes({
        bgImageId: image.id,
        bgImage: image
      });
    };

    var onRemoveImage = function onRemoveImage() {
      setAttributes({
        bgImageId: undefined,
        bgImage: ''
      });
    };

    var panelbody_header = Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(PanelBody, {
      title: __('Settings', 'sell_media')
    }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(BlockControls, null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(BlockAlignmentToolbar, {
      value: position_image,
      onChange: function onChange(position_image) {
        setAttributes({
          position_image: position_image || 'wide'
        });
      },
      controls: ['left', 'wide', 'full']
    })), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(TextControl, {
      label: __('Form Label', 'sell_media'),
      value: custom_label || '',
      help: __('Set Custom label to show in search form', 'sell_media'),
      type: 'text',
      onChange: function onChange(custom_label) {
        return setAttributes({
          custom_label: custom_label
        });
      }
    }), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(TextareaControl, {
      label: __('Form Description', 'sell_media'),
      value: custom_description || '',
      help: __('Set Custom Description to show in search form', 'sell_media'),
      onChange: function onChange(custom_description) {
        return setAttributes({
          custom_description: custom_description
        });
      }
    }), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])("div", {
      class: "components-base-control"
    }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])("label", {
      className: "components-base-control__label"
    }, __('Search form background color', 'sell_media'))), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(ColorPicker, {
      color: custom_color,
      onChangeComplete: function onChangeComplete(newval) {
        return setAttributes({
          custom_color: newval.hex
        });
      },
      disableAlpha: true
    }), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(RadioControl, {
      label: __('Layout', 'sell_media'),
      className: "layout_radio_control_custom",
      help: __('Set the form layout for image position', 'sell_media'),
      selected: position_image,
      options: [{
        label: 'Background',
        value: 'wide'
      }, {
        label: 'Left',
        value: 'right'
      }, {
        label: 'Top',
        value: 'full'
      }],
      onChange: function onChange(position_image) {
        setAttributes({
          position_image: position_image
        });
      }
    }), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])("div", {
      className: "wp-block-image-selector-example-image"
    }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(MediaUploadCheck, {
      fallback: instructions
    }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(MediaUpload, {
      title: __('Layout image', 'image-selector-example'),
      onSelect: onUpdateImage,
      allowedTypes: ALLOWED_MEDIA_TYPES,
      value: bgImageId,
      render: function render(_ref) {
        var open = _ref.open;
        return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(Button, {
          className: !bgImageId ? 'editor-post-featured-image__toggle' : 'editor-post-featured-image__preview',
          onClick: open
        }, !bgImageId && __('Set Layout image', 'image-selector-example'), !!bgImageId && !bgImage && Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(Spinner, null), !!bgImageId && bgImage && Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])("img", {
          src: bgImage.url,
          alt: __('Layout image', 'image-selector-example'),
          style: {
            width: '100%',
            height: 200,
            position: 'relative'
          }
        }));
      }
    })), !!bgImageId && bgImage && Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(MediaUploadCheck, null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(MediaUpload, {
      title: __('Background image', 'image-selector-example'),
      onSelect: onUpdateImage,
      allowedTypes: ALLOWED_MEDIA_TYPES,
      value: bgImageId,
      render: function render(_ref2) {
        var open = _ref2.open;
        return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(Button, {
          onClick: open,
          isDefault: true,
          isLarge: true
        }, __('Replace layout image', 'image-selector-example'));
      }
    })), !!bgImageId && Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(MediaUploadCheck, null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(Button, {
      style: {
        paddingTop: 10
      },
      onClick: onRemoveImage,
      isLink: true,
      isDestructive: true
    }, __('Remove layout image', 'image-selector-example')))));
    var inspectorControls = Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(InspectorControls, null, panelbody_header);

    function do_serverside_render(attributes) {
      return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(ServerSideRender, {
        block: "sellmedia/sell-media-search-form",
        attributes: attributes
      });
    }

    return [inspectorControls, do_serverside_render(props.attributes)];
  },
  save: function save(props) {}
});

/***/ }),

/***/ "./src/index.js":
/*!**********************!*\
  !*** ./src/index.js ***!
  \**********************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _blocks_sell_media_all_items__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../blocks/sell-media-all-items */ "./blocks/sell-media-all-items/index.js");
/* harmony import */ var _blocks_sell_media_filters__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../blocks/sell-media-filters */ "./blocks/sell-media-filters/index.js");
/* harmony import */ var _blocks_sell_media_items_slider__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../blocks/sell-media-items-slider */ "./blocks/sell-media-items-slider/index.js");
/* harmony import */ var _blocks_sell_media_search_form__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../blocks/sell-media-search-form */ "./blocks/sell-media-search-form/index.js");
/* harmony import */ var _blocks_sell_media_list_all_collections__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../blocks/sell-media-list-all-collections */ "./blocks/sell-media-list-all-collections/index.js");
/**
 * Import Sell Media blocks
 */






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

/***/ "@wordpress/compose":
/*!******************************************!*\
  !*** external {"this":["wp","compose"]} ***!
  \******************************************/
/*! no static exports found */
/***/ (function(module, exports) {

(function() { module.exports = this["wp"]["compose"]; }());

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