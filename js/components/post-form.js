Vue.component('post-form', {
    data: function(){
        return {
            post: {
                username: null,
                email: null,
                subject: null,
                post: null
            }
        }
    },
    methods: {
        refresh: function(){
            this.$emit('change', this.post);
        }
    },
    template: `
        <form class="form-group">
            <input class="form-control" type="text" placeholder="Anonymous" v-model="post.username" @change="refresh()">
            <input class="form-control" type="text" placeholder="Email Address" v-model="post.email" @change="refresh()">
            <input class="form-control" type="text" placeholder="Subject" v-model="post.subject" @change="refresh()">
            <textarea class="form-control" placeholder="Body" v-model="post.post" @change="refresh()"></textarea>
        </form>
    `
});