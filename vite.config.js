import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
});



// import { defineConfig } from 'vite'
// import laravel from 'laravel-vite-plugin'

// export default defineConfig({
//   server: {
//     host: '0.0.0.0',
//     port: 5173,
//     cors: {
//       origin: ['https://c2d4c328861a.ngrok-free.app'], // troque pelo seu
//       credentials: true,
//     },
//   },
//   plugins: [
//     laravel({
//       input: ['resources/css/app.css', 'resources/js/app.js'],
//       refresh: true,
//     }),
//   ],
// })
