/// <reference types="vitest" />
import { defineConfig } from 'vite'

export default defineConfig({
    test: {
        coverage: {
            enabled: true,
            reportsDirectory: './_meta/_coverage',
            include: [
                'packages/**/app/assets/**/*.*',
                'packages/**/src/**/*.*'
            ],
        },
        reporters: ['default', 'junit'],
        outputFile: './_meta/junit_frontend.xml'
    }
})
