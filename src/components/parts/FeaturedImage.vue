<template>
	<div>

		<img
			:src="post.sell_media_featured_image.sizes[size][0]" 
			:data-srcset="post.sell_media_featured_image.sizes.srcset[0]"
			:alt="post.sell_media_featured_image.alt" 
		/>

	</div>

</template>

<script>

	export default {

		props: ['post', 'size']

	}

</script>