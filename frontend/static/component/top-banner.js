Vue.component('top-banner', {
    template: `
        <i-menu mode="horizontal" theme="dark"
            :active-name="activeMenuName"
            @on-select="onMenuItemSelected">
            <template v-for="item in menuItems">
                <template v-if="item.children">
                    <submenu v-if="!item.admin || isAdmin || isKiller"
                        :key="item.name"
                        :name="item.name">
                        <template slot="title">
                            <Icon :type="item.icon" v-if="item.icon" /> {{ item.text }}
                        </template>
                        <menu-item v-for="subItem in item.children"
                            :key="subItem.name"
                            :name="subItem.name"
                            :style="subItem.style"
                            v-if="!subItem.admin || isAdmin">
                            <Icon :type="subItem.icon" v-if="subItem.icon" /> {{ subItem.text }}
                        </menu-item>
                    </submenu>
                </template>
                <template v-else-if="item.type==='daemon-status'">
                    <menu-item 
                        :key="item.name"
                        :name="item.name"
                        :style="item.style"
                    >
                        <!--<Tooltip :content="queue_status_tooltip" max-width="400">-->
                            Daemon:
                            <Icon type="ios-warning" v-if="queue_status==='inactive'"></Icon>
                            <Icon type="ios-done-all" v-if="queue_status==='active' && queue_worker_count==0"></Icon>
                            <Icon type="ios-eye" v-if="queue_status==='active' && queue_worker_count>0"></Icon>
                            <Icon type="ios-loading" v-if="queue_status==='unknown'"></Icon>
                            <span style="width: 60px;display: inline-block;">{{ queue_status }}</span>
                            <span style="width: 80px;display: inline-block;">{{queue_worker_count}} workers</span>
                        <!--</Tooltip>-->
                    </menu-item>
                </template>
                <template v-else>
                    <menu-item v-if="!item.admin || isAdmin"
                        :key="item.name"
                        :name="item.name"
                        :to="item.to"
                        :style="item.style">
                        <Icon :type="item.icon" v-if="item.icon" /> {{ item.text }}
                    </menu-item>
                </template>
            </template>
        </i-menu>
    `,
    data() {
        return {
            activeMenuName: 'DatabaseHub',
            menuItems: [
                {
                    name: 'dashboardPage',
                    style: 'width: 15%;text-align: left;',
                    icon: 'ios-nuclear',
                    text: 'DatabaseHub'
                },
                {
                    name: 'applicationListPage',
                    icon: 'ios-beaker',
                    text: 'Applications'
                },
                {
                    name: 'createApplicationPage',
                    icon: 'ios-create',
                    text: 'Apply'
                },
                {
                    name: 'approvalListPage',
                    icon: 'md-nutrition',
                    text: 'Approvals'
                },
                {
                    name: 'managementPage',
                    icon: 'ios-flask',
                    text: 'Management',
                    admin: true,
                    children: [
                        {
                            name: 'databaseListPage',
                            icon: 'ios-cube',
                            text: 'Databases',
                            admin: true,
                        },
                        {
                            name: 'permissionsPage',
                            icon: 'ios-people',
                            text: 'Permissions',
                            admin: true,
                        },
                        {
                            name: 'permissionAuditPage',
                            icon: 'ios-book',
                            text: 'Permission Audit',
                            admin: true,
                        },
                        {
                            name: 'processesPage',
                            icon: 'ios-backspace',
                            text: 'Processes'
                        },
                        {
                            name: 'workersPage',
                            icon: 'ios-pulse',
                            text: 'Workers',
                            admin: true,
                        }
                    ]
                },
                {
                    name: 'quickQueryPage',
                    icon: 'md-paper',
                    text: 'Quick Query'
                },
                {
                    name: 'logout',
                    style: 'float: right;',
                    icon: 'ios-exit-outline',
                    text: 'Logout'
                },
                {
                    name: 'userInfo',
                    style: 'float: right;',
                    icon: 'md-person',
                    text: JSON.parse(SinriQF.cookies.getCookie('DatabaseHubUser')).realname
                },
                {
                    type: 'daemon-status',
                    name: 'daemon-status',
                    style: 'float: right;',
                },
            ],
            queue_status: "unknown",
            queue_worker_count: '?',
            queue_status_tooltip: '',
            queue_status_refresh_time: null
        };
    },
    computed: {
        isAdmin() {
            return JSON.parse(SinriQF.cookies.getCookie('DatabaseHubUser')).userType === 'ADMIN'
        },
        isKiller() {
            return !!(JSON.parse(SinriQF.cookies.getCookie('DatabaseHubUser')).asKiller)
        }
    },
    methods: {
        onMenuItemSelected(name) {
            this.activeMenuName = name;

            switch (name) {
                case 'userInfo':
                    break;
                case 'logout':
                    window.location.href = API.logout.url;

                    break;
                case 'quickQueryPage':
                    const {href} = router.resolve({name});

                    window.open(href, '_blank');

                    break;
                case 'daemon-status':
                    break;
                default:
                    router.push({name});
            }
        },
        updateActiveMenuName(to) {
            this.activeMenuName = to.name
        },
        refreshQueueDaemonStatus: function () {
            this.queue_status_tooltip = "Loading";
            ajax("checkWorkerStatus", {type: 'status'}).then(({status, worker_count, output}) => {
                console.log("output", output);
                this.queue_status = status;
                this.queue_worker_count = worker_count;
                this.queue_status_refresh_time = (new Date());
                this.queue_status_tooltip = "Updated: " + this.queue_status_refresh_time;
                if (output && output.length > 0) {
                    this.queue_status_tooltip += "\n" + output.join("\n")
                }
            }).catch(({message}) => {
                //SinriQF.iview.showErrorMessage(message, 5);
                this.queue_status = "unknown";
                this.queue_worker_count = '?';
                this.queue_status_tooltip = "Load Error: " + message;
            });
        }
    },
    watch: {
        $route: {
            handler: 'updateActiveMenuName',
            immediate: true
        }
    },
    mounted: function () {
        this.refreshQueueDaemonStatus();
        setInterval(() => {
            this.refreshQueueDaemonStatus();
        }, 10000);
    }
});
