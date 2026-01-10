/// <reference types="vitest" />
import { defineConfig } from 'vite'
import { resolve } from 'path'
import vue from '@vitejs/plugin-vue'
import dts from 'vite-plugin-dts'

// https://vitejs.dev/config/
// https://stackoverflow.com/a/74397545/445757
export default defineConfig({
    plugins: [
        vue(),
        dts({
            include: ['env.d.ts', 'app/assets/**/*.ts', 'app/assets/**/*.vue'],
            exclude: ['app/assets/tests/**/*'],
            outDir: 'dist',
            copyDtsFiles: true,
            rollupTypes: false,
            compilerOptions: {
                customConditions: ['userfrosting:monorepo']
            }
        })
    ],
    build: {
        lib: {
            entry: {
                index: resolve(__dirname, 'app/assets/index.ts'),
                interfaces: resolve(__dirname, 'app/assets/interfaces/index.ts'),
                stores: resolve(__dirname, 'app/assets/stores/index.ts'),
                composables: resolve(__dirname, 'app/assets/composables/index.ts'),
                routes: resolve(__dirname, 'app/assets/routes/index.ts')
            },
            formats: ['es']
        },
        rollupOptions: {
            external: [
                'vue',
                'vue-router',
                'axios',
                'pinia',
                'pinia-plugin-persistedstate',
                '@regle/core',
                '@regle/rules',
                'dot-prop',
                'luxon',
                /^@userfrosting\/sprinkle-core/
            ],
            output: {
                preserveModules: false,
                entryFileNames: '[name].js'
            }
        }
    },
    test: {
        coverage: {
            reportsDirectory: './_meta/_coverage',
            include: ['app/assets/**/*.*'],
            // exclude: ['app/assets/tests/**/*.*', 'app/assets/interfaces/routes.ts']
        },
        environment: 'happy-dom'
    }
})
