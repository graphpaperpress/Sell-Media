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
  import mixinProduct from '../../mixins/product'

	export default {
    mixins: [mixinProduct],

		props: ['post', 'size'],

		mounted: function() {
			let attachment = this.post.sell_media_attachments[0]
			this.$store.dispatch( 'setProduct', { post_id: attachment.parent, attachment_id: attachment.id } )
		}

	}

</script>