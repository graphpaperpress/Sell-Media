<template>
    <div :id="name" :class="name">

        <div class="search-wrapper">
            <input type="text" v-model="keyword" placeholder="Search Title…"/>
            <label>Search Title:</label>
        </div>

        <div class="columns is-multiline has-text-centered" v-if="loaded === true">
            <thumbnail v-for="post in posts" :key="post.slug" :post="post"></thumbnail>
        </div>
        <nav class="pagination">
            <button class="button" v-if="showPrev" @click.prevent="showPrevPage()">Previous</button>
            <span> {{ currentPage }} / {{ totalPages }} </span>
            <button class="button" v-if="showNext" @click.prevent="showNextPage()">Next</button>
        </nav>

        <div id="child">
            <child :message="name"></child>
        </div>

    </div>
</template>

<script>

    export default {

        mounted: function() {
            const vm = this;

            if ( vm.$route.params.page ) {
                vm.getPosts( vm.$route.params.page );
            } else {
                vm.getPosts();
            }

        },

        data: function() {
            return {
                posts: {},
                currentPage: '',
                prevPage: '',
                nextPage: '',
                showNext: true,
                showPrev: true,
                postCollection: '',
                postPerPage: sell_media.posts_per_page,
                totalPages: '',
                loaded: false,
                pageTitle: '',
                name: this.$options.name // component name
            }
        },

        methods: {
            getPosts: function( pageNumber = 1 ) {
                const vm = this;
                vm.loaded = false;
                vm.$http.get( '/wp-json/wp/v2/sell_media_item', {
                    params: {
                        per_page: vm.postPerPage,
                        page: pageNumber
                    }
                } )
                .then( ( res ) => {
                    vm.posts = res.data;
                    console.log(vm.posts);
                    vm.totalPages = res.headers[ 'x-wp-totalpages' ];

                    if ( pageNumber <= parseInt( vm.totalPages ) ) {
                        vm.currentPage = parseInt( pageNumber );
                    } else {
                        vm.$router.push( { 'name': 'search' } );
                        vm.currentPage = 1;
                    }

                    vm.loaded = true;
                    vm.pageTitle = 'Search';
                    vm.$store.commit( 'changeTitle', vm.pageTitle );

                } )
                .catch( ( res ) => {
                    console.log( res )
                } )
            },
            showNextPage: function( event ) {
                const vm = this;

                if ( vm.currentPage < vm.totalPages ) {
                    showNext: true;
                    vm.currentPage = vm.currentPage + 1;
                    vm.$router.push( { 'name': 'search', params: { 'page': vm.currentPage } } );
                }
            },
            showPrevPage: function( event ) {
                const vm = this;

                if ( vm.currentPage != 1 ) {
                    showPrev: true;
                    vm.currentPage = vm.currentPage - 1;
                    vm.$router.push( { 'name': 'search', params: { 'page': vm.currentPage } } );
                }
            },
        },

        watch: {

            '$route'( to, from ) {
                this.getPosts( this.$route.params.page );
            }

        }
    }
</script>