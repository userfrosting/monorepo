<script setup lang="ts">
defineProps({
    title: String
})
</script>

<template>
    <div class="uk-card uk-card-default uk-card-small">
        <div v-if="title || $slots.header" class="uk-card-header">
            <h4 data-test="title">
                <slot name="header">{{ $t(title ?? '') }}</slot>
            </h4>
        </div>
        <div class="uk-card-body uk-card-small" data-test="slot">
            <slot></slot>
        </div>
        <div v-if="$slots.footer" class="uk-card-footer" data-test="footer">
            <slot name="footer"></slot>
        </div>
    </div>
</template>
