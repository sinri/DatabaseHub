Vue.component('layout-list', {
    template: `
    <layout class="layout-list">
        <div class="layout-search-bar">
            <slot name="search"></slot>
        </div>
        <div class="layout-handle-bar">
            <slot name="handle"></slot>
        </div>
        
        <divider dashed></divider>
        
        <div class="layout-table">
            <slot></slot>
        </div>
        <div class="layout-pagination">
            <slot name="pagination"></slot>
        </div>
    </layout>
    `
});
