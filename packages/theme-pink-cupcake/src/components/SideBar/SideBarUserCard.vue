<script setup lang="ts">
export interface Props {
    username?: string
    avatar?: string
    meta?: string
}

withDefaults(defineProps<Props>(), {
    username: '',
    avatar: '',
    meta: ''
})
</script>

<template>
    <div class="uk-section-xsmall uk-text-center" v-bind="$attrs">
        <img v-if="avatar" :src="avatar" alt="avatar" class="uk-border-circle" uk-height-match />
        <p class="uk-margin-remove" v-if="username" data-test="username">{{ username }}</p>
        <p class="uk-margin-remove uk-text-meta" v-if="meta" data-test="meta">({{ meta }})</p>
    </div>
</template>
