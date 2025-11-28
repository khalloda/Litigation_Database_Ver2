import path from 'path';
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
  plugins: [react()],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, '.'),
    },
  },
  build: {
    outDir: 'dist', // Default - will copy to Laravel public/ during deployment
    emptyOutDir: true,
    // Ensure relative paths for assets
    assetsDir: 'assets',
    rollupOptions: {
      output: {
        manualChunks: undefined, // Or configure code splitting
      },
    },
  },
  // Removed Gemini API key from frontend - now handled by Laravel backend
});
