<script lang="ts" setup>
import type { UserInterface } from '@userfrosting/sprinkle-account/interfaces'
import { useTranslator } from '@userfrosting/sprinkle-core/stores'
const { getDateTime } = useTranslator()

defineProps<{
    users: UserInterface[]
}>()
</script>

<template>
    <!-- TODO : Create user Card template -->
    <UFCardBox :title="$t('USER.LATEST')">
        <div class="uk-grid uk-flex-center" uk-grid>
            <div v-for="user in users" :key="user.id" class="uk-text-center">
                <RouterLink
                    :to="{ name: 'admin.user', params: { user_name: user.user_name } }"
                    class="uk-text-decoration-none uk-link-text">
                    <img :src="user.avatar" alt="User Image" class="uk-border-circle" />
                    <p class="uk-margin-remove">{{ user.full_name }}</p>
                    <p class="uk-margin-remove uk-text-meta">
                        {{ getDateTime(user.created_at).toRelative() }}
                    </p>
                </RouterLink>
            </div>
        </div>
        <template #footer>
            <RouterLink :to="{ name: 'admin.users' }" class="uk-flex uk-flex-center">
                {{ $t('USER.VIEW_ALL') }}
            </RouterLink>
        </template>
    </UFCardBox>
</template>
