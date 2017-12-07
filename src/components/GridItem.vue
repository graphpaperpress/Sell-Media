<template>
	<div class="column is-mobile is-one-third">
		<router-link :to="{ name: 'item', params: { slug:p.slug }}">
			<img v-if="p.sell_media_featured_image" v-bind:src="p.sell_media_featured_image.sizes.medium[0]" v-bind:alt="p.title.rendered">
			<h2>{{ p.title.rendered }}</h2>
		</router-link>
	</div>
</template>

<script>
	export default {
		props: ['p']
    }
</script>