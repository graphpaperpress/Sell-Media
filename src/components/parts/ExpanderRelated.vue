<template>
	<div :class="name" class="expander expander-related">
		<div class="expander-content">
			<button class="delete is-large" aria-label="close" @click="$emit('closeModal')"></button>

			<div class="columns">
				<div class="column is-half has-text-center">
					<media :post="post" @attachment="setAttachment"></media>
				</div>
				<div class="column is-half has-text-left">

					<div class="cart-container columns">
						<div class="column is-one-fifth">
							<p class="is-size-7">Image ID: <router-link :to="{ name: 'item', params: { slug:post.slug }}">{{ post.id }}</router-link></p>
							<p class="is-size-7">Location ID: {{  }}</p>
						</div>
						<div class="column">
							<div v-if="multiple" class="multiple-selector buttons has-addons">
								<div v-for="size in sizes" class="button-block">
									<div class="label">{{ size.label }}</div>
									<div v-if="user" class="icon">
										<icon name="download"></icon>
									</div>
								</div>
							</div>
							<div v-else class="single-selector">
								<button v-if="user" class="button is-outlined is-link" :title="labels.download">
									<span class="icon">
										<icon name="download"></icon>
									</span>
								</button>
							</div>
						</div>
						<div class="column">
							<div class="buttons">
								<button class="button is-link">Add To Cart</button>
								<button class="button is-link">
									<span class="icon">
										<icon name="heart"></icon>
									</span>
								</button>
							</div>
						</div>
					</div>

					<div class="set-container">
						<div class="buttons image-sets sets">
							<button class="button is-dark is-small">Image Set No.</button>
							<button v-for="(set,index) in imageSets" class="button is-dark is-small">{{ set }}</button>
						</div>
						<div class="buttons video-sets sets">
							<button class="button is-dark is-small">Video Set No.</button>
							<button v-for="(set,index) in videoSets" class="button is-dark is-small">{{ set }}</button>
						</div>
						<div class="buttons other-sets sets">
							<button v-for="(set,index) in otherSets" class="button is-dark is-small">{{ set }}</button>
						</div>
					</div>

					<portal-target name="slideshow-thumbnails"></portal-target>

				</div>

			</div>

		</div>
	</div>
</template>

<script>

	export default {

		props: ['post'],

		data: function () {
			return {
				user: this.$store.state.user,
				attachment: {},
				attachments: {},
				multiple: false,
				labels: sell_media.search_labels,
				prev_label: sell_media.cart_labels.prev,
				next_label: sell_media.cart_labels.next,
				sizes: {
					0: {
						name: 'Small',
						label: 'S',
						description: '',
						price: '200'
					},
					1: {
						name: 'Medium',
						label: 'M',
						description: '',
						price: '350'
					},
					2: {
						name: 'Large',
						label: 'L',
						description: '',
						price: '500'
					},
					3: {
						name: 'Extra Large',
						label: 'XL',
						description: '',
						price: '750'
					},
					4: {
						name: 'TIF',
						label: 'TIF',
						description: '',
						price: '1000'
					}
				},
				imageSets: ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10'], // get from rest api, image product types in same set as current
				videoSets: ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10'], // get from rest api, video product types in same set as current
				otherSets: ['360°R Dome', 'HDR Dome', '360° Video', 'VR Environment', '3D Object'] // get from rest api, other product types in same set as current
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
		}
	}
</script>

<style lang="scss">

	.expander-related {
		background: #222;
		color: #eee;
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

	.set-container {
		margin: 1rem 0;
	}

	.sets {
		&.buttons:not(:last-child) {
			margin-bottom: .25rem;
		}
		.button {
			padding: .25rem;
		}

	}

	.block-level {
		display: block;
	}

	.button-block {
		text-align: center;
		margin-right: .25rem;
		width: 38px;

		.label,
		.icon {
			padding: .25rem;
			width: 100%;
		}

		.label {
			background-color: #3273dc;
			margin: 0;
			border-top-left-radius: 2px;
    		border-top-right-radius: 2px;
		}

		.icon {
			background-color: lighten( #3273dc, 10% );
			border-bottom-left-radius: 2px;
    		border-bottom-right-radius: 2px;
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
