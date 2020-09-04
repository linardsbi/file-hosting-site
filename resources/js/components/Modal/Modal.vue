<template>
    <div v-if="!isHidden" class="modal fade" id="modal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLabel">{{this.modalTitle}}</h5>
                    <button type="button" class="close" v-on:click="close" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="modal-form" v-bind="this.formAttr">
                        {{this.modalContent}}
                    </form>
                </div>
                <div class="modal-footer">
                    <modal-footer :buttons="modalFooter.buttons"></modal-footer>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

export default {
    data: function () {
        return {
            isHidden: false,
            modalTitle: "Empty modal",
            formAttr: {
                method: "post"
            },
            modalContent: "test",
            modalFooter: {
                buttons: [{
                        text: "Submit",
                        type: "submit",
                        classList: ["btn", "btn-primary"],
                        event: this.onSubmit,
                        callback: function () {},
                    },
                    {
                        text: "Close",
                        type: "button",
                        classList: ["btn", "btn-secondary"],
                        event: this.close,
                        callback: function () {},
                    },
                ],
            }
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
        close: function () {
            this.isHidden = true;
            this.$emit("closed");
        },
    }
}
</script>
