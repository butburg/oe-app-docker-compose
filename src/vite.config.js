import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';



export default defineConfig(({ mode }) => {

    const env = loadEnv(mode, process.cwd(), '')
    console.log()
    // vite config
    return defineConfig({
        server: {
            //origin: 'http://127.0.0.1:8080', // Defines the origin of the generated asset URLs during development.
            host: '0.0.0.0', // Bind to all network interfaces inside the container
            port: 5173,      // Specify the strict port to use for Vite
            strictPort: true, // Enforce strict port usage
            hmr: {
                host: env.VITE_APP_NAME || 'localhost',
                port: 5173,
            }
        },
        plugins: [
            laravel({
                input: [
                    'resources/css/app.css',
                    'resources/js/app.js',
                ],
                refresh: true,
            }),
        ],
    })
});
