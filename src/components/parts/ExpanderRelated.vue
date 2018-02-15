<template>
	<div class="expander expander-related" ref="detailsBox" :style="{ height: height + 'px' }">
		<div class="expander-content">
			<button class="delete is-large" aria-label="close" @click="$emit('closeModal')"></button>

			<div class="columns">
				<div class="column media-column is-half has-text-center">
					<media :post="currentPost" :type="productType" :showSlideshow="showSlideshow"></media>
				</div>
				<div class="column nav-column is-half has-text-left">

					<div class="cart-container columns">
						<div class="product-info column is-one-third">
							<p class="is-size-7" v-if="attachment">Product ID: <router-link :to="{ name: 'attachment', params: { prefix: currentPost.slug, slug: attachment.slug }}"><span class="is-uppercase">{{ attachment.slug }}</span></router-link></p>
							<p class="is-size-7" v-if="currentPost.sell_media_meta.set && currentPost.sell_media_meta.set[0]">Location ID: <span class="is-uppercase"><router-link :to="{ name: 'archive', query: { search: currentPost.sell_media_meta.set[0].slug }}">{{ currentPost.sell_media_meta.set[0].name }}</router-link></span></p>
						</div>
						<div class="column">
							<cart-form :key="currentPost.slug" :post="currentPost" :attachment="attachment" :multiple="multiple"></cart-form>
						</div>
					</div>

					<div v-if="imageSets.length > 0" class="buttons image-sets sets">

						<button
						@click="getPost(imageSets[0])"
						class="button is-small"
						:class="[ productType === 'image' ? 'is-light' : 'is-dark' ]">
						Image Set No.
						</button>

						<button
						v-for="(item,index) in imageSets"
						v-if="index < 10"
						@click="getPost(item)"
						class="button is-small"
						:class="[ currentPost.id === item.id ? 'is-light' : 'is-dark' ]"
						:data-slug="item.slug">
						<template v-if="index < 9">0</template>{{ index + 1 }}
						</button>

					</div>

					<div v-if="videoSets.length > 0" class="buttons video-sets sets">

						<button
						@click="getPost(videoSets[0])"
						class="button is-small"
						:class="[ productType === 'video' ? 'is-light' : 'is-dark' ]">
						Video Set No.
						</button>

						<button
						v-for="(item,index) in videoSets"
						v-if="index < 10"
						@click="getPost(item)"
						class="button is-small"
						:class="[ currentPost.id === item.id ? 'is-light' : 'is-dark' ]"
						:data-slug="item.slug">
						<template v-if="index < 9">0</template>{{ index + 1 }}
						</button>

					</div>

					<div v-if="otherSets.length > 0" class="buttons other-sets sets">

						<button
						v-for="(item,index) in otherSets"
						@click="getProductTypeSets(item)"
						class="button is-small"
						:class="[ currentPost.sell_media_meta.product_type[0].name === item.sell_media_meta.product_type[0].name ? 'is-light' : 'is-dark' ]"
						:data-slug="item.slug">
						{{ item.sell_media_meta.product_type[0].name }}
						</button>

					</div>

					<div v-if="productType !== 'image' && productType !== 'video'" :id="thumbnailStyle + '-thumbnails'" :class="thumbnailStyle + '-thumbnails'">
						<div
						:class="gridContainer" class="is-multiline has-text-centered">
							<div
							v-for="(item, index) in productTypeSets"
							@click="getPost(item)"
							:class="[{ active: currentPost.id === item.id}, gridLayout]">
								<div :class="thumbnailStyle + '-thumbnail'">
									<img :src="item.sell_media_featured_image.sizes.medium[0]" :alt="item.sell_media_featured_image.title" />
								</div>
							</div>
						</div>
					</div>

					<portal-target v-if="productType === 'image'" name="slideshow-thumbnails"></portal-target>
					<portal-target v-if="productType === 'video'" name="video-thumbnails"></portal-target>

				</div>

			</div>

		</div>
	</div>
</template>

