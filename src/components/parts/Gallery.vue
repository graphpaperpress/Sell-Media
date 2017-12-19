<template>
	<div class="columns is-multiline">
		<div v-for="attachment in attachments" class="column is-mobile is-one-third">
			<router-link :to="{ name: 'attachment', params: { prefix: prefix, slug: attachment.slug }}">
				<img v-if="attachment.sizes[thumbnailCrop][0]" v-bind:src="attachment.sizes[thumbnailCrop][0]" v-bind:alt="attachment.alt">
				<h2>{{ attachment.title }}</h2>
			</router-link>
		</div>
	</div>
</template>

<script>
	export default {
		props: [ 'attachments', 'prefix' ],

		data: function () {
			return {
				thumbnailCrop: sell_media.thumbnail_crop,
			}
		}
    }
</script>