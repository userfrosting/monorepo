<script setup lang="ts">
const { errors } = defineProps<{
    errors: string[]
}>()
</script>

<template>
    <div class="uk-form-label uk-text-danger" v-for="error of errors" :key="error" v-html="error" />
</template>
