<template>
    <div>
        <div class="logo">
            <img src="../../images/logo_admin.png" alt="">
        </div>
        <group title="使用姓名密码登录">
            <x-input v-model="name" title="姓名" placeholder="请输入您的姓名" novalidate :show-clear="false"></x-input>
            <x-input v-model="password" title="密码" placeholder="请输入密码" type="password" novalidate :show-clear="false"></x-input>
        </group>
        <Box gap="30px 20px">
            <x-button @click.native="login" type="primary">登录</x-button>
        </Box>
    </div>
</template>

<script>
    import { Group, XInput, Box, XButton } from 'vux'
    export default {
        components: {
            Group, XInput, Box, XButton
        },
        data () {
            return {
                name: '',
                password: ''
            }
        },
        methods: {
            login () {
                this.$http.post('login', {
                    name: this.name,
                    password: this.password
                }).then(res => {
                    this.$router.push({name: 'index'});
                })
            }
        }
    }
</script>

<style lang="less" scoped>
    .logo{
        width: 100px;
        height: 100px;
        border-radius: 50%;
        margin: 60px auto;
        img{
            width: 100%;
            transform: scale(.7);
        }
    }
</style>