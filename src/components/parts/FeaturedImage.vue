<template>
	<div class="featured-image">
		<img
			:src="post.sell_media_featured_image.sizes[size][0]"
			:data-srcset="post.sell_media_featured_image.sizes.srcset[0]"
			:alt="post.sell_media_featured_image.alt"/>
	</div>

</template>

<script>
import { mapActions } from "vuex"

export default {
  props: ['post', 'size'],

  methods: {
    ...mapActions(["setProduct"])
  },

  mounted: function() {
    const attachment = this.post.sell_media_attachments[0]
    this.setProduct({ post_id: attachment.parent, attachment_id: attachment.id })
  }

}

</script>
