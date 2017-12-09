<template>
	<div class="column is-mobile is-one-third">
		<router-link :to="{ name: 'item', params: { slug:post.slug }}">
			<img v-if="post.sell_media_featured_image" v-bind:src="post.sell_media_featured_image.sizes.medium[0]" v-bind:alt="post.alt">
		</router-link>	
		<h2>{{ post.title.rendered }}</h2>
		<modal v-if="showModal" @closeModal="showModal = false" :post="post"></modal>
		<button class="button" @click="showModal = true">Quick View</button>
	</div>
</template>

<script>
	export default {

		props: ['post'],

		data: function () {
			return {
				showModal: false,
				quick_view_label: sell_media.quick_view_label
			}
		}
    }
</script>