<script setup lang="ts">
import { useRouter } from 'vue-router'
import { Severity } from '@userfrosting/sprinkle-core/interfaces'
import type { UserResponse } from '@userfrosting/sprinkle-admin/interfaces'
import UserEditModal from './UserEditModal.vue'
import UserDeleteModal from './UserDeleteModal.vue'
import UserActivateModal from './UserActivateModal.vue'
import UserPasswordModal from './UserPasswordModal.vue'
import UserPasswordResetModal from './UserPasswordResetModal.vue'

const router = useRouter()
const { user } = defineProps<{
    user: UserResponse
}>()

const emits = defineEmits(['updated'])
</script>

<template>
    <UFCardBox>
        <div class="uk-text-center uk-margin">
            <img :src="user.avatar" alt="avatar" class="uk-border-circle" />
            <h3 class="uk-text-center uk-margin-remove">{{ user.full_name }}</h3>
            <p class="uk-margin-remove uk-text-meta" v-if="user.user_name" data-test="meta">
                ({{ user.user_name }})
            </p>
            <slot data-test="slot"></slot>
        </div>
        <hr />
        <!-- TODO : Find a way to slot the description list -->
        <dl class="uk-description-list" v-if="$checkAccess('view_user_field')">
            <dt><font-awesome-icon icon="envelope" /> {{ $t('EMAIL') }}</dt>
            <dd class="uk-text-meta">{{ user.email }}</dd>
            <dd class="uk-text-meta">
                <UFLabel :severity="Severity.Warning" v-if="user.flag_verified == false">
                    {{ $t('UNVERIFIED') }}
                </UFLabel>
                <UFLabel :severity="Severity.Success" v-else>
                    {{ $t('VERIFIED') }}
                </UFLabel>
            </dd>
            <dt><font-awesome-icon icon="users" /> {{ $t('GROUP') }}</dt>
            <dd class="uk-text-meta" v-if="user.group">
                <router-link :to="{ name: 'admin.group', params: { slug: user.group.slug } }">
                    {{ user.group.name }}
                </router-link>
            </dd>
            <dd class="uk-text-meta" v-else>
                <i>{{ $t('NONE') }}</i>
            </dd>
            <dt><font-awesome-icon icon="language" /> {{ $t('LOCALE') }}</dt>
            <dd class="uk-text-meta">{{ user.locale_name }}</dd>
            <dt><font-awesome-icon icon="user-shield" /> {{ $t('STATUS') }}</dt>
            <dd class="uk-text-meta">
                <UFLabel :severity="Severity.Danger" v-if="user.flag_enabled == false">
                    {{ $t('DISABLED') }}
                </UFLabel>
                <UFLabel :severity="Severity.Success" v-else>
                    {{ $t('ACTIVE') }}
                </UFLabel>
            </dd>
            <dt><font-awesome-icon icon="calendar" /> {{ $t('CREATED_ON') }}</dt>
            <dd class="uk-text-meta">{{ $tdate(user.created_at, 'DDD') }}</dd>
            <!-- <slot data-test="slot"></slot> -->
        </dl>
        <hr />
        <UserEditModal
            :user="user"
            @saved="emits('updated')"
            v-if="$checkAccess('update_user_field')"
            class="uk-width-1-1 uk-margin-small-bottom uk-button uk-button-primary uk-button-small" />
        <UserPasswordModal
            :user="user"
            v-if="$checkAccess('update_user_field')"
            class="uk-width-1-1 uk-margin-small-bottom uk-button uk-button-default uk-button-small" />
        <UserPasswordResetModal
            :user="user"
            v-if="$checkAccess('update_user_field')"
            class="uk-width-1-1 uk-margin-small-bottom uk-button uk-button-default uk-button-small" />
        <UserActivateModal
            :user="user"
            @saved="emits('updated')"
            v-if="$checkAccess('update_user_field')"
            class="uk-width-1-1 uk-margin-small-bottom uk-button uk-button-default uk-button-small" />
        <UserDeleteModal
            :user="user"
            @deleted="router.push({ name: 'admin.users' })"
            v-if="$checkAccess('delete_user')"
            class="uk-width-1-1 uk-margin-small-bottom uk-button uk-button-danger uk-button-small" />
        <slot data-test="slot"></slot>
    </UFCardBox>
</template>
