/// <reference types="vitest" />
import { defineConfig } from 'vitest/config'
import { resolve } from 'path'
import vue from '@vitejs/plugin-vue'
import dts from 'vite-plugin-dts'

// https://vitejs.dev/config/
// https://stackoverflow.com/a/74397545/445757
export default defineConfig({
    resolve: {
        conditions: ['userfrosting:monorepo', 'import']
    },
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
                index: resolve(import.meta.dirname, 'app/assets/index.ts'),
                interfaces: resolve(import.meta.dirname, 'app/assets/interfaces/index.ts'),
                stores: resolve(import.meta.dirname, 'app/assets/stores/index.ts'),
                composables: resolve(import.meta.dirname, 'app/assets/composables/index.ts'),
                routes: resolve(import.meta.dirname, 'app/assets/routes/index.ts')
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
            include: ['app/assets/**/*.{js,jsx,ts,tsx,vue}'],
            exclude: [
                'app/assets/**/.*',
                'app/assets/**/*.md',
                'app/assets/tests/**/*.*',
                'app/assets/interfaces/routes.ts'
            ]
        },
        environment: 'happy-dom'
    }
})
