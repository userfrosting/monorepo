<script setup lang="ts">
import { useRouter } from 'vue-router'
const router = useRouter()
</script>

<template>
    <UFPageLogin
        @goto-registration="router.push({ name: 'account.register' })"
        @goto-forgot-password="router.push({ name: 'account.forgot-password' })"
        @goto-verification="router.push({ name: 'account.verification' })" />
</template>
