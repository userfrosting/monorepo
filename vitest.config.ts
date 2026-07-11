/// <reference types="vitest" />
import { defineConfig } from 'vitest/config'

export default defineConfig({
    resolve: {
        conditions: ['userfrosting:monorepo', 'import']
    },
    test: {
        coverage: {
            enabled: true,
            reportsDirectory: './_meta/_coverage',
            include: [
                'packages/**/app/assets/**/*.{js,jsx,ts,tsx,vue}',
                'packages/theme-pink-cupcake/src/**/*.{js,jsx,ts,tsx,vue}'
            ],
            exclude: ['**/.*', '**/*.md']
        },
        reporters: ['default', 'junit'],
        outputFile: './_meta/junit_frontend.xml',
        projects: [
            './packages/theme-pink-cupcake/vite.config.ts',
            './packages/sprinkle-core/vite.config.ts',
            './packages/sprinkle-account/vite.config.ts',
            './packages/sprinkle-admin/vite.config.ts',
            './packages/skeleton/vite.config.ts'
        ]
    }
})
