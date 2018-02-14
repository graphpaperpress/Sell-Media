<template>
	<div class="modal is-active">
		<div class="modal-background" @click="$emit('closeModal')"></div>
		<div class="modal-content">

<!-- 			<div class="modal-nav">
				<a @click="prev" class="modal-nav-prev">{{ prev_label }}</a>
				<a @click="next" class="modal-nav-next">{{ next_label }}</a>
			</div> -->

			<div class="modal-columns">
				<div class="modal-column-main has-text-center">
					<template v-if="multiple">
						<media-slideshow :post="post" :attachments="attachments" @attachment="setAttachment"></media-slideshow>
					</template>
					<template v-else>
						<figure>
							<featured-image :post="post" @attachment="setAttachment" :size="image_size" ></featured-image>
						</figure>
					</template>
				</div>
				<div class="modal-column-sidebar has-text-left">
					<div class="cart-form">

						<p class="title is-5">{{post.title.rendered}}</p>

						<cart-form :key="post.slug" :post="post" :attachment="attachment" :multiple="multiple"></cart-form>

					</div>
				</div>
			</div>
		</div>
		<button class="modal-close is-large" aria-label="close" @click="$emit('closeModal')"></button>
	</div>
</template>

<script>

import MediaSlideshow from './MediaSlideshow.vue';

	export default {

		props: ['post'],

		data: function () {
			return {
				attachment: {},
				attachments: {},
				image_size: 'large',
				multiple: false,
				currentModal: 0,
				prev_label: sell_media.cart_labels.prev,
				next_label: sell_media.cart_labels.next,
			}
		},

		mounted: function() {
			document.addEventListener("keydown", (e) => {
				if (e.keyCode == 27) {
					this.$emit('closeModal');
				}
			});

		},

		created: function() {
			this.attachments = this.post.sell_media_attachments;
			let count = Object.keys(this.attachments);
			this.multiple = count.length > 1 ? true : false;
		},

		methods: {
			next: function() {
				this.post += 1
			},
			prev: function() {
				this.post -= 1
			},
			setAttachment: function(data) {
				this.attachment = data
			}
		},

		components: {
			'media-slideshow': MediaSlideshow
		}
	}
</script>

<style lang="scss" scoped>
	
	// Varaibles
	$white: #fff;

	.modal {
		z-index: 1000;
	}

	.modal-content {
		background: $white;
		padding: 1rem;
		@media print, screen and (min-width: 769px) {
			width: 960px;
    		max-width: 90%;
    		max-height: calc(100vh - 20%);

			.modal-columns {
				float: left;
			}

			.modal-column-main {
				width: 65%;
				float: left;
				margin-right: 1rem;
			}

			.modal-column-sidebar {
				width: calc(35% - 1rem);
				float: left;
			}
		}
	}
</style>
