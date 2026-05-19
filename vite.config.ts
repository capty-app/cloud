import inertia from '@inertiajs/vite';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.tsx'],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        inertia(),
        react({
            babel: {
                plugins: ['babel-plugin-react-compiler'],
            },
        }),
        tailwindcss(),
    ],
    resolve: {
        // Force every dependency that imports "react" / "react-dom" to resolve
        // to the same instance. Without this, Vite 8 + Rolldown can wrap CJS
        // packages (e.g. @radix-ui/react-use-previous) in a __toESM shim that
        // loses its React reference during chunk splitting, producing
        // "Cannot read properties of null (reading 'useMemo')" at runtime.
        dedupe: ['react', 'react-dom', 'react/jsx-runtime'],
    },
    // Treat the affected Radix package's CJS as ESM during interop so it isn't
    // wrapped in __toESM(require_react()).
    build: {
        commonjsOptions: {
            transformMixedEsModules: true,
        },
        rollupOptions: {
            output: {
                // Keep React-using packages together with React in the same
                // chunk so the CJS interop helpers and React are always
                // initialized in the same evaluation step.
                manualChunks(id) {
                    if (id.includes('node_modules/react/') || id.includes('node_modules/react-dom/') || id.includes('node_modules/scheduler/')) {
                        return 'vendor-react';
                    }
                    if (id.includes('node_modules/@radix-ui/')) {
                        return 'vendor-react';
                    }
                },
            },
        },
    },
});