<script>
  import mixinProduct from '../../mixins/product'
	export default {
    mixins: [mixinProduct],

		props: ['post'],

		data: function () {
			return {
				user: this.$store.getters.user,
				currentPost: this.post,
				attachments: {},
				showSlideshow: true,
				multiple: false,
				search_labels: sell_media.search_labels,
				cart_labels: sell_media.cart_labels,
				imageSets: [],
				videoSets: [],
				otherSets: [],
				productTypeSets: [],
				gridLayout: this.$store.getters.gridLayout,
				gridContainer: this.$store.getters.gridLayoutContainer,
				thumbnailStyle: 'slideshow',
				height: 600,
			}
		},

		mounted: function() {
			document.addEventListener("keydown", (e) => {
				if (e.keyCode == 27) {
					this.$emit('closeModal');
				}
			});

			this.getSets()
			this.getOtherSets()
		},

		created: function() {
			this.attachments = this.currentPost.sell_media_attachments;
			let count = Object.keys(this.attachments);
			this.multiple = count.length > 1 ? true : false;
		},

		methods: {
			getPost: function(item) {
				this.currentPost = item
				this.$store.dispatch( 'setProduct', { post_id: item.id, attachment_id: item.sell_media_attachments[0].id } )
			},
			getSets: function() {
				const vm = this
				let type = ( vm.currentPost.sell_media_meta.product_type && vm.currentPost.sell_media_meta.product_type[0].slug ) ? vm.currentPost.sell_media_meta.product_type[0].slug : null
				// image and video product types should show child term, all others should show parent term.
				let set_id = ( type === 'image' || type === 'video' ) ? vm.currentPost.sell_media_meta.set[1].term_id : vm.currentPost.sell_media_meta.set[0].term_id

				if ( ! set_id ) {
					vm.setsLoaded = true
					return false
				}

				vm.$http.get( '/wp-json/wp/v2/sell_media_item', {
					params: {
						per_page: 20,
						set: set_id
					}
				} )
				.then( ( res ) => {
					let sets = res.data
					let image_sets = []
					let video_sets = []

					for ( let set of sets ) {
						if ( set.sell_media_meta.product_type && set.sell_media_meta.product_type[0].slug === 'image' ) {
							image_sets.push(set)
						}
						if ( set.sell_media_meta.product_type && set.sell_media_meta.product_type[0].slug === 'video' ) {
							video_sets.push(set)
						}
					}

					vm.imageSets = image_sets
					vm.videoSets = video_sets

				} )
				.catch( ( res ) => {
					console.log( res )
				} )
			},
			getOtherSets: function() {
				const vm = this;
				// Search API response includes set with parent_id, WP API returns array of indexed ids
				// Need to make these consistent in the future
				let post_set = vm.currentPost.set.parent_id ? vm.currentPost.set.parent_id : vm.currentPost.set[0]
				vm.$http.get( '/wp-json/wp/v2/sell_media_item', {
					params: {
						per_page: 20,
						set: post_set,
						product_type_exclude: [6,7], // CHANGE THIS! IT'S HARDCODED. Exclude image and video product_types (term_id 6 and 7)
					}
				} )
				.then( ( res ) => {
					let sets = res.data
					let other_sets = []
					let types = []

					for ( let set of sets ) {
						let type = set.sell_media_meta.product_type[0] ? set.sell_media_meta.product_type[0].slug : null
						let in_array = types.includes(type)
						if ( ! in_array ) {
							types.push(type)
							other_sets.push(set)
						}
					}

					vm.otherSets = other_sets

				} )
				.catch( ( res ) => {
					console.log( res )
				} )
			},
			getProductTypeSets: function(item) {
				const vm = this
				vm.currentPost = item
				vm.$http.get( '/wp-json/wp/v2/sell_media_item', {
					params: {
						per_page: 20,
						set: item.set ? item.set[0] : null,
						product_type: item.product_type ? item.product_type[0] : null
					}
				} )
				.then( ( res ) => {
					let sets = res.data
					let product_type_sets = []

					for ( let set of sets ) {
						product_type_sets.push(set)
					}

					vm.productTypeSets = product_type_sets
				} )
				.catch( ( res ) => {
					console.log( res )
				} )
			}
		},

		updated: function () {
			this.$nextTick(function () {
				this.height = this.$refs.detailsBox.clientHeight
			})
		},

		computed: {
			productType: function () {
				let type = this.currentPost.sell_media_meta.product_type[0] ? this.currentPost.sell_media_meta.product_type[0].slug : null
				if ( '360-video' === type || 'video' === type ) {
					this.thumbnailStyle = 'video'
				}
				return type
			},
			attachment: function() {
				return this.currentPost.sell_media_attachments.find(attachment => attachment.id === this.$store.getters.product.attachment_id)
			}
		}
	}
</script>

<style lang="scss">

	$black: #000;
	$dark: #333;
	$white: #fff;
	$blue: #3273dc;

	.expander-related {
		background: $black;
		color: darken( $white, 5% );
		transition: max-height .3s ease-in-out,
			margin-bottom .1s .2s;
		position: absolute;
		top: auto;
		left: 0;
		height: 600px;
		margin: .75rem 0 0;
		padding: 2.617924em;
		width: 100%;
		overflow: hidden;
		z-index: 1;

		a:hover {
			color: $white;
		}

		img {
			display: block;
			max-height: 432px;
		}

		.media-column {
			.featured-image,
			.slideshow {
				float: right;
			}
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
			color: $white;
		}

		.total {
			display: none;
			margin-bottom: 0 !important;
		}

	}

	.has-expander-related {

		.quick-view {
			position: absolute;
			top: 0;
			left: 0;
			padding: 10px;
			background: transparent !important;
			text-indent: -999rem;
			display: block;
			width: 100%;
			height: 100%;
			color: $white;
			z-index: 2;
			text-transform: uppercase;
			font-size: .8rem;
			letter-spacing: 1px;
			cursor: zoom-in;
		}
	}

	.product-info a {
		font-weight: 900;
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
			border: 1px solid $blue;
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
    			fill: $blue;
    			transition: all .25s ease-in-out;
    		}

    		&:hover {
    			background: $dark;

    			svg {
					fill: $white;
    			}
    		}
		}
	}

	.expander-content {
		width: 100%;
		height: auto;
	}
</style>
