Vue.component('avatar', {
    template: `
    <div class="c-avatar" :title="fullName">
        <div class="c-avatar-text" :style="styles">{{ avatarText }}</div>
        <div class="c-avatar-username" v-if="showUsername"><slot>{{ fullName }}</slot></div>
    </div>
    `,
    props: {
        size: {
            type: [Number, String],
            default: 38
        },
        realName: {
            type: String,
            default: ''
        },
        username: {
            type: String,
            default: ''
        },
        showUsername: {
            type: Boolean,
            default: false
        }
    },
    computed: {
        styles () {
            return {
                width: this.size + 'px',
                height: this.size + 'px',
                lineHeight: this.size + 'px'
            }
        },
        fullName () {
            return `${this.realName}(${this.username})`
        },
        avatarText () {
            return this.realName.slice(this.realName.length - 2)
        }
    }
});
