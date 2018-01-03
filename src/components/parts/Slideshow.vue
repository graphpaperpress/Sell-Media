<template>

	<div :class="slideshow">
		<p>
			<a @click.prevent="prev">{{ prev_label }}</a> | <a @click.prevent="next">{{ next_label }}</a>
		</p>
		
		<div
			 v-for="slide in [currentSlide]"
			 transition="fade"
			 >
			<img
				:src="attachments[Math.abs(currentSlide) % attachments.length].sizes.large[0]" :alt="slide.alt"
			/>
		</div>
	</div>

</template>

<script>

	export default {

		props: ['attachments'],

		data: function() {
			return {
				currentSlide: 0,
				prev_label: sell_media.cart_labels.prev,
				next_label: sell_media.cart_labels.next,
			}
		},

		mounted: function() {
			let attachment = this.attachments[this.currentSlide]
			this.$emit('attachment', attachment)
		},

		methods: {

			next: function(event) {
				this.currentSlide += 1
				let attachment = this.attachments[this.currentSlide]
				if (attachment !== undefined)
					this.$emit('attachment', attachment)
				else
					event.preventDefault()
			},
			prev: function(event) {
				this.currentSlide -= 1
				let attachment = this.attachments[this.currentSlide]
				if (attachment !== undefined)
					this.$emit('attachment', attachment)
			}
		}
	}
</script>

<style lang="scss" scoped>

	.fade {
		transition: all 0.8s ease;
		overflow: hidden;
		visibility: visible;
		opacity: 1;
		position: absolute;
	}
	.fade-enter,
	.fade-leave {
		opacity: 0;
		visibility: hidden;
	}

</style>