import path from 'path';
import react from '@vitejs/plugin-react-swc';
import { defineConfig } from 'vitest/config';

export default defineConfig({
  plugins: [react()],
  test: {
    globals: true,
    environment: 'jsdom',
    setupFiles: ['./resources/js/test/setup.ts'],
    include: ['resources/js/**/*.test.{ts,tsx}'],
    exclude: [
      '**/node_modules/**',
      '**/dist/**',
      '**/cypress/**',
      '**/.{idea,git,cache,output,temp}/**',
    ],
    coverage: {
      provider: 'istanbul',
      reporter: ['text', 'html', 'lcov'],
      reportsDirectory: './coverage/frontend',
      include: ['resources/js/**/*.{ts,tsx}'],
      exclude: [
        'resources/js/**/*.test.{ts,tsx}',
        'resources/js/test/**',
        'resources/js/types/**',
        'resources/js/**/*.d.ts',
      ],
    },
  },
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './resources/js'),
    },
  },
});
