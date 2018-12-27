Vue.component('layout-drawer', {
    template: `
    <div class="layout-drawer">
        <div class="layout-drawer-header" v-if="$slots.header">
            <slot name="header"></slot>
        </div>
        <div class="layout-drawer-body">
            <slot></slot>
        </div>
        <div class="layout-drawer-footer" v-if="$slots.footer">
            <slot name="footer"></slot>
        </div>
    </div>
    `
});
