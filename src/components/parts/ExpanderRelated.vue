<template>
	<div class="expander expander-related" ref="detailsBox" :style="{ height: height + 'px' }">
		<div class="expander-content">
			<button class="delete is-large" aria-label="close" @click="$emit('closeModal')"></button>

			<div class="columns">
				<div class="column is-half has-text-center">
					<media :post="post" :type="productType"></media>
				</div>
				<div class="column is-half has-text-left">

					<div class="cart-container columns">
						<div class="product-info column is-one-third">
							<p class="is-size-7" v-if="attachment">Product ID: <router-link :to="{ name: 'item', params: { slug: post.slug }}"><span class="is-uppercase">{{ attachment.slug }}</span></router-link></p>
							<p class="is-size-7" v-if="post.sell_media_meta.set && post.sell_media_meta.set[0]">Location ID: <span class="is-uppercase">{{ post.sell_media_meta.set[0].name }}</span></p>
						</div>
						<div class="column">
							<cart-form :key="post.slug" :post="post" :attachment="attachment" :multiple="multiple"></cart-form>
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
						:class="[ post.id === item.id ? 'is-light' : 'is-dark' ]"
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
						:class="[ post.id === item.id ? 'is-light' : 'is-dark' ]"
						:data-slug="item.slug">
						<template v-if="index < 9">0</template>{{ index + 1 }}
						</button>

					</div>

					<div v-if="otherSets.length > 0" class="buttons other-sets sets">
					
						<button
						v-for="(item,index) in otherSets"
						@click="getProductTypeSets(item)"
						class="button is-small"
						:class="[ post.sell_media_meta.product_type[0].name === item.sell_media_meta.product_type[0].name ? 'is-light' : 'is-dark' ]"
						:data-slug="item.slug">
						{{ item.sell_media_meta.product_type[0].name }}
						</button>

					</div>

					<div v-if="productType !== 'image'" id="slideshow-thumbnails" class="slideshow-thumbnails">
						<div
						:class="gridContainer" class="is-multiline has-text-centered">
							<div
							v-for="(item, index) in productTypeSets"
							@click="getPost(item)"
							:class="[{ active: post.id === item.id}, gridLayout]">
								<div class="slideshow-thumbnail">
									<img :src="item.sell_media_featured_image.sizes.medium[0]" :alt="item.sell_media_featured_image.title" />
								</div>
							</div>
						</div>
					</div>

					<portal-target v-if="productType === 'image'" name="slideshow-thumbnails"></portal-target>

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
				attachments: {},
				multiple: false,
				search_labels: sell_media.search_labels,
				cart_labels: sell_media.cart_labels,
				imageSets: [],
				videoSets: [],
				otherSets: [],
				productTypeSets: [],
				gridLayout: this.$store.getters.gridLayout,
				gridContainer: this.$store.getters.gridLayoutContainer,
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
			this.attachments = this.post.sell_media_attachments;
			let count = Object.keys(this.attachments);
			this.multiple = count.length > 1 ? true : false;
		},

		methods: {
			getPost: function(item) {
				this.post = item
				this.$store.commit( 'setProduct', { post_id: item.id, attachment_id: item.sell_media_attachments[0].id } )
			},
			getSets: function() {
				const vm = this
				let type = ( vm.post.sell_media_meta.product_type && vm.post.sell_media_meta.product_type[0].slug ) ? vm.post.sell_media_meta.product_type[0].slug : null
				// image and video product types should show child term, all others should show parent term.
				let set_id = ( type === 'image' || type === 'video' ) ? vm.post.sell_media_meta.set[1].term_id : vm.post.sell_media_meta.set[0].term_id

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
				console.log(vm.post)
				vm.$http.get( '/wp-json/wp/v2/sell_media_item', {
					params: {
						per_page: 20,
						set: vm.post.set ? vm.post.set[0] : null
					}
				} )
				.then( ( res ) => {
					let sets = res.data
					let other_sets = []
					let types = []

					for ( let set of sets ) {
						let type = set.sell_media_meta.product_type[0] ? set.sell_media_meta.product_type[0].slug : null
						let in_array = types.includes(type)
						if ( ! in_array && type !== 'image' && type !== 'video' ) {
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
				vm.post = item
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
				return this.post.sell_media_meta.product_type[0] ? this.post.sell_media_meta.product_type[0].slug : null
			},
			attachment: function() {
				return this.post.sell_media_attachments.find(attachment => attachment.id === this.$store.state.product.attachment_id)
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
			max-height: 432px;
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
		color: $white;
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
