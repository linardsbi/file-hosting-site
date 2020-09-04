<template>
    <div class="context-wrapper">
        <div id="context-menu" :data-selected-ids="ids" v-if="!isHidden" class="absolute z-10 bg-white">
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
            <div v-on:click="$emit('trash-file', ids)" class="trash-wrapper">
                <span class="trash-icon">
                </span>
                <div>Move to trash</div>
            </div>
            <div class="separator"></div>
            <div v-on:click="$emit('show-properties', ids)" class="properties-wrapper">
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
    </div>
</template>

<script>

export default {
    props: ["hidden", "ids"],
    data: function () {
        return {
            isHidden: true,
        }
    },
    mounted() {
    },
    methods: {
        downloadFile() {
            window.location.pathname = `/download/${this.ids.slice(0,-1)}`;
            this.isHidden = true;
        },
        previewFile() {
            this.$emit("open-modal", "preview");
            this.isHidden = true;
        },
        renameFile() {
            this.$emit("open-modal", "rename");
            this.isHidden = true;
        },
        open() {
            this.isHidden = false;
        },
    }
}
</script>
