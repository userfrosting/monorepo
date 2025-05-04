<script setup lang="ts">
defineProps({
    title: String
})
</script>

<template>
    <div class="uk-flex uk-flex-center uk-flex-middle" uk-grid>
        <div class="uk-width-1-1@s uk-width-1-2@l">
            <UFCardBox>
                <template #header v-if="$slots.header || title">
                    <slot name="header">{{ title }}</slot>
                </template>
                <slot />
                <template #footer v-if="$slots.footer">
                    <slot name="footer"></slot>
                </template>
            </UFCardBox>
        </div>
    </div>
</template>
