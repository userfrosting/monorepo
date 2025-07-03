<template>
    <UFCardBox>
        <UFSprunjeTable dataUrl="/api/permissions" searchColumn="name">
            <template #header>
                <UFSprunjeHeader sort="name">{{ $t('PERMISSION') }}</UFSprunjeHeader>
                <UFSprunjeHeader sort="properties">{{ $t('DESCRIPTION') }}</UFSprunjeHeader>
            </template>

            <template #body="{ item }">
                <UFSprunjeColumn>
                    <strong>
                        <RouterLink
                            :to="{
                                name: 'admin.permission',
                                params: { id: item.id }
                            }">
                            {{ item.name }}
                        </RouterLink>
                    </strong>
                </UFSprunjeColumn>
                <UFSprunjeColumn>{{ item.description }}</UFSprunjeColumn>
            </template>
        </UFSprunjeTable>
    </UFCardBox>
</template>
