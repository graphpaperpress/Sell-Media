<template>
	<div class="column is-mobile is-one-third">
		<a v-bind:href="p.link" v-bind:title="p.title.rendered">
			<img v-bind:src="p.media_details.sizes.medium_large.source_url">
			<h2>{{ p.title.rendered }}</h2>
		</a>
	</div>
</template>

<script>
	
	export default {

		props: ['p'],

    }

</script>