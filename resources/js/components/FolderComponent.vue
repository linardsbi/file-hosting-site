<template>
    <div v-on:keyup.alt="selectAll" id="folder" class="min-h-screen">
        <button id="go-up" class="mb-4 flex bg-transparent hover:bg-blue-500 text-blue-700 font-semibold hover:text-white py-2 px-4 border border-blue-500 hover:border-transparent rounded" :class="{ hidden: !parent_id }" :data-parent-id="parent_id" v-on:dblclick="goUp">
            <svg class="up-icon mr-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
            </svg>
            Go up
        </button>
        <vue-dropzone class="w-full flex flex-wrap" :include-styling="false" ref="dropzone" id="dropzone" :options="dropzoneOptions" v-on:vdropzone-thumbnail="thumbnail" v-on:vdropzone-sending="onSend" v-on:vdropzone-success="onUploadSuccess" v-on:vdropzone-file-added-manually="handleFileAdd"></vue-dropzone>
        <context-menu ref="contextMenu" v-on:open-modal="openModal" v-on:trash-file="trashFile" v-on:create-new-folder="createFolder" :ids="selectedItems"></context-menu>
        <modal ref="modal"></modal>
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
                thumbnailWidth: 200,
                headers: {
                    "X-CSRF-TOKEN": document.head.querySelector("[name=csrf-token]").content
                }
            },
            contextMenuHidden: true,
            selectedItems: "",
            dzitems: [],
            parent_id: "",
        }
    },
    mounted() {
        this.loadFolder();
    },
    methods: {
        openModal: function (data) {
            console.log(data);
            this.$refs.modal.close();
        },
        selectAll: function (e) {
            console.log(e)
        },
        goUp: function (e) {
            window.location.pathname = `/${e.target.getAttribute("data-parent-id")}`;
        },
        // this function should be replaced with a function that gets the thumbnail
        // when the 'success' event is fired
        thumbnail: function(file, dataUrl) {
            var j, len, ref, thumbnailElement;
            if (file.previewElement) {
                file.previewElement.classList.remove("dz-file-preview");
                ref = file.previewElement.querySelectorAll("[data-dz-thumbnail-bg]");
                for (j = 0, len = ref.length; j < len; j++) {
                    thumbnailElement = ref[j];
                    thumbnailElement.alt = file.name;
                    thumbnailElement.style.backgroundImage = `url('thumbnails/${file.id}-sm.png')`;
                }
                return setTimeout(((function (_this) {
                    return function () {
                        return file.previewElement.classList.add("dz-image-preview");
                    };
                })(this)), 1);
            }
        },
        handleDblclick(e, item) {
            if (item.itemType === "folder") {
                window.location.pathname = `/${item.id}`;
            } else {
                this.$emit('preview-file', item.id);
            }
        },

        handleFileAdd(file) {
            this.dzitems.push(file);
            file.previewElement.classList.add(file.itemType);

            const updateElement = (elName, data) => {
                file.previewElement.querySelector(`[data-${elName}]`).innerHTML = data;
            };

            const date = (file.date) ? new Date(file.date) : new Date();
            const expiration = (file.expiration) ? new Date(file.expiration) : new Date();
            updateElement("dz-date", date.toDateString());
            updateElement("dz-expiration", expiration.toDateString());
            updateElement("id", file.id);

            file.previewElement.addEventListener("dblclick", (e) => {
                e.preventDefault();
                this.handleDblclick(e, file);
            });

            file.previewElement.addEventListener("click", (e) => {
                this.contextMenuHidden = true;
                e.preventDefault();
                if (!e.ctrlKey) {
                    for (let item of document.getElementById("dropzone").querySelectorAll(".highlighted")) {
                        item.classList.remove("highlighted");
                    }
                }

                file.previewElement.classList.toggle("highlighted");
            });

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
            return `
            <div class="cursor-pointer mb-2 mr-2 lg:flex">
                <span class="hidden" data-id=""></span>
                <div class="border-l border-b border-gray-400 lg:border-t lg:border-gray-400 rounded-b lg:rounded-b-none lg:rounded-r flex lg:h-auto lg:w-24 flex-none bg-cover rounded-t lg:rounded-t-none lg:rounded-l text-center overflow-hidden"
                    data-dz-thumbnail-bg>
                    <svg class="flex-1 file-icon relative" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <svg class="flex-1 folder-icon relative" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z" />
                    </svg></div>

                <div
                    class="border-r border-b border-l border-gray-400 lg:border-l-0 lg:border-t lg:border-gray-400 bg-white rounded-b lg:rounded-b-none lg:rounded-r p-4 flex flex-col justify-between leading-normal">
                    <div class="mb-1">
                        <p class="text-sm text-gray-600 flex items-center">
                            <svg class="text-gray-500 w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Expires at <span data-dz-expiration class="ml-1"></span>
                        </p>
                        <div class="text-gray-900 font-bold text-xl mb-2" data-dz-name></div>

                    </div>
                    <div class="flex items-center">
                        <div class="text-sm">
                            <p class="text-gray-900 leading-none" data-dz-owner></p>
                            <p class="text-gray-600" data-dz-date></p>
                            <p class="text-gray-600" data-dz-size></p>
                        </div>
                    </div>
                </div>
            </div>`;
        },

        openContextMenu(e, file) {
            const fileElement = $(e.path).closest(".file");
            if (fileElement.length === 0) {
                this.contextMenuHidden = true;
                return;
            } else {
                fileElement.addClass("highlighted");
            }

            this.$refs.contextMenu.open();
            this.selectedItems = "";
            for (let item of document.getElementById("dropzone").querySelectorAll(".highlighted > [data-id]")) {
                // not cool!
                this.selectedItems += `${item.innerHTML},`;
            }

            Vue.nextTick(function () {
                const menu = document.getElementById("context-menu");
                menu.style.left = `${e.clientX}px`;
                menu.style.top = `${e.clientY}px`;
            });

        },
        onSend(file, xhr, formData) {
            formData.append('folder_id', this.folder_id);
        },

        async loadFolder() {
            const response = await fetch(`/folder/${this.folder_id}`).then(value => value.json());

            this.parent_id = response.parent_id;

            for (let item of response.folders) {
                this.$refs.dropzone.manuallyAddFile({
                    id: item.id,
                    size: 0,
                    name: item.name,
                    date: item.created_at,
                    itemType: "folder",
                }, "");
            }
            for (let item of response.files) {
                this.$refs.dropzone.manuallyAddFile({
                    id: item.id,
                    size: item.bytes,
                    name: item.real_name,
                    date: item.created_at,
                    itemType: "file",
                    expiration: item.expires_at,
                }, `thumbnails/${item.id}-sm.png`);
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
            let files = "";
            let folders = "";

            for (let item of fileIds.split(",")) {
                if (!item) continue;
                const thisItem = this.dzitems.find((file) => file.id === item);
                if (thisItem.type === "folder") {
                    folders += `${item},`;
                } else {
                    files += `${item},`;
                }

                // BAD!
                this.$refs.dropzone.removeFile(thisItem);
            }

            let request = null;

            if (files) {
                request = await axios.delete(`/file/trash/${files.slice(0,-1)}`);
            }
            if (folders) {
                request = await axios.delete(`/folder/trash/${folders.slice(0,-1)}`);
            }
            if (request.status === 200) {
                this.contextMenuHidden = true;
            }
        }
    }
}
</script>
