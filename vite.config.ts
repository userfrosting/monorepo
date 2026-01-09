import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'
import ViteYaml from '@modyfi/vite-plugin-yaml'

// Get vite port from env, default to 5173
const vitePort = parseInt(process.env.VITE_PORT || '5173', 10)

// https://vitejs.dev/config/
// Monorepo-specific Vite configuration for development
export default defineConfig({
    plugins: [
        vue(),
        ViteYaml(),
        vueDevTools({
            appendTo: 'packages/skeleton/app/assets/main.ts'
        })
    ],
    // Use 'development' condition to resolve to source TS files for HMR in monorepo
    resolve: {
        conditions: ['development', 'import']
    },
    server: {
        host: true, // Allows external access (needed for Docker)
        strictPort: true,
        port: vitePort,
        origin: `http://localhost:${vitePort}`,
    },
    root: 'packages/skeleton/app/assets/',
    base: '/assets/',
    build: {
        outDir: 'public/assets',
        assetsDir: '',
        emptyOutDir: true,
        manifest: true,
        rollupOptions: {
            input: {
                main: 'packages/skeleton/app/assets/main.ts'
            }
        }
    },
    // Fix uikit path issue
    // @see : https://github.com/uikit/uikit/issues/5024
    css: {
        preprocessorOptions: {
            less: {
                relativeUrls: 'all'
            }
        }
    },
    // Force optimization of UiKit (not module packages) in dev mode 
    // to avoid the error:
    // "importing binding name 'default' cannot be resolved by star export entries"
    // Treat all sprinkles as source code (not prebuilt) for HMR
    optimizeDeps: {
        include: ['uikit', 'uikit/dist/js/uikit-icons'],
        exclude: [
            '@userfrosting/sprinkle-core',
            '@userfrosting/sprinkle-account',
            '@userfrosting/sprinkle-admin',
            '@userfrosting/theme-pink-cupcake'
        ]
    }
})
