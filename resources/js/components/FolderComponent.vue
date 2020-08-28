<template>
    <div id="folder" class="min-h-screen flex">
        <vue-dropzone :include-styling="false" ref="dropzone" id="dropzone" :options="dropzoneOptions" v-on:vdropzone-sending="onSend" v-on:vdropzone-success="onUploadSuccess" v-on:vdropzone-file-added-manually="handleFileAdd"></vue-dropzone>
        <context-menu v-on:trash-file="trashFile" v-on:create-new-folder="createFolder" :ids="selectedItems" :hidden="contextMenuHidden"></context-menu>
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
            selectedItems: "",
            dzitems: [],
        }
    },
    mounted() {
        this.loadFolder();
    },
    methods: {
        handleDblclick(e, item) {
            console.log(e);
            if (item.itemType === "folder" || item.itemType === "upOne") {
                window.location.pathname = `/${item.id}`;
            } else {
                this.$emit('preview-file', item.id);
            }
        },

        handleFileAdd(file) {
            console.log(file);
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

            if (file.itemType === "upOne") {
                file.previewElement.addEventListener("click", (e) => {
                    e.preventDefault();
                    window.location.pathname = `/${file.id}`;
                });

                return;
            } else {
                file.previewElement.addEventListener("click", (e) => {
                    e.preventDefault();
                    if (!e.ctrlKey) {
                        for (let item of document.getElementById("dropzone").querySelectorAll(".highlighted")) {
                            item.classList.remove("highlighted");
                        }
                    }

                    file.previewElement.classList.toggle("highlighted");
                });
            }

            file.previewElement.addEventListener("contextmenu", (e) => {
                e.preventDefault();
                this.openContextMenu(e, file);
            });
        },

        onUploadSuccess(file, response) {
            file.id = response.id;
            file.itemType = response.type;
            this.handleFileAdd(file);
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
            for (let item of document.getElementById("dropzone").querySelectorAll(".highlighted")) {
                // not cool!
                this.selectedItems += `,${item.children[0].innerText}`;
            }

            const menu = document.getElementById("context-menu");
            menu.style.left = `${e.clientX}px`;
            menu.style.top = `${e.clientY}px`;
        },
        onSend(file, xhr, formData) {
            formData.append('folder_id', this.folder_id);
        },
        //onSuccess(file, response) {},

        async loadFolder() {
            const response = await fetch(`/folder/${this.folder_id}`).then(value => value.json());

            if (response.parent_id !== "") {
                this.$refs.dropzone.manuallyAddFile({
                    id: response.parent_id,
                    size: 0,
                    name: "Up",
                    date: "",
                    itemType: "upOne",
                }, ``);
            }

            for (let item of response.folders) {
                this.$refs.dropzone.manuallyAddFile({
                    id: item.id,
                    size: 0,
                    name: item.name,
                    date: item.created_at,
                    itemType: "folder",
                }, ``);
            }
            for (let item of response.files) {
                this.$refs.dropzone.manuallyAddFile({
                    id: item.id,
                    size: item.bytes,
                    name: item.real_name,
                    date: item.created_at,
                    itemType: "file",
                }, `/api/preview/${item.id}`);
            }
        },

        async createFolder(name = "none") {
            const request = await axios.post(
                `/folder/create`, {
                    name: name,
                    parent_id: this.folder_id
                });
            if (request.status === 200) {
                console.log(request);
                this.$refs.dropzone.manuallyAddFile({
                    id: request.data.new_id,
                    size: 0,
                    name: name,
                    date: (new Date()).toDateString(),
                    itemType: "folder",
                }, ``);
                this.contextMenuHidden = true;
            }
        },
        // TODO: error handling
        async trashFile(fileIds) {
            for (let item of fileIds.split(",")) {
                if (!item) continue;
                const thisItem = this.dzitems.find((file) => file.id === item);
                const request = await axios.delete(`/${thisItem.itemType}/trash/${item}`);

                if (request.status === 200) {
                    this.$refs.dropzone.removeFile(thisItem);
                    this.contextMenuHidden = true;
                }
            }
        }
    }
}
</script>
