<template>
	<div id="sell-media" class="sell-media">
		<!-- <app-nav></app-nav> -->
		<router-view></router-view>
	</div>
</template>

<script>
export default {
	name: 'app',
}
</script>

<style lang="sass">
	@import '~bulma/bulma.sass';
	@import '~bulma-steps-component/bulma-steps.sass';
	@import '~bulma-checkradio/checkradio.sass';

	.sell-media {
		box-sizing: border-box;
		margin: 3rem 0;
	}

	$base-font-size: 16px;
	$heading-scale: 4; // amount of px headings grow from h6 to h1

	@for $i from 1 through 6 {
		h#{$i} {
			font-size: $base-font-size + $heading-scale * (6 - $i);
		}
	}
</style>
