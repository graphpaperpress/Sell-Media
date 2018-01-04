export const gridLayout = state => {
	
	let setting = sell_media.thumbnail_layout
	let layout = null

	if ( 'sell-media-two-col' === setting )
		layout = 'is-half'
	if ( 'sell-media-three-col' === setting )
		layout = 'is-one-third'
	if ( 'sell-media-four-col' === setting )
		layout = 'is-one-quarter'
	if ( 'sell-media-five-col' === setting )
		layout = 'is-one-fifth'

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

export const defaultSize = state => {
	return {}
}
