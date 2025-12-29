<script setup lang="ts">
defineProps<{
    label: string
}>()
</script>

<template>
    <li class="uk-nav-header" v-bind="$attrs">{{ label }}</li>
</template>
