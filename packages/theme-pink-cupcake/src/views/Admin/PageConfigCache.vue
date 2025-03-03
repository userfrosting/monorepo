<script setup lang="ts">
import UIkit from 'uikit'
import { useConfigCacheApi } from '@userfrosting/sprinkle-admin/composables'
import { useTranslator } from '@userfrosting/sprinkle-core/stores'
const { clearCache, loading } = useConfigCacheApi()

const clearCacheHandler = async () => {
    await clearCache()
        .then((message) => {
            UIkit.notification({
                message: message,
                status: 'success',
                pos: 'top-right',
                timeout: 4000
            })
        })
        .catch((error) => {
            const { translate } = useTranslator()
            UIkit.notification({
                message: error.description ?? translate('ERROR.MISC'),
                status: 'danger',
                pos: 'top-right',
                timeout: 4000
            })
        })
}
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
        @confirmed="clearCacheHandler()"
        acceptLabel="SITE_CONFIG.CACHE.CLEAR_CONFIRM_YES"
        prompt="SITE_CONFIG.CACHE.CLEAR_CONFIRM"
        warning="">
    </UFModalConfirmation>
</template>
