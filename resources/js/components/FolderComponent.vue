<template>
    <div id="folder" class="min-h-screen flex">
        <vue-dropzone :include-styling="false" ref="dropzone" id="dropzone" :options="dropzoneOptions" v-on:vdropzone-file-added="onAdd" v-on:vdropzone-sending="onSend"></vue-dropzone>
        <context-menu v-on:trash-file="trashFile" v-on:create-new-folder="createFolder" :id="selectedFile" :hidden="contextMenuHidden"></context-menu>
    </div>
</template>
<script>
import vue2Dropzone from 'vue2-dropzone'

export default {
    components: {
        vueDropzone: vue2Dropzone
    },
    data: function () {
        return {
            folder_id: window.location.pathname.slice(1),
            dropzoneOptions: {
                url: '/upload',
                previewTemplate: this.template(),
                headers: {
                    "X-CSRF-TOKEN": document.head.querySelector("[name=csrf-token]").content
                }
            },
            contextMenuHidden: true,
            selectedFile: "",
            dzitems: [],
        }
    },
    mounted() {
        this.loadFolder();
    },
    methods: {
        handleDblclick(e, item) {
            console.log(e);
            if (item.type === "folder" || item.type === "upOne") {
                window.location.pathname = `/${item.id}`;
            } else {
                this.$emit('preview-file', item.id);
            }
        },

        onAdd(file) {
            this.dzitems.push(file);

            const updateElement = (elName, data) => {
                file.previewElement.querySelector(`[data-${elName}]`).innerHTML = data;
            };

            const date = (file.date) ? new Date(file.date) : new Date();
            updateElement("dz-date", date.toDateString());
            updateElement("id", file.id);

            file.previewElement.addEventListener("dblclick", (e) => {
                e.preventDefault();
                this.handleDblclick(e, file);
            });

            if (file.type === "upOne") return;

            file.previewElement.addEventListener("contextmenu", (e) => {
                e.preventDefault();
                this.openContextMenu(e, file);
            });
        },
        template: function () {
            return `<div class="dz-preview dz-file-preview">
                <span class="hidden" data-id=""></span>
                <div class="dz-image">
                    <div data-dz-thumbnail-bg></div>
                </div>
                <div class="dz-details flex">
                    <div class="dz-filename flex-1"><span data-dz-name></span></div>
                    <div class="dz-size flex-none px-5"><span data-dz-size></span></div>
                    <div class="dz-date flex-none"><span data-dz-date></span></div>
                </div>
                <div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>
                <div class="dz-error-message"><span data-dz-errormessage></span></div>
                <div class="dz-success-mark"><i class="fa fa-check"></i></div>
                <div class="dz-error-mark"><i class="fa fa-close"></i></div>
            </div>
        `;
        },

        openContextMenu(e, file) {
            this.contextMenuHidden = false;
            this.selectedFile = file.id;

            const menu = document.getElementById("context-menu");
            menu.style.left = `${e.clientX}px`;
            menu.style.top = `${e.clientY}px`;
        },
        onSend(file, xhr, formData) {
            formData.append('folder_id', this.folder_id);
        },
        //onSuccess(file, response) {},

        async loadFolder() {
            const response = await fetch(`/api/${this.folder_id}`).then(value => value.json());

            if (response.parent_id !== "") {
                this.$refs.dropzone.manuallyAddFile({
                    id: response.parent_id,
                    size: 0,
                    name: "Up",
                    date: "",
                    type: "upOne",
                }, ``);
            }

            for (let item of response.folders) {
                this.$refs.dropzone.manuallyAddFile({
                    id: item.id,
                    size: 0,
                    name: item.name,
                    date: item.created_at,
                    type: "folder",
                }, ``);
            }
            for (let item of response.files) {
                this.$refs.dropzone.manuallyAddFile({
                    id: item.id,
                    size: item.bytes,
                    name: item.real_name,
                    date: item.created_at,
                    type: "file",
                }, `/api/preview/${item.id}`);
            }
        },

        async createFolder(name = "none") {
            const request = await axios.post(
                `/folders/create`, {
                    name: name,
                    parent_id: this.folder_id
                });
            if (request.status === 200) {
                this.$refs.dropzone.manuallyAddFile({
                    id: item.id,
                    size: 0,
                    name: name,
                    date: item.created_at,
                    type: "folder",
                }, ``);
                this.contextMenuHidden = true;
            }
        },
        // TODO: error handling
        async trashFile(fileId) {
            const request = await axios.delete(`/files/trash/${fileId}`);
            if (request.status === 200) {
                this.$refs.dropzone.removeFile(this.dzitems.find((file) => file.id === fileId));
                this.contextMenuHidden = true;
            }

        }
    }
}
</script>
