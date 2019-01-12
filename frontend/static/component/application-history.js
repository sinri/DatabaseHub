Vue.component('application-history', {
    template: `
        <div>
            <!--<div>{{ history }}</div>-->
            <Timeline>
                <Timeline-Item v-for="item in history">
                    <Icon type="ios-color-wand" slot="dot" v-if="item.action==='APPLY'"></Icon>
                    <Icon type="ios-backspace" slot="dot" v-if="item.action==='CANCEL'"></Icon>
                    <Icon type="ios-checkmark" slot="dot" v-if="item.action==='APPROVE'"></Icon>
                    <Icon type="ios-close" slot="dot" v-if="item.action==='DENY'"></Icon>
                    <Icon type="ios-plane" slot="dot" v-if="item.action==='EXECUTE'"></Icon>
                    <Icon type="md-create" slot="dot" v-if="item.action==='UPDATE'"></Icon>
                    <Icon type="ios-git-branch" slot="dot" v-if="item.action==='FORK'"></Icon>
                    <p style="font-weight: bold;">{{ item.actTime }} {{ item.actUser }} made action <code>{{ item.action }}</code>, status became <code>{{ item.status }}</code>  </p>
                    <p v-if="item.detail.trim()!==''">{{ item.detail }}</p>
                </Timeline-Item>
            </Timeline>    
        </div>
    `,
    data: function () {
        return {}
    },
    props: ['history']
});