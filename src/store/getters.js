const gridLayoutMappings = {
  'sell-media-two-col': 'column is-half',
  'sell-media-three-col': 'column is-one-third',
  'sell-media-four-col': 'column is-one-quarter',
  'sell-media-five-col': 'column is-one-fifth',
  'sell-media-masonry': 'is-masonry',
  'sell-media-horizontal-masonry': 'is-horizontal-masonry',
}
const gridLayoutContainerMappings = {
  'sell-media-two-col': 'columns is-half-container',
  'sell-media-three-col': 'columns is-one-third-container',
  'sell-media-four-col': 'columns is-one-quarter-container',
  'sell-media-five-col': 'columns is-one-fifth-container',
  'sell-media-masonry': 'is-masonry-container',
  'sell-media-horizontal-masonry': 'is-horizontal-masonry-container',
}

const pageLayoutMappings = {
  'sell-media-single-two-col': {
    'content': 'column is-two-thirds',
    'sidebar': 'column is-one-third',
  }
}

export const gridLayout = () => gridLayoutMappings[sell_media.thumbnail_layout] || null;

export const gridLayoutContainer = () => gridLayoutContainerMappings[sell_media.thumbnail_layout] || null;

export const pageLayout = () => pageLayoutMappings[sell_media.layout] || {};
