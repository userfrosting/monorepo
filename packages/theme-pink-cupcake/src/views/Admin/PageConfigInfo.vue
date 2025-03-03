<script setup lang="ts">
import { useConfigSystemInfoApi } from '@userfrosting/sprinkle-admin/composables'
const { load, data } = useConfigSystemInfoApi()

// Load the data on page access
load()
</script>

<template>
    <UFCardBox :title="$t('SITE_CONFIG.SYSTEM_INFO')">
        <dl class="uk-description-list">
            <dt>{{ $t('SITE_CONFIG.SYSTEM_INFO.UF_VERSION') }}</dt>
            <dd>
                <pre><code>{{ data.frameworkVersion }}</code></pre>
            </dd>

            <dt>{{ $t('SITE_CONFIG.SYSTEM_INFO.PHP_VERSION') }}</dt>
            <dd>
                <pre><code>{{ data.phpVersion }}</code></pre>
            </dd>

            <dt>{{ $t('SITE_CONFIG.SYSTEM_INFO.SERVER') }}</dt>
            <dd>
                <pre><code>{{ data.server }}</code></pre>
            </dd>

            <dt>{{ $t('SITE_CONFIG.SYSTEM_INFO.DB_CONNECTION') }}</dt>
            <dd>
                <pre><code>{{ data.database.connection }}</code></pre>
            </dd>

            <dt>{{ $t('SITE_CONFIG.SYSTEM_INFO.DB_VERSION') }}</dt>
            <dd>
                <pre><code>{{ data.database.type }} {{ data.database.version }}</code></pre>
            </dd>

            <dt>{{ $t('SITE_CONFIG.SYSTEM_INFO.DB_NAME') }}</dt>
            <dd>
                <pre><code>{{ data.database.name }}</code></pre>
            </dd>

            <dt>{{ $t('SITE_CONFIG.SYSTEM_INFO.DIRECTORY') }}</dt>
            <dd>
                <pre><code>{{ data.projectPath }}</code></pre>
            </dd>

            <dt>{{ $t('SITE_CONFIG.SYSTEM_INFO.SPRINKLES') }}</dt>
            <dd>
                <ul class="uk-list uk-list-disc uk-list-collapse">
                    <li v-for="sprinkle in data.sprinkles" :key="sprinkle">
                        {{ sprinkle }}
                    </li>
                </ul>
            </dd>
        </dl>
    </UFCardBox>
</template>
