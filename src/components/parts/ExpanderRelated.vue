<template>
	<div :class="name" class="expander expander-related">
		<div class="expander-content">
			<button class="delete is-large" aria-label="close" @click="$emit('closeModal')"></button>

			<div class="columns">
				<div class="column is-half has-text-center">
					<media :post="post" :type="type"></media>
				</div>
				<div class="column is-half has-text-left">

					<div class="cart-container columns">
						<div class="column is-one-fifth">
							<p class="is-size-7">Product ID: <router-link :to="{ name: 'item', params: { slug: post.slug }}">{{ post.title.rendered }}</router-link></p>
							<p class="is-size-7" v-if="post.sell_media_meta.set && post.sell_media_meta.set[0]">Location ID: {{ post.sell_media_meta.set[0].name }}</p>
						</div>
						<div class="column">
							<cart-form :key="post.slug" :post="post" :attachment="attachment" :multiple="multiple"></cart-form>
						</div>
					</div>

					<div class="set-container" v-if="setsLoaded">

						<div v-if="imageSets.length > 0" class="buttons image-sets sets">

							<button
							class="button is-small"
							:class="[ type === 'image' ? 'is-light' : 'is-dark' ]">
							Image Set No.
							</button>

							<button
							v-for="(set,index) in imageSets"
							@click="getPost(set)"
							class="button is-small"
							:class="[ post.id === set.id ? 'is-light' : 'is-dark' ]">
							<template v-if="index < 10">0</template>{{ index + 1 }}
							</button>

						</div>

						<div v-if="videoSets.length > 0" class="buttons video-sets sets">

							<button
							class="button is-small"
							:class="[ type === 'video' ? 'is-light' : 'is-dark' ]">
							Video Set No.
							</button>

							<button
							v-for="(set,index) in videoSets"
							@click="getPost(set)"
							class="button is-small"
							:class="[ post.id === set.id ? 'is-light' : 'is-dark' ]">
							<template v-if="index < 10">0</template>{{ index + 1 }}
							</button>

						</div>

						<div v-if="otherSets.length > 0" class="buttons other-sets sets">

							<button
							v-for="(set,index) in otherSets"
							@click="getPost(set)"
							class="button is-small"
							:class="[ post.id === set.id ? 'is-light' : 'is-dark' ]">
							{{ }}
							</button>

						</div>

					</div>

					<div v-else class="loading">
						<button class="button is-black is-loading">Loading...</button>
						<div class="is-size-7">loading related media</div>
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
				search_labels: sell_media.search_labels,
				cart_labels: sell_media.cart_labels,
				imageSets: [],
				videoSets: [],
				otherSets: [],
				setsLoaded: false,
				type: ''
			}
		},

		mounted: function() {
			document.addEventListener("keydown", (e) => {
				if (e.keyCode == 27) {
					this.$emit('closeModal');
				}
			});
			this.getSets()
			this.getType(this.post)
		},

		created: function() {
			this.attachments = this.post.sell_media_attachments;
			let count = Object.keys(this.attachments);
			this.multiple = count.length > 1 ? true : false;
			this.attachment = count.length > 0 ? this.attachments[0]: null;
		},

		methods: {
			next: function() {
				this.post += 1
			},
			prev: function() {
				this.post -= 1
			},
			getPost: function(set) {
				this.post = set
				this.type = this.getType(this.post)
			},
			getType: function() {
				this.type = this.post.sell_media_meta.product_type[0] ? this.post.sell_media_meta.product_type[0].slug : null
			},
			getSetType: function(set) {
				this.type = set.sell_media_meta.product_type[0] ? set.sell_media_meta.product_type[0].slug : null
			},
			getSets: function() {
				const vm = this;
				vm.$http.get( '/wp-json/wp/v2/sell_media_item', {
					params: {
						per_page: 20,
						set: vm.post.set ? vm.post.set[0] : null
					}
				} )
				.then( ( res ) => {
					let sets = res.data
					let image_sets = []
					let video_sets = []
					let other_sets = []

					for ( let set of sets ) {
						let type = vm.getSetType(set)
						if ( type === 'image' ) {
							image_sets.push(set)
						} else if ( type === 'video' ) {
							video_sets.push(set)
						} else {
							other_sets.push(set)
						}
					}

					vm.imageSets = image_sets
					vm.videoSets = video_sets
					vm.otherSets = other_sets
					vm.setsLoaded = true

				} )
				.catch( ( res ) => {
					console.log( res )
				} )
			},
		}
	}
</script>

<style lang="scss">

	.expander-related {
		background: #000;
		color: #eee;
		padding: 1rem;
		position: relative;
		max-height: 600px;
		transition: max-height .3s ease-in-out,
			margin-bottom .1s .2s;
		margin: .75rem 0 0;

		a:hover {
			color: #fff;
		}

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
		width: 32px;

		.label,
		.icon {
			border: 1px solid #3273dc;
			padding: .1rem;
			width: 100%;
		}

		.label {
			margin: 0;
			border-top-left-radius: 2px;
    		border-top-right-radius: 2px;
		}

		.icon {
			border-top: 0;
			border-bottom-left-radius: 2px;
    		border-bottom-right-radius: 2px;
    		transition: all .25s ease-in-out;

    		svg {
    			fill: #3273dc;
    			transition: all .25s ease-in-out;
    		}

    		&:hover {
    			background: #333;

    			svg {
					fill: #fff;
    			}
    		}
		}
	}

	// two cols
	.is-half .expander {
		width: calc( 200% + 1.5rem );
	}

	// three cols
	.is-one-third .expander {
		width: calc( 300% + 3rem );
	}

	// four cols
	.is-one-quarter .expander {
		width: calc( 400% + 4.5rem );
	}

	// 5 cols
	.is-one-fifth .expander {
		width: calc( 500% + 6rem );
	}

	.is-half:nth-of-type(2n+2),
	.is-one-third:nth-of-type(3n+2),
	.is-one-quarter:nth-of-type(4n+2),
	.is-one-fifth:nth-of-type(5n+2) {
		.expander {
			margin-left: calc( -100% - 1.5rem );
		}
	}

	.is-one-third:nth-of-type(3n+3),
	.is-one-quarter:nth-of-type(4n+3),
	.is-one-fifth:nth-of-type(5n+3) {
		.expander {
			margin-left: calc( -200% - 3rem );
		}
	}

	.is-one-quarter:nth-of-type(4n+4),
	.is-one-fifth:nth-of-type(5n+4) {
		.expander {
			margin-left: calc( -300% - 4.5rem );
		}
	}

	.is-one-fifth:nth-of-type(5n+5) .expander {
		margin-left: calc( -400% - 6rem );
	}

	.expander-content {
		width: 100%;
		height: auto;
	}
</style>
