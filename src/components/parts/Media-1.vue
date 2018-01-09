<template>
	<div class="column is-mobile is-one-third">
		<a v-bind:href="m.link" v-bind:title="m.title.rendered">
			<img v-bind:src="m.media_details.sizes.medium_large.source_url">
			<h2>{{ m.title.rendered }}</h2>
		</a>
	</div>
</template>

<script>
	export default {
		props: ['m'],
    }
</script>