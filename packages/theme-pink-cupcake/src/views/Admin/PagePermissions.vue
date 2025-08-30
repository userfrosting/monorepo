<template>
    <UFCardBox>
        <UFSprunjeTable dataUrl="/api/permissions" searchColumn="name">
            <template #header>
                <UFSprunjeHeader sort="name">{{ $t('PERMISSION') }}</UFSprunjeHeader>
                <UFSprunjeHeader sort="properties">{{ $t('DESCRIPTION') }}</UFSprunjeHeader>
            </template>

            <template #body="{ row }">
                <UFSprunjeColumn>
                    <strong>
                        <RouterLink
                            :to="{
                                name: 'admin.permission',
                                params: { id: row.id }
                            }">
                            {{ row.name }}
                        </RouterLink>
                    </strong>
                </UFSprunjeColumn>
                <UFSprunjeColumn>{{ row.description }}</UFSprunjeColumn>
            </template>
        </UFSprunjeTable>
    </UFCardBox>
</template>
