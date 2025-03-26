export {}

declare module 'vue' {
    interface ComponentCustomProperties {
        $t: (key: string, placeholders?: string | number | object) => string
        $tdate: (date: string, format?: string | object) => string
    }
}
