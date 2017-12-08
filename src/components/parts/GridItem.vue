<template>
	<div class="column is-mobile is-one-third">
		<router-link :to="{ name: 'item', params: { slug:p.slug }}">
			<img v-if="p.sell_media_featured_image" v-bind:src="p.sell_media_featured_image.sizes.medium[0]" v-bind:alt="p.alt">
		</router-link>	
		<h2>{{ p.title.rendered }}</h2>
		<sm-modal v-if="showModal" @closeModal="showModal = false" v-bind:p="p"></sm-modal>
		<button class="button" @click="showModal = true">Quick View</button>
	</div>
</template>

<script>
	export default {

		props: ['p'],

		data: function () {
			return {
				showModal: false,
				quick_view_label: sell_media.quick_view_label
			}
		}
    }
</script>