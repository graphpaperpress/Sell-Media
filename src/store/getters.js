export const gridLayout = state => {
	
	let setting = sell_media.thumbnail_layout
	let layout = null

	if ( 'sell-media-two-col' === setting )
		layout = 'column is-half'
	if ( 'sell-media-three-col' === setting )
		layout = 'column is-one-third'
	if ( 'sell-media-four-col' === setting )
		layout = 'column is-one-quarter'
	if ( 'sell-media-five-col' === setting )
		layout = 'column is-one-fifth'
	if ( 'sell-media-masonry' === setting )
		layout = 'is-masonry'
	if ( 'sell-media-horizontal-masonry' === setting )
		layout = 'is-horizontal-masonry'

	return layout
}

export const gridLayoutContainer = state => {
	
	let setting = sell_media.thumbnail_layout
	let layout = null

	if ( 'sell-media-two-col' === setting )
		layout = 'columns is-half-container'
	if ( 'sell-media-three-col' === setting )
		layout = 'columns is-one-third-container'
	if ( 'sell-media-four-col' === setting )
		layout = 'columns is-one-quarter-container'
	if ( 'sell-media-five-col' === setting )
		layout = 'columns is-one-fifth-container'
	if ( 'sell-media-masonry' === setting )
		layout = 'is-masonry-container'
	if ( 'sell-media-horizontal-masonry' === setting )
		layout = 'is-horizontal-masonry-container'

	return layout
}

export const pageLayout = state => {

	let setting = sell_media.layout
	let layout = {}

	if ( setting === 'sell-media-single-two-col' ) {
		layout = {
			'content': 'column is-two-thirds',
			'sidebar': 'column is-one-third'
		}
	}

	return layout
}
