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
                    class="uk-button uk-button-primary uk-width-1-1 uk-width-auto@s"
                    v-if="$checkAccess('create_role')" />
            </template>

            <template #header>
                <UFSprunjeHeader sort="name">{{ $t('ROLE') }}</UFSprunjeHeader>
                <UFSprunjeHeader sort="description">{{ $t('DESCRIPTION') }}</UFSprunjeHeader>
                <UFSprunjeHeader>{{ $t('ACTIONS') }}</UFSprunjeHeader>
            </template>

            <template #body="{ row, sprunjer }">
                <UFSprunjeColumn class="uk-text-nowrap">
                    <strong>
                        <RouterLink
                            :to="{
                                name: 'admin.role',
                                params: { slug: row.slug }
                            }">
                            {{ row.name }}
                        </RouterLink>
                    </strong>
                </UFSprunjeColumn>
                <UFSprunjeColumn>{{ row.description }}</UFSprunjeColumn>
                <UFSprunjeColumn class="uk-text-center uk-table-shrink uk-text-nowrap">
                    <button
                        class="uk-button uk-button-primary uk-width-1-1 uk-width-auto@s"
                        type="button">
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
                                        params: { slug: row.slug }
                                    }"
                                    v-if="$checkAccess('uri_role')">
                                    <font-awesome-icon icon="eye" fixed-width /> View
                                </RouterLink>
                            </li>
                            <li>
                                <RoleEditModal
                                    :role="row"
                                    @saved="sprunjer.fetch()"
                                    v-if="$checkAccess('update_role_field')"
                                    class="uk-drop-close" />
                            </li>
                            <li>
                                <RoleManagePermissionModal
                                    :role="row"
                                    v-if="$checkAccess('update_role_field')"
                                    class="uk-drop-close" />
                            </li>
                            <li>
                                <RoleDeleteModal
                                    :role="row"
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
