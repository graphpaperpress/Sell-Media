<template>
	<div :class="name" class="expander">
		<div class="expander-content">
			<button class="delete is-large" aria-label="close" @click="$emit('closeModal')"></button>

			<div class="columns">
				<div class="column has-text-center">
					<template v-if="multiple">
						<slideshow :attachments="attachments" @attachment="setAttachment"></slideshow>
					</template>
					<template v-else>
						<figure>
							<featured-image :post="post" @attachment="setAttachment" :size="image_size"></featured-image>
						</figure>
					</template>
				</div>
				<div class="column has-text-left">
					<div class="cart-form">

						<p class="title is-5">{{post.title.rendered}}</p>

						<cart-form :key="post.slug" :post="post" :attachment="attachment" :multiple="multiple"></cart-form>

					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>

import Slideshow from './Slideshow.vue';

	export default {

		props: ['post'],

		data: function () {
			return {
				image_size: 'large',
				attachments: {},
				multiple: false,
				currentModal: 0,
				prev_label: sell_media.cart_labels.prev,
				next_label: sell_media.cart_labels.next,
				attachment: {}
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
			'slideshow': Slideshow
		}
	}
</script>

<style lang="scss">

	.expander {
		background: #333;
		padding: 1rem;
		position: relative;
		max-height: 600px;
		transition: max-height .3s ease-in-out,
			margin-bottom .1s .2s;
		margin: .75rem 0 0;

		img {
			max-height: 450px;
		}

		.title,
		.label,
		.content,
		.button.is-text {
			color: #eee;
		}

		.delete {
			position: absolute;
			right: 0.5em;
			top: 0.5em;
		}

		.button.is-text:disabled:hover {
			color: #fff;
		}
	}

	// two cols
	.is-half .expander {
		width: calc( 200% + 10px );
	}

	// three cols
	.is-one-third .expander {
		width: calc( 300% + 20px );
	}

	// four cols
	.is-one-quarter .expander {
		width: calc( 400% + 30px );
	}

	// 5 cols
	.is-one-fifth .expander {
		width: calc( 500% + 40px );
	}

	.is-half:nth-of-type(2n+2),
	.is-one-third:nth-of-type(3n+2),
	.is-one-quarter:nth-of-type(4n+2),
	.is-one-fifth:nth-of-type(5n+2) {
		.expander {
			margin-left: calc( -100% - 10px );
		}
	}

	.is-one-third:nth-of-type(3n+3),
	.is-one-quarter:nth-of-type(4n+3),
	.is-one-fifth:nth-of-type(5n+3) {
		.expander {
			margin-left: calc( -200% - 20px );
		}
	}

	.is-one-quarter:nth-of-type(4n+4),
	.is-one-fifth:nth-of-type(5n+4) {
		.expander {
			margin-left: calc( -300% - 30px );
		}
	}

	.is-one-fifth:nth-of-type(5n+5) .expander {
		margin-left: calc( -400% - 40px );
	}

	.expander-content {
		width: 100%;
		height: auto;
	}
</style>
