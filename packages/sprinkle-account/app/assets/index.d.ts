export {}

declare module 'vue' {
    interface ComponentCustomProperties {
        $checkAccess: (slug: string) => Boolean
    }
}
