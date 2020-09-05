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
            <div v-on:click="showProperties" class="properties-wrapper">
                <span class="properties-icon">
                </span>
                <div>Properties</div>
            </div>
            <div v-on:click="showPermissions" class="permissions-wrapper">
                <span class="permissions-icon">
                </span>
                <div>Permissions</div>
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
    props: ["ids"],
    data: function () {
        return {
            isHidden: true,
            target: null,
        }
    },
    mounted() {

    },
    computed: {
        targetId: function () {
            return this.target.querySelector("[data-id]").innerText;
        },
        targetType: function () {
            return this.target.getAttribute("data-type");
        },
        selectedFilename: function () {
            return this.target.querySelector("[data-dz-name]").innerText;
        },
        filenameWithoutExtension: function () {
            return this.selectedFilename.split(".").slice(0,-1).join();
        },
        previewModalData: function () {
            return {
                title: "Preview file",
                formAttr: {},
                content: `

                `,
                footer: {buttons: []}
            };
        },
        renameModalData: function () {
            return {
                title: "Rename item",
                formAttr: {},
                content: `
                    <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2"
                    for="rename-file">
                        New name
                    </label>
                    <input
                        required class="appearance-none block w-full bg-gray-200 text-gray-700 border border-red-500 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white"
                        value="${this.filenameWithoutExtension}" name="newName" id="rename-file" type="text">
                    <p class="text-red-500 text-xs italic">Please fill out this field.</p>
                `,
                footer: {
                    buttons: [],
                }
            };
        },
        propertiesContent: async function () {
            const request = await axios.get(`/${this.targetType}/properties/${this.targetId}`);
            let content = "";
            if (request.status === 200) {
                for (let key in request.data) {
                    content += `<div class="item-${key}"><span class="mr-1">${request.data[key].text}</span><span>${request.data[key].value}</span></div>`;
                }
            }

            return content;
        },
        propertiesModalData: async function () {
            const content = await this.propertiesContent;
            return {
                title: "Properties",
                formAttr: {},
                content: content,
                footer: {
                    buttons: [],
                }
            };
        },

        // FIXME
        permissionsContent: async function () {
            const request = await axios.get(`/${this.targetType}/permissions/${this.targetId}`);
            let content = "";
            if (request.status === 200) {
                for (let groupName in request.data.items) {
                    content += `<div class="${groupName}-group">
                                <h5>${request.data.items[groupName].text}</h5>`;
                    for (let userId in request.data.items[groupName].value) {
                        content += `<div class="user flex" data-id="${userId}">
                                        <span class="mr-1">${request.data.items[groupName].value[userId].item_name}</span>`;
                        for (let permission of request.data.columns) {
                            const hasPermission = !!request.data.items[groupName].value[userId].permissions.[permission];
                            content += `<div class="item-${permission} mr-2">
                                            <span class="mr-1">${permission}: </span>
                                            <input type="checkbox" ${hasPermission ? "checked" : ""}>
                                        </div>`;
                        }
                        content += `</div>`;
                    }
                    // FIXME
                    if (groupName === "others") {
                        for (let permission of request.data.columns) {
                            const hasPermission = !!request.data.items[groupName];
                            content += `<div class="item-${permission} mr-2">
                                            <span class="mr-1">${permission}: </span>
                                            <input type="checkbox" ${hasPermission ? "checked" : ""}>
                                        </div>`;
                        }
                    }
                    content += `</div>`;
                }
            }
            return content;
        },
        permissionsModalData: async function () {
            const content = await this.permissionsContent;
            return {
                title: "Permissions",
                formAttr: {},
                content: content,
                footer: {
                    buttons: [],
                }
            };
        },
    },
    methods: {
        downloadFile() {
            window.location.pathname = `/download/${this.ids.slice(0,-1)}`;
            this.isHidden = true;
        },
        previewFile() {
            this.$emit("open-modal", this.previewModalData);
            this.isHidden = true;
        },
        renameFile() {
            this.$emit("open-modal", this.renameModalData);
            this.isHidden = true;
        },
        open(target) {
            this.target = target;
            this.isHidden = false;
        },
        async showProperties() {
            this.$emit("open-modal", await this.propertiesModalData);
            this.isHidden = true;
        },
        async showPermissions() {
            this.$emit("open-modal", await this.permissionsModalData);
            this.isHidden = true;
        }
    }
}
</script>
