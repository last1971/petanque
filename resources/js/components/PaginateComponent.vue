<template>
    <div class="d-flex d-flex-row justify-content-between">
        <div>
            <b-btn @click="insert">Добавить</b-btn>
        </div>
        <div>
        <ul class="pagination">
            <li class="page-item" v-for="page of showPages" :class="(page) === currentPage ? 'active' : ''">
                <router-link
                    class="page-link"
                    :to="new_path(page)"
                >{{ page }}</router-link>
            </li>
        </ul>
        </div>
    </div>
</template>

<script>
    export default {
        props:['objects', 'path'],

        methods:{
            new_path(page) {
                let ret = _.cloneDeep(this.path)
                ret.query.page = page
                return ret
            },
            insert() {
                this.$emit('insert')
            }
        },

        computed:{
            showPages(){
                let ret = [];
                if (this.objects) {
                    if (this.objects.last_page < 8) {
                        for (let i = 1; i <= this.objects.last_page; i++)
                            ret.push(i);
                    } else {
                        ret.push(1);
                        if (this.objects.current_page < 5) {
                            for (let i = 2; i < 5; i++)
                                ret.push(i);
                        } else {
                            ret.push(parseInt((this.objects.current_page - 1) / 2));
                            if (this.objects.last_page - this.objects.current_page < 4) {
                                for (let i = this.objects.last_page - 4; i <= this.objects.last_page; i++)
                                    ret.push(i);
                            } else {
                                ret.push(this.objects.current_page - 1);
                                ret.push(this.objects.current_page);
                            }
                        }
                        if (this.objects.current_page < 5) {
                            ret.push(5);
                        } else if (this.objects.last_page - this.objects.current_page > 3) {
                            ret.push(this.objects.current_page + 1);
                        }
                        if (this.objects.last_page - this.objects.current_page > 3) {
                            ret.push(parseInt(this.objects.current_page + (this.objects.last_page - this.objects.current_page) / 2));
                            ret.push(this.objects.last_page);
                        }
                    }
                }
                return ret;
            },
            currentPage() {
                return this.objects.current_page
            }
        },
    }
</script>

<style scoped>

</style>
