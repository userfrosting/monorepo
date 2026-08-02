/// <reference types="vitest" />
import { defineConfig } from 'vitest/config'
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
            include: ['env.d.ts', 'src/**/*.ts', 'src/**/*.vue'],
            exclude: ['src/tests/**/*'],
            outDir: 'dist',
            copyDtsFiles: true,
            rollupTypes: false,
            compilerOptions: {
                customConditions: ['userfrosting:monorepo']
            }
        })
    ],
    // Add UIKit alias : https://stackoverflow.com/a/75264118/445757
    resolve: {
        conditions: ['userfrosting:monorepo', 'import'],
        alias: {
            '../../images/backgrounds': 'uikit/src/images/backgrounds',
            '../../images/components': 'uikit/src/images/components',
            '../../images/icons': 'uikit/src/images/icons'
        }
    },
    build: {
        lib: {
            entry: {
                index: resolve(import.meta.dirname, 'src/index.ts'),
                'components/pages/account': resolve(
                    import.meta.dirname,
                    'src/components/Pages/Account/index.ts'
                ),
                'components/pages/admin': resolve(import.meta.dirname, 'src/components/Pages/Admin/index.ts'),
                components: resolve(import.meta.dirname, 'src/components/index.ts'),
                plugins: resolve(import.meta.dirname, 'src/plugins/index.ts'),
                views: resolve(import.meta.dirname, 'src/views/index.ts')
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
                'uikit',
                'uikit/dist/js/uikit-icons',
                '@fontsource/montserrat',
                '@fontsource/mulish',
                '@fortawesome/fontawesome-free',
                '@fortawesome/fontawesome-svg-core',
                '@fortawesome/free-brands-svg-icons',
                '@fortawesome/free-regular-svg-icons',
                '@fortawesome/free-solid-svg-icons',
                '@fortawesome/vue-fontawesome',
                '@userfrosting/sprinkle-core',
                '@userfrosting/sprinkle-core/composables',
                '@userfrosting/sprinkle-core/interfaces',
                '@userfrosting/sprinkle-core/stores',
                '@userfrosting/sprinkle-account',
                '@userfrosting/sprinkle-account/composables',
                '@userfrosting/sprinkle-account/guards',
                '@userfrosting/sprinkle-account/interfaces',
                '@userfrosting/sprinkle-account/stores',
                '@userfrosting/sprinkle-admin',
                '@userfrosting/sprinkle-admin/components',
                '@userfrosting/sprinkle-admin/composables',
                '@userfrosting/sprinkle-admin/interfaces'
            ],
            output: {
                preserveModules: false,
                entryFileNames: '[name].js',
                assetFileNames: (assetInfo) => {
                    // Keep LESS files in their original location
                    if (assetInfo.name?.endsWith('.less')) {
                        return 'less/[name][extname]'
                    }
                    return 'assets/[name][extname]'
                }
            }
        }
    },
    test: {
        coverage: {
            reportsDirectory: './_meta/_coverage',
            include: ['src/**/*.{js,jsx,ts,tsx,vue}'],
            exclude: ['src/**/.*', 'src/**/*.md', 'src/tests/**/*.*']
        },
        setupFiles: ['src/tests/translator.setup.ts'],
        environment: 'happy-dom'
    }
})
