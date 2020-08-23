<template>
    <div class="context-wrapper">
        <div id="context-menu" :data-selected-id="id" v-bind:class="{ hidden: hidden }" class="absolute z-10 bg-white">
            <div v-on:click="previewFile" class="preview-wrapper">
                <span class="preview-icon">
                </span>
                <div>Preview</div>
            </div>
            <div v-on:click="renameFile" class="rename-wrapper">
                <span class="rename-icon">
                </span>
                <div>Rename</div>
            </div>
            <div v-on:click="downloadFile" class="download-wrapper">
                <span class="download-icon">
                </span>
                <div>Download</div>
            </div>
            <div v-on:click="$emit('trash-file', id)" class="trash-wrapper">
                <span class="trash-icon">
                </span>
                <div>Move to trash</div>
            </div>
            <div class="separator"></div>
            <div v-on:click="$emit('show-properties', id)" class="properties-wrapper">
                <span class="properties-icon">
                </span>
                <div>Properties</div>
            </div>
            <div class="separator"></div>
            <div v-on:click="$emit('create-new-folder')" class="new-folder-wrapper">
                <span class="folder-icon">
                </span>
                <div>New Folder</div>
            </div>
        </div>
        <div id="context-overlay">

        </div>
    </div>
</template>

<script>

export default {
    props: ["hidden", "id"],
    data: function () {
        return {

        }
    },
    mounted() {
        //console.log('Component mounted.')
    },
    methods: {
        downloadFile() {
            window.location.pathname = `/download/${this.id}`;
            this.hidden = true;
        },
        previewFile() {

            this.hidden = true;
        },
        renameFile() {

            this.hidden = true;
        }
    }
}
</script>
