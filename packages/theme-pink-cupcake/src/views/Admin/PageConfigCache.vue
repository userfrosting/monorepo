<script setup lang="ts">
import { useConfigCacheApi } from '@userfrosting/sprinkle-admin/composables'
const { clearCache, loading } = useConfigCacheApi()
</script>

<template>
    <UFCardBox :title="$t('SITE_CONFIG.CACHE')">
        <div class="uk-text-center">
            <button
                class="uk-button uk-button-primary"
                :disabled="loading"
                uk-toggle="target: #confirm-clear-cache">
                {{ $t('SITE_CONFIG.CACHE.CLEAR') }}
            </button>
        </div>
    </UFCardBox>

    <!-- This is the modal -->
    <UFModalConfirmation
        id="confirm-clear-cache"
        @confirmed="clearCache()"
        acceptLabel="SITE_CONFIG.CACHE.CLEAR_CONFIRM_YES"
        prompt="SITE_CONFIG.CACHE.CLEAR_CONFIRM"
        warning="">
    </UFModalConfirmation>
</template>
