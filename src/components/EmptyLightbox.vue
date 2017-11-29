<template id="empty-lightbox">
	<p v-on:click="emptyLightbox" class="empty-lightbox" title="{{ title }}">{{ title }}</p>
</template>

<script>
export default {
	data: function() {
		return {
			title: sell_media.text_lightbox_remove_all,
			title_empty: sell_media.text_lightbox_empty,
		}
	},
	methods: {
		emptyLightbox() {
			this.$cookie.delete('sell_media_lightbox');
		}
	}
}
</script>