<template>

	<div v-if="field == active" :class="className(field)">

		<div class="field" v-if="getSizes(post,field)">
			<!-- <label class="label">{{ field }} {{ labels.size }}</label> -->
			<div class="control">
				<div v-for="size in getSizes(post,field)" :key="size.id" class="radio" :class="{ wide: size.name === 'MOV' }">

					<input type="radio" :id="size.id" :value="size" v-model="selected" @change="$emit('selected', selected)" />

					<label :for="size.id">{{ size.name }}<span v-if="size.description"> ({{ size.description }})</span></label>

					<a v-if="user && size.name === 'S'" @click="downloadFile(size)" :title="labels.download" class="download">
						<icon v-if="downloading === size" name="circle-o-notch" spin></icon>
						<icon v-else name="download"></icon>
					</a>

				</div>
			</div>
		</div>

		<p class="total" v-if="selected.price">{{ labels.price }}: {{ labels.currency_symbol }}<span class="price">{{ selected.price }}</span></p>

	</div>

</template>

<script>
import mixinUser from '../../mixins/user'
import download from 'downloadjs'

export default {
  mixins: [mixinUser],

  props: ['post', 'field', 'active'],

  data: function() {
    return {
      selected: {},
      downloading: false,
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
      vm.downloading = size
      if( ! vm.user ) {
        return false;
      }

      vm.$http.get( '/wp-json/sell-media/v2/api', {
        params: {
          action: 'download_file',
          _wpnonce: sell_media.nonce,
          post_id: this.product.post_id,
          attachment_id: this.product.attachment_id,
          size_id: size ? size.id : 'original'
        }
      } )
        .then(( res ) => {
          let data = res.data;
          if( data.download ) {
            download(data.download);
          }
        } )
        .catch( ( res ) => {
          console.log( `Something went wrong : ${res}` );
        } );

      vm.downloading = false
    },
  }
}
</script>

<style lang="scss" scoped>

	$white: #fff;
	$light: #ddd;
	$primary: #1496ed;

	.total {
		margin-bottom: 1rem;
	}

	.radio {
		border: 1px solid $light;
		text-align: center;
		width: 50px;
		vertical-align: top;

		.expander & {
			border-color: $white;
			width: 36px;
		}

		&.wide {
			width: 50px;
		}
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
		padding: 16px 0;
		margin: 0;
		overflow: hidden;
		font-size: .75rem;

		.expander & {
			color: $white;
			padding: 12px 8px;
		}
	}

	input:hover + label,
	input:checked + label {
		color: $white;
	    background: $primary;
	}

	.download {
		border-top: 1px solid $light;
		display: block;
		padding: 10px;

		.expander & {
			border-color: $white;
			padding: 5px 10px;
		}

		&:hover {
			color: $white;
	    	background: $primary;
		}
	}

	.downloads-field {
		margin-bottom: 2rem;

		.expander & {
			float: left;
			margin: 0 .75rem 0 0;
		}
	}

</style>