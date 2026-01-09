/// <reference types="vitest" />
import { configDefaults } from 'vitest/config'
import { defineConfig } from 'vite'
import { resolve } from 'path'
import vue from '@vitejs/plugin-vue'
import ViteYaml from '@modyfi/vite-plugin-yaml'
import dts from 'vite-plugin-dts'

// https://vitejs.dev/config/
// https://stackoverflow.com/a/74397545/445757
export default defineConfig({
    plugins: [
        vue(),
        ViteYaml(),
        dts({
            include: ['env.d.ts', 'app/assets/**/*.ts', 'app/assets/**/*.vue'],
            exclude: ['app/assets/tests/**/*'],
            outDir: 'dist',
            copyDtsFiles: true,
            rollupTypes: false,
            compilerOptions: {
                customConditions: ['development']
            }
        })
    ],
    build: {
        lib: {
            entry: {
                index: resolve(__dirname, 'app/assets/index.ts'),
                components: resolve(__dirname, 'app/assets/components/index.ts'),
                composables: resolve(__dirname, 'app/assets/composables/index.ts'),
                interfaces: resolve(__dirname, 'app/assets/interfaces/index.ts'),
                routes: resolve(__dirname, 'app/assets/routes/index.ts'),
                views: resolve(__dirname, 'app/assets/views/index.ts')
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
                'limax',
                '@userfrosting/sprinkle-core',
                '@userfrosting/sprinkle-core/composables',
                '@userfrosting/sprinkle-core/interfaces',
                '@userfrosting/sprinkle-core/stores',
                '@userfrosting/sprinkle-account',
                '@userfrosting/sprinkle-account/composables',
                '@userfrosting/sprinkle-account/guards',
                '@userfrosting/sprinkle-account/interfaces',
                '@userfrosting/sprinkle-account/stores'
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
            exclude: ['app/assets/tests/**/*.*']
        },
        environment: 'happy-dom',
        exclude: [
            ...configDefaults.exclude,
            './vendor/**/*.*',
        ],
    }
})
