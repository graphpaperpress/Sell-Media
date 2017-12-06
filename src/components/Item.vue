<template>
	<div class="column is-mobile is-one-third">
		<a v-bind:href="i.link" v-bind:title="i.title.rendered">
			<img v-bind:src="i.media_details.sizes.medium_large.source_url">
			<h2>{{ i.title.rendered }}</h2>
		</a>
	</div>
</template>

<script>
	export default {
		props: ['i'],
    }
</script>