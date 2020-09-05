<template>
    <div v-if="!isHidden" class="modal fade" id="modal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
        <div v-on:click="this.closeByBackgroundClick" class="modal-dialog absolute w-screen h-screen top-0 left-0 flex bg-black bg-opacity-75 overflow-hidden z-10">
            <div class="modal-content m-auto bg-white">
                <div class="flex modal-header bg-teal-100 border-t-4 border-teal-500 rounded-b text-teal-900 px-4 py-3 shadow-md">
                    <h5 class="modal-title font-bold flex-1" id="modalLabel">{{this.modal.title}}</h5>
                    <button type="button" class="close flex-none" v-on:click="close" aria-label="Close">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
</svg>
                    </button>
                </div>
                <div class="modal-body m-4">
                    <form id="modal-form" v-bind="this.modal.formAttr" v-html="this.modal.content"></form>
                </div>
                <div class="modal-footer m-4 text-right" v-if="modal.footer && modal.footer.buttons">
                    <modal-footer v-on:close-modal="this.close" :buttons="modal.footer.buttons"></modal-footer>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

export default {
    data: function () {
        return {
            isHidden: true,
            modal: {
                title: "Empty modal",
                formAttr: {
                    method: "post"
                },
                content: "test",
                footer: {
                    buttons: [
                        {
                            text: "Close",
                            type: "button",
                            classList: ["mr-2","border", "border-gray-500", "bg-gray-300", "hover:bg-gray-400", "text-gray-800", "font-bold", "py-2", "px-4", "rounded", "inline-flex", "items-center"],
                            event: this.close,
                            callback: function () {},
                        },
                        // {
                        //     text: "Submit",
                        //     type: "submit",
                        //     classList: ["bg-transparent", "hover:bg-blue-500", "text-blue-700", "font-semibold", "hover:text-white", "py-2", "px-4", "border", "border-blue-500", "hover:border-transparent", "rounded"],
                        //     event: this.onSubmit,
                        //     callback: function () {},
                        // },
                    ],
                }
            },
        }
    },
    computed: {

    },
    mounted() {
    },
    methods: {
        onSubmit: function () {
            this.isHidden = true;
            this.$emit("submitted");
        },
        closeByBackgroundClick: function (e) {
            if(e.target !== e.currentTarget) return;
            this.close();
        },
        close: function (e) {
            document.querySelector("body").style.overflow = "auto";
            this.isHidden = true;
            this.$emit("closed");
        },
        open: function (data) {
            if (data.footer && data.footer.buttons && data.footer.buttons.length === 0) {
                data.footer.buttons = this.modal.footer.buttons;
            }
            document.querySelector("body").style.overflow = "hidden";
            this.modal = {...this.modal, ...data};
            this.$emit("opened");
            this.isHidden = false;
        },
    }
}
</script>
