<template>

	<div v-if="field == active" :class="className(field)">

		<div class="field" v-if="getSizes(post,field)">
			<!-- <label class="label">{{ field }} {{ labels.size }}</label> -->
			<div class="control">
				<div v-for="size in getSizes(post,field)" class="radio">
					
					<input type="radio" :id="size.id" :value="size" v-model="selected" @change="$emit('selected', selected)" />
					
					<label :for="size.id">{{ size.name }}<span v-if="size.description"> ({{ size.description }})</span></label>

					<a v-if="user" @click="downloadFile(size)" :title="labels.download" :download="file" class="download">
						<icon name="download"></icon>
					</a>

				</div>
			</div>
		</div>
	
		<p class="total">{{ labels.price }}: {{ labels.currency_symbol }}<span v-if="selected.price">{{ selected.price }}</span>
			<span v-else>0</span>
		</p>

	</div>

</template>

<script>

	export default {

		props: ['post', 'field', 'active'],

		data: function() {
			return {
				user: this.$store.state.user,
				selected: {},
				file: '',
				labels: {
					price: sell_media.cart_labels.price,
					choose: sell_media.cart_labels.choose,
					size: sell_media.cart_labels.size,
					required: 'Please make a selection',
					currency_symbol: sell_media.currency_symbol,
					download: sell_media.search_labels.download
				}
			}
		},

		methods: {

			className: function(field) {
				return field.toLowerCase().replace(/ /g, '-') + '-field';
			},

			getSizes: function(post,field) {
				let taxonomy = field.toLowerCase()
				if ( post['sell_media_pricing'][taxonomy] ) {
					return post['sell_media_pricing'][taxonomy]
				} else {
					this.$emit('selected', true)
				}
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
						// not working, but this approach would be best
						this.file = '/wp-content/uploads/downloads/' + data.file
					}
				} )
				.catch( ( res ) => {
					console.log( `Something went wrong : ${res}` );
				} );
			},
		}
	}
</script>

<style lang="scss" scoped>

	$white: #fff;
	$primary: #1496ed;

	.total {
		margin-bottom: 1rem;
	}

	.radio {
		text-align: center;
		width: 36px;
		border: 1px solid $white;
	}

	input[type="radio"] {
		visibility: hidden;
		-webkit-appearance: none;
		-moz-appearance: none;
		-ms-appearance: none;
		-o-appearance: none;
		appearance: none;
		position: absolute;
	}

	label {
		cursor: pointer;
		display: block;
		padding: 8px;
		width: 34px;
		height: 34px;
		overflow: hidden;
		font-size: .75rem;
	}

	input:hover + label,
	input:checked + label {
		color: $white;
	    background: $primary;
	}

	.download {
		border-top: 1px solid $white;
		display: block;
		width: 100%;
		padding: 5px 10px;

		&:hover {
			color: $white;
	    	background: $primary;
		}
	}

	.downloads-field {
		float: left;
		margin-right: .75rem;
	}

</style>