<script setup>
import { Severity } from '@userfrosting/sprinkle-core/interfaces'
import UserCreateModal from '../../components/Pages/Admin/User/UserCreateModal.vue'
import UserEditModal from '../../components/Pages/Admin/User/UserEditModal.vue'
import UserDeleteModal from '../../components/Pages/Admin/User/UserDeleteModal.vue'
import UserActivateModal from '../../components/Pages/Admin/User/UserActivateModal.vue'
import UserPasswordModal from '../../components/Pages/Admin/User/UserPasswordModal.vue'
import UserPasswordResetModal from '../../components/Pages/Admin/User/UserPasswordResetModal.vue'
</script>

<template>
    <UFCardBox>
        <UFSprunjeTable dataUrl="/api/users" searchColumn="name">
            <template #actions="{ sprunjer }">
                <UserCreateModal
                    @saved="sprunjer.fetch()"
                    class="uk-button uk-button-primary"
                    v-if="$checkAccess('create_user')" />
            </template>

            <template #header>
                <UFSprunjeHeader sort="name">{{ $t('USER') }}</UFSprunjeHeader>
                <UFSprunjeHeader sort="last_activity">{{ $t('ACTIVITY.LAST') }}</UFSprunjeHeader>
                <UFSprunjeHeader sort="status">{{ $t('STATUS') }}</UFSprunjeHeader>
                <UFSprunjeHeader sort="status">{{ $t('EMAIL') }}</UFSprunjeHeader>
                <UFSprunjeHeader>{{ $t('ACTIONS') }}</UFSprunjeHeader>
            </template>

            <template #body="{ item, sprunjer }">
                <UFSprunjeColumn>
                    <strong>
                        <RouterLink
                            :to="{
                                name: 'admin.user',
                                params: { user_name: item.user_name }
                            }">
                            {{ item.full_name }} ({{ item.user_name }})
                        </RouterLink>
                    </strong>
                    <div class="uk-text-meta">{{ item.email }}</div>
                </UFSprunjeColumn>
                <UFSprunjeColumn>
                    <div>{{ $tdate(item.last_activity.occurred_at) }}</div>
                    <i>{{ item.last_activity.description }}</i>
                </UFSprunjeColumn>
                <UFSprunjeColumn>
                    <UFLabel :severity="Severity.Danger" v-if="item.flag_enabled == false">
                        {{ $t('DISABLED') }}
                    </UFLabel>
                    <UFLabel :severity="Severity.Success" v-else>
                        {{ $t('ACTIVE') }}
                    </UFLabel>
                </UFSprunjeColumn>
                <UFSprunjeColumn>
                    <UFLabel :severity="Severity.Warning" v-if="item.flag_verified == false">
                        {{ $t('UNVERIFIED') }}
                    </UFLabel>
                    <UFLabel :severity="Severity.Success" v-else>
                        {{ $t('VERIFIED') }}
                    </UFLabel>
                </UFSprunjeColumn>
                <UFSprunjeColumn>
                    <button class="uk-button uk-button-primary uk-text-nowrap" type="button">
                        {{ $t('ACTIONS') }} <span uk-drop-parent-icon></span>
                    </button>
                    <div
                        class="uk-padding-small"
                        uk-dropdown="pos: bottom-right; mode: click; offset: 2">
                        <ul class="uk-nav uk-dropdown-nav">
                            <li>
                                <RouterLink
                                    :to="{
                                        name: 'admin.user',
                                        params: { user_name: item.user_name }
                                    }"
                                    v-if="$checkAccess('uri_user')">
                                    <font-awesome-icon icon="eye" fixed-width /> {{ $t('VIEW') }}
                                </RouterLink>
                            </li>
                            <li>
                                <UserEditModal
                                    :user="item"
                                    @saved="sprunjer.fetch()"
                                    v-if="$checkAccess('update_user_field')"
                                    class="uk-drop-close" />
                            </li>
                            <li>
                                <UserPasswordModal
                                    :user="item"
                                    v-if="$checkAccess('update_user_field')"
                                    class="uk-drop-close" />
                            </li>
                            <li>
                                <UserPasswordResetModal
                                    :user="item"
                                    v-if="$checkAccess('update_user_field')"
                                    class="uk-drop-close" />
                            </li>
                            <li>
                                <UserActivateModal
                                    :user="item"
                                    @saved="sprunjer.fetch()"
                                    v-if="$checkAccess('update_user_field')"
                                    class="uk-drop-close" />
                            </li>
                            <li>
                                <UserDeleteModal
                                    :user="item"
                                    @deleted="sprunjer.fetch()"
                                    v-if="$checkAccess('delete_user')"
                                    class="uk-drop-close" />
                            </li>
                        </ul>
                    </div>
                </UFSprunjeColumn>
            </template>
        </UFSprunjeTable>
    </UFCardBox>
</template>
