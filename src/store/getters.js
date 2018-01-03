export const layout = state => {
	
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
