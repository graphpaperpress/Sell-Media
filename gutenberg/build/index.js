/******/ (function() { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./blocks/sell-media-all-items/index.js":
/*!**********************************************!*\
  !*** ./blocks/sell-media-all-items/index.js ***!
  \**********************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/blocks */ "@wordpress/blocks");
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_editor__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/editor */ "@wordpress/editor");
/* harmony import */ var _wordpress_editor__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_editor__WEBPACK_IMPORTED_MODULE_3__);




const {
  __,
  _x,
  sprintf
} = wp.i18n;
const {
  ServerSideRender,
  RadioControl,
  PanelBody,
  ToggleControl,
  TextControl,
  SelectControl
} = wp.components;
const {
  InspectorControls
} = wp.editor;
(0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_1__.registerBlockType)('sellmedia/sell-media-all-items', {
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

  edit(props) {
    const {
      attributes: {
        per_page,
        show_title,
        quick_view,
        thumbnail_crop,
        thumbnail_layout
      },
      setAttributes
    } = props;
    const panelbody_header = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(PanelBody, {
      title: __('Settings', 'sell_media')
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(TextControl, {
      label: __('Per Page', 'sell_media'),
      value: per_page || '',
      type: 'number',
      onChange: per_page => {
        setAttributes({
          per_page
        });
        macy_init(thumbnail_layout);
      }
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(SelectControl, {
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
      onChange: thumbnail_crop => {
        setAttributes({
          thumbnail_crop
        });
        macy_init(thumbnail_layout);
      }
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(SelectControl, {
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
      onChange: thumbnail_layout => {
        setAttributes({
          thumbnail_layout
        });
        macy_init(thumbnail_layout);
      }
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(ToggleControl, {
      label: __('Show Title', 'sell_media'),
      checked: !!show_title,
      onChange: show_title => {
        setAttributes({
          show_title
        });
        macy_init(thumbnail_layout);
      }
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(ToggleControl, {
      label: __('Quick View', 'sell_media'),
      checked: !!quick_view,
      onChange: quick_view => {
        setAttributes({
          quick_view
        });
        macy_init(thumbnail_layout);
      }
    }));
    const inspectorControls = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(InspectorControls, null, panelbody_header);

    function do_serverside_render(attributes) {
      macy_init(thumbnail_layout);
      return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(ServerSideRender, {
        block: "sellmedia/sell-media-all-items",
        attributes: attributes
      });
    }

    return [inspectorControls, do_serverside_render(props.attributes)];
  },

  save: props => {}
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
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/blocks */ "@wordpress/blocks");
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_editor__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/editor */ "@wordpress/editor");
/* harmony import */ var _wordpress_editor__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_editor__WEBPACK_IMPORTED_MODULE_3__);




const {
  __,
  _x,
  sprintf
} = wp.i18n;
const {
  ServerSideRender,
  RadioControl,
  PanelBody,
  ToggleControl,
  TextControl,
  SelectControl
} = wp.components;
const {
  InspectorControls
} = wp.editor;
(0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_1__.registerBlockType)('sellmedia/sell-media-filters', {
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

  edit(props) {
    const {
      attributes: {
        all,
        newest,
        most_popular,
        collections,
        keywords
      },
      setAttributes
    } = props;

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

    const panelbody_header = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(PanelBody, {
      title: __('Settings', 'sell_media')
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(ToggleControl, {
      label: __('All', 'sell_media'),
      checked: !!all,
      onChange: all => {
        handle_all_option_otherevent(all);
        setAttributes({
          all
        });
      }
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(ToggleControl, {
      label: __('Newest', 'sell_media'),
      checked: !!newest,
      onChange: newest => {
        handle_other_option_allevent(newest, most_popular, collections, keywords);
        setAttributes({
          newest
        });
      }
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(ToggleControl, {
      label: __('Most Popular', 'sell_media'),
      checked: !!most_popular,
      onChange: most_popular => {
        handle_other_option_allevent(newest, most_popular, collections, keywords);
        setAttributes({
          most_popular
        });
      }
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(ToggleControl, {
      label: __('Collections', 'sell_media'),
      checked: !!collections,
      onChange: collections => {
        handle_other_option_allevent(newest, most_popular, collections, keywords);
        setAttributes({
          collections
        });
      }
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(ToggleControl, {
      label: __('Keywords', 'sell_media'),
      checked: !!keywords,
      onChange: keywords => {
        handle_other_option_allevent(newest, most_popular, collections, keywords);
        setAttributes({
          keywords
        });
      }
    }));
    const inspectorControls = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(InspectorControls, null, panelbody_header);

    function do_serverside_render(attributes) {
      return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(ServerSideRender, {
        block: "sellmedia/sell-media-filters",
        attributes: attributes
      });
    }

    return [inspectorControls, do_serverside_render(props.attributes)];
  },

  save: props => {}
});

/***/ }),

/***/ "./blocks/sell-media-items-slider/index.js":
/*!*************************************************!*\
  !*** ./blocks/sell-media-items-slider/index.js ***!
  \*************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/blocks */ "@wordpress/blocks");
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_editor__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/editor */ "@wordpress/editor");
/* harmony import */ var _wordpress_editor__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_editor__WEBPACK_IMPORTED_MODULE_3__);




const {
  __,
  _x,
  sprintf
} = wp.i18n;
const {
  ServerSideRender,
  RadioControl,
  PanelBody,
  ToggleControl,
  TextControl
} = wp.components;
const {
  InspectorControls,
  RichText
} = wp.editor;
(0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_1__.registerBlockType)('sellmedia/sell-media-items-slider', {
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

  edit(props) {
    const {
      attributes: {
        total_items,
        show_title,
        slider_controls,
        gutter,
        item_title,
        total_visible_items
      },
      setAttributes
    } = props;
    const panelbody_header = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(PanelBody, {
      title: __('Settings', 'sell_media')
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(TextControl, {
      label: __('Title', 'sell_media'),
      value: item_title,
      type: 'string',
      onChange: item_title => setAttributes({
        item_title
      })
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(TextControl, {
      label: __('Total Items', 'sell_media'),
      value: total_items,
      type: 'number',
      onChange: total_items => setAttributes({
        total_items
      })
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(TextControl, {
      label: __('Total Visible Items', 'sell_media'),
      value: total_visible_items,
      type: 'number',
      onChange: total_visible_items => setAttributes({
        total_visible_items
      })
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(ToggleControl, {
      label: __('Show Slider Controls', 'sell_media'),
      checked: !!slider_controls,
      onChange: slider_controls => setAttributes({
        slider_controls
      })
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(ToggleControl, {
      label: __('Show Title', 'sell_media'),
      value: show_title,
      checked: !!show_title,
      onChange: show_title => setAttributes({
        show_title
      })
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(TextControl, {
      label: __('Gutter', 'sell_media'),
      value: gutter,
      type: 'number',
      onChange: gutter => setAttributes({
        gutter
      })
    }));
    const inspectorControls = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(InspectorControls, null, panelbody_header);

    function do_serverside_render(attributes) {
      return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(ServerSideRender, {
        block: "sellmedia/sell-media-items-slider",
        attributes: attributes
      });
    }

    return [inspectorControls, do_serverside_render(props.attributes), recent_items(total_visible_items, slider_controls, gutter)];
  },

  save: props => {}
});

function recent_items(total_visible_items, slider_controls, gutter) {
  setTimeout(function () {
    const slider = tns({
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
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/blocks */ "@wordpress/blocks");
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_editor__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/editor */ "@wordpress/editor");
/* harmony import */ var _wordpress_editor__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_editor__WEBPACK_IMPORTED_MODULE_3__);




const {
  __,
  _x,
  sprintf
} = wp.i18n;
const {
  ServerSideRender
} = wp.components;
(0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_1__.registerBlockType)('sellmedia/sell-media-list-all-collections', {
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

  edit(props) {
    function do_serverside_render() {
      return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(ServerSideRender, {
        block: "sellmedia/sell-media-list-all-collections"
      });
    }

    return [do_serverside_render()];
  },

  save: props => {}
});

/***/ }),

/***/ "./blocks/sell-media-search-form/index.js":
/*!************************************************!*\
  !*** ./blocks/sell-media-search-form/index.js ***!
  \************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/blocks */ "@wordpress/blocks");
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_editor__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/editor */ "@wordpress/editor");
/* harmony import */ var _wordpress_editor__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_editor__WEBPACK_IMPORTED_MODULE_3__);




const {
  __,
  _x,
  sprintf
} = wp.i18n;
const {
  ServerSideRender,
  RadioControl,
  PanelBody,
  ToggleControl,
  TextControl,
  TextareaControl,
  FormFileUpload,
  ColorPicker,
  SelectControl,
  Button,
  Spinner,
  ResponsiveWrapper,
  ToolbarGroup,
  ToolbarButton
} = wp.components;
const {
  InspectorControls,
  MediaUpload,
  MediaUploadCheck,
  BlockControls,
  BlockAlignmentToolbar
} = wp.editor;
(0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_1__.registerBlockType)('sellmedia/sell-media-search-form', {
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

  edit(props) {
    const {
      attributes: {
        custom_label,
        custom_description,
        custom_color,
        bgImage,
        bgImageId,
        position_image
      },
      setAttributes
    } = props;
    const instructions = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, __('To edit the background image, you need permission to upload media.', 'sell_media'));
    const ALLOWED_MEDIA_TYPES = ['image'];

    const onUpdateImage = image => {
      setAttributes({
        bgImageId: image.id,
        bgImage: image
      });
    };

    const onRemoveImage = () => {
      setAttributes({
        bgImageId: undefined,
        bgImage: ''
      });
    };

    const panelbody_header = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(PanelBody, {
      title: __('Settings', 'sell_media')
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(BlockControls, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(BlockAlignmentToolbar, {
      value: position_image,
      onChange: position_image => {
        setAttributes({
          position_image: position_image || 'wide'
        });
      },
      controls: ['left', 'wide', 'full']
    })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(TextControl, {
      label: __('Form Label', 'sell_media'),
      value: custom_label || '',
      help: __('Set Custom label to show in search form', 'sell_media'),
      type: 'text',
      onChange: custom_label => setAttributes({
        custom_label
      })
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(TextareaControl, {
      label: __('Form Description', 'sell_media'),
      value: custom_description || '',
      help: __('Set Custom Description to show in search form', 'sell_media'),
      onChange: custom_description => setAttributes({
        custom_description
      })
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      class: "components-base-control"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
      className: "components-base-control__label"
    }, __('Search form background color', 'sell_media'))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(ColorPicker, {
      color: custom_color,
      onChangeComplete: newval => setAttributes({
        custom_color: newval.hex
      }),
      disableAlpha: true
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(RadioControl, {
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
      onChange: position_image => {
        setAttributes({
          position_image
        });
      }
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "wp-block-image-selector-example-image"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(MediaUploadCheck, {
      fallback: instructions
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(MediaUpload, {
      title: __('Layout image', 'image-selector-example'),
      onSelect: onUpdateImage,
      allowedTypes: ALLOWED_MEDIA_TYPES,
      value: bgImageId,
      render: _ref => {
        let {
          open
        } = _ref;
        return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(Button, {
          className: !bgImageId ? 'editor-post-featured-image__toggle' : 'editor-post-featured-image__preview',
          onClick: open
        }, !bgImageId && __('Set Layout image', 'image-selector-example'), !!bgImageId && !bgImage && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(Spinner, null), !!bgImageId && bgImage && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("img", {
          src: bgImage.url,
          alt: __('Layout image', 'image-selector-example'),
          style: {
            width: '100%',
            height: 200,
            position: 'relative'
          }
        }));
      }
    })), !!bgImageId && bgImage && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(MediaUploadCheck, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(MediaUpload, {
      title: __('Background image', 'image-selector-example'),
      onSelect: onUpdateImage,
      allowedTypes: ALLOWED_MEDIA_TYPES,
      value: bgImageId,
      render: _ref2 => {
        let {
          open
        } = _ref2;
        return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(Button, {
          onClick: open,
          isDefault: true,
          isLarge: true
        }, __('Replace layout image', 'image-selector-example'));
      }
    })), !!bgImageId && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(MediaUploadCheck, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(Button, {
      style: {
        paddingTop: 10
      },
      onClick: onRemoveImage,
      isLink: true,
      isDestructive: true
    }, __('Remove layout image', 'image-selector-example')))));
    const inspectorControls = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(InspectorControls, null, panelbody_header);

    function do_serverside_render(attributes) {
      return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(ServerSideRender, {
        block: "sellmedia/sell-media-search-form",
        attributes: attributes
      });
    }

    return [inspectorControls, do_serverside_render(props.attributes)];
  },

  save: props => {}
});

/***/ }),

/***/ "@wordpress/blocks":
/*!********************************!*\
  !*** external ["wp","blocks"] ***!
  \********************************/
/***/ (function(module) {

module.exports = window["wp"]["blocks"];

/***/ }),

/***/ "@wordpress/components":
/*!************************************!*\
  !*** external ["wp","components"] ***!
  \************************************/
/***/ (function(module) {

module.exports = window["wp"]["components"];

/***/ }),

/***/ "@wordpress/editor":
/*!********************************!*\
  !*** external ["wp","editor"] ***!
  \********************************/
/***/ (function(module) {

module.exports = window["wp"]["editor"];

/***/ }),

/***/ "@wordpress/element":
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
/***/ (function(module) {

module.exports = window["wp"]["element"];

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	!function() {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = function(module) {
/******/ 			var getter = module && module.__esModule ?
/******/ 				function() { return module['default']; } :
/******/ 				function() { return module; };
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	!function() {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = function(exports, definition) {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	!function() {
/******/ 		__webpack_require__.o = function(obj, prop) { return Object.prototype.hasOwnProperty.call(obj, prop); }
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	!function() {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = function(exports) {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	}();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
!function() {
/*!**********************!*\
  !*** ./src/index.js ***!
  \**********************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _blocks_sell_media_all_items__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../blocks/sell-media-all-items */ "./blocks/sell-media-all-items/index.js");
/* harmony import */ var _blocks_sell_media_filters__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../blocks/sell-media-filters */ "./blocks/sell-media-filters/index.js");
/* harmony import */ var _blocks_sell_media_items_slider__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../blocks/sell-media-items-slider */ "./blocks/sell-media-items-slider/index.js");
/* harmony import */ var _blocks_sell_media_list_all_collections__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../blocks/sell-media-list-all-collections */ "./blocks/sell-media-list-all-collections/index.js");
/* harmony import */ var _blocks_sell_media_search_form__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../blocks/sell-media-search-form */ "./blocks/sell-media-search-form/index.js");
/**
 * Import Sell Media blocks
 */





}();
/******/ })()
;
//# sourceMappingURL=index.js.map