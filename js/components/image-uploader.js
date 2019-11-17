Vue.component('image-uploader', {
    mixins: ['http'],
    props: ['meta', 'id'],
    data: function(){
        return {
            loading: false,
            formData: null,
            postData: {},
        }
    },
    methods: {
        update: function(post){
            this.postData = post;
        },
        process: function(){
            file = this.$refs.fileInput.files[0];
            console.log(file);

            if(file.type.split("/")[0] !== 'image'){
                alert("File Type is not an Image");
                this.$refs.fileInput.value = null;
                return;
            }

            this.formData = new FormData();
            this.formData.append('file', file);

            var preview = document.querySelector('img');
            var file    = document.querySelector('input[type=file]').files[0];
            var reader  = new FileReader();

            reader.onloadend = function () {
                preview.src = reader.result;
            }

            if (file) {
                reader.readAsDataURL(file);
            } else {
                preview.src = "";
            }

        },
        upload: function(){
            if(this.formData){
                this.loading = true;
                this.formData.append('post', this.postData.post);
                this.formData.append('username', this.postData.username);
                this.formData.append('title', this.postData.subject);
                this.formData.append('email', this.postData.email);
                this.formData.append('parent_id', this.id);

                axios.post( 'api.php',
                  this.formData,
                  {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                  }
                ).then( (response) => {
                  console.log(response);
                  this.$emit('upload');
                  this.loading = false;
                })
            } else {
                alert("No file selected");
            }
        }
    },
    template: `
        <div>
            <form class="form-group" id="uploadForm" method='post' enctype="multipart/form-data">
                <post-form @change="update"></post-form>
                <input class="form-control" type='file' @change="process()" ref="fileInput">
                <div class="row">
                    <div class="col text-center">
                        <img src="" height="400" alt="Image preview..." id="imagePreview">
                    </div>
                    <div class="col text-left">
                        <div id="loader" v-if="loading"></div>
                    </div>
                </div>
                <div v-if="formData">
                    <vbutton class="form-control" bgcolor="green" color="white" @click="upload()">Submit</vbutton>
                </div>
            </form>
        </div>
    `
});