<script setup lang="ts">
import { inject } from 'vue'
import type { Sprunjer } from '@userfrosting/sprinkle-core/interfaces'
import { useTranslator } from '@userfrosting/sprinkle-core/stores'

const sprunjer = inject('sprunjer') as Sprunjer
</script>

<template>
    <button class="uk-button uk-button-default uk-button-small" type="button">
        <font-awesome-icon icon="ellipsis" />
    </button>
    <div uk-dropdown="pos: bottom-right">
        <ul class="uk-nav uk-dropdown-nav">
            <li>
                <a href="#" @click="sprunjer.downloadCsv()">
                    <font-awesome-icon icon="download" />
                    {{ useTranslator().translate('DOWNLOAD.CSV') }}
                </a>
            </li>
        </ul>
    </div>
</template>
