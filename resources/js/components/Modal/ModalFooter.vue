<script>
export default {
    props: ['buttons'],

    render: function (createElement) {
        let self = this;
        let btns = [];

        for (let button of this.buttons) {
            btns.push(createElement('button', {
                attrs: {
                    type: button.type
                },
                on: {
                    click: function () {
                        if (typeof(button.event) === "string") {
                            if (button.event === "close") {
                                self.$emit("close-modal");
                            }
                        } else if (typeof(button.event) === "function") {
                            button.event();
                        }
                    },
                },
                class: button.classList.reduce((a,b)=> (a[b]=true,a),{}),
            }, button.text));
        }

        return createElement(
            'div', {
                domProps: {
                    class: "button-wrapper"
                }
            }, btns
        );
    },
}
</script>
