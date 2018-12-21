Vue.component('status', {
    template: `
    <i class="c-status" :class="status"></i>
    `,
    props: {
        status: {
            type: String,
            required: true
        }
    }
});
