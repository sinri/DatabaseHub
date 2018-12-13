let TopBannerComponent = {
    template: `
        <i-menu mode="horizontal" theme="dark"
            :active-name="opened"
            @on-select="onMenuItemSelected">
            <template v-for="item in menuItems">
                <menu-item
                    :name="item.name"
                    :style="item.style + (item.name===opened ? 'background: rgb(91, 100, 120);' : '')">
                    <Icon :type="item.icon" /> {{item.text}}
                </menu-item>
            </template>
            <menu-item name="userInfo" style="width: 20%;text-align: right;">
                <Icon type="ios-people" />{{currentUser.realname}}
            </menu-item>
            <menu-item name="logout" style="width: 10%;text-align: right;">
                <Icon type="ios-exit-outline" /> Logout
            </menu-item>
        </i-menu>
    `,
    props: ['opened'],
    data: function () {
        return {
            currentUser: JSON.parse(SinriQF.cookies.getCookie('DatabaseHubUser')),
            menuItems: [
                {
                    name: 'indexPage',
                    style: 'width: 30%;text-align: left;',
                    icon: 'ios-nuclear',
                    text: 'DatabaseHub'
                },
                {
                    name: 'requestPage',
                    style: 'width: 10%;text-align: center;',
                    icon: 'ios-nuclear',
                    text: 'Requests'
                },
                {
                    name: 'taskPage',
                    style: 'width: 10%;text-align: center;',
                    icon: 'ios-nuclear',
                    text: 'Tasks'
                },
                {
                    name: 'configPage',
                    style: 'width: 10%;text-align: center;',
                    icon: 'ios-nuclear',
                    text: 'Configure'
                },
                {
                    name: 'queryPage',
                    style: 'width: 10%;text-align: center;',
                    icon: 'ios-nuclear',
                    text: 'Quick Query'
                }
            ]
        };
    },
    methods: {
        ensureLogin () {
            if (!SinriQF.cookies.getCookie(SinriQF.config.TokenName)) {
                window.location.href = 'login.html';
            }
        },
        onMenuItemSelected (name) {
            console.log('onMenuItemSelected', name);
            switch (name) {
                case 'indexPage':
                    window.location.href = 'index.html';
                    break;
                case 'requestPage':
                    window.location.href = 'index.html';
                    break;
                case 'taskPage':
                    window.location.href = 'index.html';
                    break;
                case 'configPage':
                    window.location.href = 'configure.html';
                    break;
                case 'queryPage':
                    window.location.href = 'index.html';
                    break;
                case 'userInfo':
                    break;
                case 'logout':
                    SinriQF.cookies.cleanCookie('DatabaseHubUser');
                    SinriQF.cookies.cleanCookie(SinriQF.config.TokenName);
                    window.location.href = 'login.html';
                    break;
            }
        }
    },
    mounted () {
        SinriQF.config.TokenName = 'database_hub_token';
        SinriQF.config.vueInstance = this;

        this.ensureLogin();
    }
};
