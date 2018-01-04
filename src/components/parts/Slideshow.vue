<template>

	<div class="slideshow">

		<div class="slideshow-content">

			<div class="slideshow-nav">

				<icon
				class="slideshow-nav-left"
				name="angle-left"
				scale="2"
				@click="goPrev"
				:disabled="prev.disabled"></icon>

				<icon
				class="slideshow-nav-right"
				name="angle-right"
				scale="2"
				@click="goPrev"
				:disabled="prev.disabled"></icon>

			</div>

			<div
			class="slideshow-image"
			v-for="slide in [currentSlide]"
			>
				<img
					:src="attachments[Math.abs(currentSlide) % attachments.length].sizes.large[0]"
					:alt="slide.alt"
				/>
			</div>

		</div>

		<div class="slideshow-thumbnails">
			<ul>
				<li v-for="(attachment, index) in attachments" @click="showSlide(index)">
					<img :src="attachment.sizes.thumbnail[0]" :alt="attachment.alt" />
				</li>
			</ul>
		</div>

		 {{ currentSlide + 1 }} of {{ attachments.length }} 

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
			},
			showSlide: function(index) {
				this.currentSlide = index
			}
		}
	}
</script>

<style lang="scss" scoped>

	.slideshow-content {
		position: relative;
	}

	.slideshow-nav svg {
		top: calc(50% - 16px);
		position: absolute;
		display: block;
		color: #fff;

		&:hover {
			background: rgba(0,0,0,.2);
		}

		&.slideshow-nav-left {
			left: .5rem;
		}

		&.slideshow-nav-right {
			right: .5rem;
		}
	}

	.slideshow-thumbnails {
		margin: 1rem auto;
		text-align: center;

		ul {
			list-style: none;
			padding: 0;
			margin: 0;

			li {
				display: inline-block;
				margin-right: .5rem;
				width: 50px;
				height: 50px;

				&:last-child {
					margin-right: 0;
				}
			}
		}
	}

</style>