<template>
	<div :class="name" class="expander expander-related">
		<div class="expander-content">
			<button class="delete is-large" aria-label="close" @click="$emit('closeModal')"></button>

			<div class="columns">
				<div class="column is-half has-text-center">
					<media :post="post"></media>
				</div>
				<div class="column is-half has-text-left">

					<div class="cart-container columns">
						<div class="column is-one-fifth">
							<p class="is-size-7">Product ID: <router-link :to="{ name: 'item', params: { slug:post.slug }}">{{ post.title.rendered }}</router-link></p>
							<p class="is-size-7" v-if="post.sell_media_meta.set && post.sell_media_meta.set[0]">Location ID: {{ post.sell_media_meta.set[0].name }}</p>
						</div>
						<div class="column">
							<div v-if="multiple" class="multiple-selector buttons has-addons">
								<div v-for="size in post.sell_media_pricing.downloads" class="button-block" :data-size-id="size.id">
									<div class="label is-size-7">{{ size.name }}</div>
									<a v-if="user" class="icon" @click="downloadFile(size)" :title="search_labels.download" :download="file">
										<icon name="download"></icon>
									</a>
								</div>
							</div>
							<div v-else class="single-selector">
								<button v-if="user" class="button is-outlined is-link" :title="search_labels.download" @click="downloadFile({ 'id': 'original' })">
									<span class="icon">
										<icon name="download"></icon>
									</span>
								</button>
							</div>
						</div>
						<div class="column">
							<div class="buttons">
								<button class="button is-link">{{ cart_labels.add_to_cart }}</button>
								<button class="button is-link">
									<span class="icon">
										<icon name="heart"></icon>
									</span>
								</button>
							</div>
						</div>
					</div>
					
					<div class="set-container">

						<div v-if="imageSets.length > 0" class="buttons image-sets sets">
							<button class="button is-dark is-small" :class="{ 'is-light': active }">Image Set No.</button>
							<button v-for="(set,index) in imageSets" @click="getPost(set)" class="button is-dark is-small" :class="{ 'is-light': active }"><template v-if="index < 10">0</template>{{ index }}</button>
						</div>
						<div v-if="videoSets.length > 0" class="buttons video-sets sets">
							<button class="button is-dark is-small">Video Set No.</button>
							<button v-for="(set,index) in videoSets" @click="getPost(set)" class="button is-dark is-small"><template v-if="index < 10">0</template>{{ index }}</button>
						</div>
						<div class="buttons other-sets sets">
							<button v-for="(set,index) in otherSets" @click="getPost(set)" class="button is-dark is-small">{{ set.sell_media_meta.product_type[0].name }}</button>
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
				attachment_id: this.$store.state.product.attachment_id,
				attachments: {},
				multiple: false,
				search_labels: sell_media.search_labels,
				cart_labels: sell_media.cart_labels,
				imageSets: [],
				videoSets: [],
				otherSets: [],
				file: '',
				active: false
			}
		},

		mounted: function() {
			document.addEventListener("keydown", (e) => {
				if (e.keyCode == 27) {
					this.$emit('closeModal');
				}
			});
			this.getSets()
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
			getPost: function(set) {
				this.post = set
				this.active = this.post.id === set.id ? true : false
			},
			downloadFile: function(size) {
				const vm = this;
				if( ! vm.user ) {
					return false;
				}

				vm.$http.get( '/wp-json/sell-media/v2/api', {
					params: {
						action: 'download_file',
						_wpnonce: sell_media.nonce,
						post_id: this.$store.state.product.post_id,
						attachment_id: this.$store.state.product.attachment_id,
						size_id: size ? size.id : 'original'
					}
				} )
				.then( ( res ) => {
					let data = res.data;
					console.log(res)
					if( data.file ) {
						window.open( '/wp-content/uploads/downloads/' + data.file, '_blank');
						this.file = '/wp-content/uploads/downloads/' + data.file
					}
				} )
				.catch( ( res ) => {
					console.log( `Something went wrong : ${res}` );
				} );
			},
			getSets: function() {
				const vm = this;
				vm.loaded = false;
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
						if ( set.sell_media_meta.product_type[0].slug === 'image' ) {
							image_sets.push(set)
						} else if ( set.sell_media_meta.product_type[0].slug === 'video' ) {
							video_sets.push(set)
						} else {
							other_sets.push(set)
						}
					}

					vm.imageSets = image_sets
					vm.videoSets = video_sets
					vm.otherSets = other_sets

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
		background: #222;
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
