Vue.component('layout-list', {
    template: `
    <layout class="layout-list">
        <div class="layout-list-header" v-if="$slots.header">
            <slot name="header"></slot>
        </div>
        <div class="layout-list-search-bar" v-if="$slots.search">
            <slot name="search"></slot>
        </div>
        <div class="layout-list-handle-bar" v-if="$slots.handle">
            <slot name="handle"></slot>
        </div>
        
        <divider dashed v-if="$slots.header || $slots.search || $slots.handle"></divider>
        
        <div class="layout-list-table">
            <slot></slot>
        </div>
        <div class="layout-list-pagination" v-if="$slots.pagination">
            <slot name="pagination"></slot>
        </div>
    </layout>
    `
});
