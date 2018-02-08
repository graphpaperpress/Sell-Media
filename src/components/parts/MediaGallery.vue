<template>

	<div class="image-gallery">

		<p class="subtitle">This gallery contains <strong>{{ attachments.length }}</strong> images</p>

		<div class="columns is-multiline">
			<div v-for="attachment in attachments" class="column is-mobile is-one-third">
				<router-link :to="{ name: 'attachment', params: { prefix: post.slug, slug: attachment.slug }}">
					<img v-if="attachment.sizes[thumbnailCrop][0]" v-bind:src="attachment.sizes[thumbnailCrop][0]" v-bind:alt="attachment.alt">
				</router-link>
			</div>
		</div>
	
	</div>
</template>

<script>
	export default {
		props: [ 'post'],

		data: function () {
			return {
				attachments: this.post.sell_media_attachments,
				thumbnailCrop: sell_media.thumbnail_crop,
				showTitle: sell_media.title
			}
		}
    }
</script>