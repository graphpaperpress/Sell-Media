<template>

	<div :class="slideshow">
		<p>
			<button class="button is-text" @click="goPrev" :disabled="prev.disabled">{{ prev.label }}</button> {{ currentSlide + 1 }} of {{ attachments.length }} <button class="button is-text" @click="goNext" :disabled="next.disabled">{{ next.label }}</button>
		</p>
		
		<div
			 v-for="slide in [currentSlide]"
			 transition="fade"
			 >
			<img
				:src="attachments[Math.abs(currentSlide) % attachments.length].sizes.large[0]"
				:alt="slide.alt"
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

				prev: {
					label: sell_media.cart_labels.prev,
					disabled: true
				},

				next: {
					label: sell_media.cart_labels.next,
					disabled: false
				}
			}
		},

		mounted: function() {
			let attachment = this.attachments[this.currentSlide]
			this.$emit('attachment', attachment)
		},

		methods: {

			goNext: function() {
				this.currentSlide += 1
				let attachment = this.attachments[this.currentSlide]
				this.$emit('attachment', attachment)

				if (this.currentSlide < this.attachments.length - 1){
					this.prev.disabled = false
				} else {
					this.next.disabled = true
				}
			},
			goPrev: function() {
				this.currentSlide -= 1
				let attachment = this.attachments[this.currentSlide]
				this.$emit('attachment', attachment)

				if (this.currentSlide > 0){
					this.next.disabled = false
				} else {
					this.prev.disabled = true
				}
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