Vue.component('query-notepads-drawer', {
    template: `
    <div class="c-query-notepads-drawer">
        <i-select placeholder="Select one to insert" style="width: 260px;" clearable filterable
            @on-change="handleInsert">
            <i-option v-for="notepad in allQueryNotepads"
                :key="notepad.id"
                :value="notepad.id">{{ notepad.title }}</i-option>
        </i-select>
        <i-button type="success" @click="showDrawer">Query Notepads</i-button>
        <Drawer :closable="false" :width="600" v-model="drawerVisible">
            <div class="c-query-notepads-drawer-header" slot="header">
                <ButtonGroup>
                    <i-button :type="currentPane === 'list' ? 'primary' : 'default'" @click="setPane('list')">Notepads</i-button>
                    <i-button :type="currentPane === 'create' ? 'primary' : 'default'" @click="setPane('create')">Create Notepads</i-button>
                    <i-button :type="currentPane === 'edit' ? 'primary' : 'default'" @click="setPane('edit')">Edit NotePads</i-button>
                </ButtonGroup>
            </div>
            <div class="c-query-notepads-drawer-content">
                <div class="c-query-notepads-drawer-content-body">
                    <template v-if="currentPane === 'list'">
                        <div class="notepads-search">
                            <i-input class="search-input" placeholder="Filter by title" clearable
                                prefix="ios-search"
                                v-model="filterTitle" />
                        </div>
                        <ul class="notepad-list">
                            <li v-for="(notepad, index) in lowerCasedAllQueryNotepads"
                                :title="notepad.title" 
                                :key="notepad.id"
                                v-if="notepad.lowerCasedTitle.includes(lowerCasedFilterTitle)">
                                <p class="notepad-list-item-title">{{ notepad.title }}</p>
                                <i-button size="small" type="success" shape="circle" @click="handleInsert(notepad.id)">Insert</i-button>
                                <i-button size="small" type="primary" shape="circle" @click="handleEdit(notepad.id)">Edit</i-button>
                                <i-button size="small" type="error" shape="circle" @click="deleteUserQueryNotepad(notepad.id, index)">Delete</i-button>
                            </li>
                        </ul>
                        <p style="text-align: center;" 
                           v-if="lowerCasedAllQueryNotepads.length === 0">暂无数据</p>
                    </template>
                    <template v-if="currentPane === 'create' || currentPane === 'edit'">
                        <i-form ref="form" label-position="top"
                            :model="form.model" 
                            :rules="form.rules">
                            <form-item label="Notepad" prop="id" v-show="currentPane === 'edit'">
                                <i-select placeholder="Database" filterable
                                           @on-change="setFormContent"
                                           v-model.trim="form.model.id">
                                     <i-option v-for="notepad in allQueryNotepads"
                                               :key="notepad.id"
                                               :value="notepad.id">{{ notepad.title }}</i-option>
                                 </i-select>
                             </form-item>
                             
                            <form-item label="Title" prop="title">
                                <i-input clearable v-model.trim="form.model.title" />
                            </form-item>
                            
                            <form-item label="SQL" prop="content">
                                <codemirror style="font-size: 14px;"
                                    :options="codeMirrorOptions"
                                    v-model.trim="form.model.content"></codemirror>
                            </form-item>
                        </i-form>
                    </template>
                </div>
                <div class="c-query-notepads-drawer-content-footer"
                    v-if="currentPane !== 'list'">
                    <i-button type="error"
                        :disabled="form.loading || !form.model.id"
                        @click="handleDelete(form.model.id)"
                        v-if="currentPane === 'edit'">Delete</i-button>
                    <i-button
                        :disabled="form.loading" 
                        @click="onResetForm">Reset Form</i-button>
                    <i-button type="primary"
                        :disabled="form.loading"
                        @click="onSubmitForm">Submit Form</i-button>
                </div>
            </div>
        </Drawer>
    </div>
    `,
    data () {
        return {
            allQueryNotepads: [],
            drawerVisible: false,
            form: {
                model: {
                    id: 0,
                    title: '',
                    content: ''
                },
                rules: {
                    id: [
                        {
                            required: true,
                            validator: (rule, val, callback) => {
                                if (this.currentPane === 'edit' && !val) {
                                    callback(new Error('不能为空'))
                                } else {
                                    callback()
                                }
                            },
                            message: '不能为空'}
                    ],
                    title: [
                        {required: true, message: '不能为空'}
                    ],
                    content: [
                        {required: true, message: '不能为空'}
                    ]
                },
                loading: false
            },
            codeMirrorOptions: {
                tabSize: 4,
                styleActiveLine: true,
                lineNumbers: true,
                line: true,
                mode: 'text/x-mysql',
                theme: 'panda-syntax'
            },
            currentPane: 'list',
            filterTitle: ''
        }
    },
    computed: {
        lowerCasedFilterTitle () {
            return this.filterTitle.toLowerCase()
        },
        lowerCasedAllQueryNotepads () {
            return this.allQueryNotepads.map(item => {
                item.lowerCasedTitle = item.title.toLowerCase()

                return item
            })
        }
    },
    methods: {
        showDrawer () {
            this.drawerVisible = true
        },
        setPane (pane) {
            // from create to edit
            if (this.currentPane === 'create' && pane === 'edit') {
                this.onResetForm()
            }
            // from edit to create
            if (this.currentPane === 'edit' && pane === 'create') {
                this.onResetForm()
            }

            this.currentPane = pane
        },

        setFormContent (id) {
            if (!id) return

            this.getQueryNotepadDetail(id).then(({notepad_detail}) => {
                this.form.model.title = notepad_detail.title
                this.form.model.content = notepad_detail.content
            }).catch(({message}) => {
                SinriQF.iview.showErrorMessage(message, 5);
            });
        },

        handleInsert (id) {
            if (!id) return

            this.getQueryNotepadDetail(id).then(({notepad_detail}) => {
                this.$emit('on-insert', notepad_detail.content)
            }).catch(({message}) => {
                SinriQF.iview.showErrorMessage(message, 5);
            });
        },
        handleEdit (id) {
            this.setPane('edit')
            this.form.model.id = id
            this.setFormContent(id)
        },
        handleDelete (id) {
            const index = this.allQueryNotepads.findIndex(item => item.id === id)

            if (index === -1) return

            this.deleteUserQueryNotepad(id, index)
            this.onResetForm()
        },

        onResetForm () {
            this.$refs.form.resetFields()

            // because v-if
            this.form.model = {
                id: 0,
                title: '',
                content: ''
            }
        },
        onSubmitForm () {
            this.$refs.form.validate((valid) => {
                if (valid) {
                    this.save();
                }
            });
        },
        afterSubmit () {
            this.onResetForm()
            this.form.loading = false
            this.getCurrentUserAllQueryNotepads()
            this.setPane('list')
        },
        save () {
            const formData = JSON.parse(JSON.stringify(this.form.model))

            if (this.currentPane === 'create') {
                this.createUserQueryNotepad(formData)
            } else {
                this.editUserQueryNotepad(formData)
            }

            this.form.loading = true
        },

        getCurrentUserAllQueryNotepads () {
            ajax('getCurrentUserAllQueryNotepads').then(({query_notepad_list = []}) => {
                this.allQueryNotepads = query_notepad_list;
            }).catch(({message}) => {
                SinriQF.iview.showErrorMessage(message, 5);
            });
        },
        getQueryNotepadDetail (id) {
            return ajax('getQueryNotepadDetail', {id})
        },
        createUserQueryNotepad (formData) {
            ajax('createUserQueryNotepad', formData).then(() => {
                this.afterSubmit()
                SinriQF.iview.showSuccessMessage('Create success!', 2);
            }).catch(({message}) => {
                SinriQF.iview.showErrorMessage(message, 5);
            });
        },
        editUserQueryNotepad (formData) {
            ajax('editUserQueryNotepad', formData).then(() => {
                this.afterSubmit()
                SinriQF.iview.showSuccessMessage('Update success!', 2);
            }).catch(({message}) => {
                SinriQF.iview.showErrorMessage(message, 5);
            });
        },
        deleteUserQueryNotepad (id, index) {
            this.allQueryNotepads.splice(index, 1);

            ajax('deleteUserQueryNotepad', { id }).then(() => {
                SinriQF.iview.showSuccessMessage('Delete success!', 2);
            }).catch(({message}) => {
                SinriQF.iview.showErrorMessage(message, 5);
            });
        }
    },
    mounted () {
        this.getCurrentUserAllQueryNotepads()
    }
});
