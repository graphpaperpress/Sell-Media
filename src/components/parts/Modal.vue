<template>
	<div v-bind:id="name" v-bind:class="name" class="modal is-active">
		<div class="modal-background" @click="$emit('closeModal')"></div>
		<div class="modal-content">
			<div class="columns">
				<div class="column is-two-thirds">
					<figure class="image">
						<img
							:src="post.sell_media_featured_image.sizes.large[0]" 
							:data-srcset="post.sell_media_featured_image.sizes.srcset[0]"
							:alt="post.sell_media_featured_image.alt" 
						/>
					</figure>
				</div>
				<div class="column">
					<div class="cart-form">

						<p class="title is-5">{{post.title.rendered}}</p>

						<cart-form :key="post.slug" :post="post"></cart-form>

					</div>
				</div>
			</div>
		</div>
		<button class="modal-close is-large" aria-label="close" @click="$emit('closeModal')"></button>
	</div>
</template>

<script>

	export default {

		props: ['post'],

		data: function () {
			return {
			}
		}
	}
</script>

<style lang="scss" scoped>
	
	// Varaibles
	$white: #fff;

	.modal-content {
		background: $white;
		@media print, screen and (min-width: 769px) {
			width: 940px;
		}
	}

	.cart-form {
		margin: 2rem 2rem 2rem .5rem;
	}
</style>
