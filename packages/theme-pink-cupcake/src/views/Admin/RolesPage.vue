<script setup>
import RoleCreateModal from '../../components/Pages/Admin/Role/RoleCreateModal.vue'
import RoleEditModal from '../../components/Pages/Admin/Role/RoleEditModal.vue'
import RoleDeleteModal from '../../components/Pages/Admin/Role/RoleDeleteModal.vue'
import RoleManagePermissionModal from '../../components/Pages/Admin/Role/RoleManagePermissionModal.vue'
</script>

<template>
    <UFCardBox>
        <UFSprunjeTable dataUrl="/api/roles" searchColumn="name">
            <template #actions="{ sprunjer }">
                <RoleCreateModal
                    @saved="sprunjer.fetch()"
                    class="uk-button uk-button-primary"
                    v-if="$checkAccess('create_role')" />
            </template>

            <template #header>
                <UFSprunjeHeader sort="name">{{ $t('ROLE') }}</UFSprunjeHeader>
                <UFSprunjeHeader sort="description">{{ $t('DESCRIPTION') }}</UFSprunjeHeader>
                <UFSprunjeHeader>{{ $t('ACTIONS') }}</UFSprunjeHeader>
            </template>

            <template #body="{ item, sprunjer }">
                <UFSprunjeColumn>
                    <strong>
                        <RouterLink
                            :to="{
                                name: 'admin.role',
                                params: { slug: item.slug }
                            }">
                            {{ item.name }}
                        </RouterLink>
                    </strong>
                </UFSprunjeColumn>
                <UFSprunjeColumn>{{ item.description }}</UFSprunjeColumn>
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
                                        name: 'admin.role',
                                        params: { slug: item.slug }
                                    }"
                                    v-if="$checkAccess('uri_role')">
                                    <font-awesome-icon icon="eye" fixed-width /> View
                                </RouterLink>
                            </li>
                            <li>
                                <RoleEditModal
                                    :role="item"
                                    @saved="sprunjer.fetch()"
                                    v-if="$checkAccess('update_role_field')"
                                    class="uk-drop-close" />
                            </li>
                            <li>
                                <RoleManagePermissionModal
                                    :role="item"
                                    v-if="$checkAccess('update_role_field')"
                                    class="uk-drop-close" />
                            </li>
                            <li>
                                <RoleDeleteModal
                                    :role="item"
                                    @deleted="sprunjer.fetch()"
                                    v-if="$checkAccess('delete_role')"
                                    class="uk-drop-close" />
                            </li>
                        </ul>
                    </div>
                </UFSprunjeColumn>
            </template>
        </UFSprunjeTable>
    </UFCardBox>
</template>
