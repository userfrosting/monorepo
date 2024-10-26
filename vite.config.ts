/// <reference types="vitest" />
import { configDefaults } from 'vitest/config'
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import dts from 'vite-plugin-dts'

// https://vitejs.dev/config/
// https://stackoverflow.com/a/74397545/445757
export default defineConfig({
    plugins: [vue(), dts()],
    publicDir: false,
    build: {
        outDir: './dist',
        lib: {
            entry: {
                plugin: 'app/assets/plugin.ts',
                components: 'app/assets/components/index.ts',
                'composable/dashboard': 'app/assets/composable/dashboard.ts',
                'composable/group': 'app/assets/composable/group.ts',
                'composable/permission': 'app/assets/composable/permission.ts',
                'composable/role': 'app/assets/composable/role.ts',
                'composable/user': 'app/assets/composable/user.ts',
                // types: 'app/assets/interfaces/index.ts',
                // guards: 'app/assets/guards/authGuard.ts',
                // stores: 'app/assets/stores/auth.ts'
                routes: 'app/assets/router/routes.ts'
            }
        },
        rollupOptions: {
            external: ['vue', 'vue-router', 'pinia'],
            output: {
                exports: 'named',
                globals: {
                    vue: 'Vue',
                    'vue-router': 'vueRouter'
                }
            }
        }
    },
    test: {
        coverage: {
            reportsDirectory: './_meta/_coverage',
            include: ['app/assets/**/*.*'],
            exclude: ['app/assets/tests/**/*.*']
        },
        environment: 'happy-dom',
        exclude: [
            ...configDefaults.exclude,
            './vendor/**/*.*',
        ],
    }
})
