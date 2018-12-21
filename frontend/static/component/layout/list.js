Vue.component('layout-list', {
    template: `
    <layout class="layout-list">
        <div class="layout-header" v-if="$slots.header">
            <slot name="header"></slot>
        </div>
        <div class="layout-search-bar" v-if="$slots.search">
            <slot name="search"></slot>
        </div>
        <div class="layout-handle-bar" v-if="$slots.handle">
            <slot name="handle"></slot>
        </div>
        
        <divider dashed></divider>
        
        <div class="layout-table">
            <slot></slot>
        </div>
        <div class="layout-pagination" v-if="$slots.pagination">
            <slot name="pagination"></slot>
        </div>
    </layout>
    `
});
