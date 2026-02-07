import path from "path";
import tailwindcss from "@tailwindcss/vite";
import react from "@vitejs/plugin-react-swc";
import laravel from 'laravel-vite-plugin';
import { visualizer } from "rollup-plugin-visualizer";
import { defineConfig, type PluginOption } from "vite";

const getPackageName = (id: string): string | null => {
  const match = id.match(/node_modules\/([^/]+\/[^/]+|[^/]+)/);
  return match ? match[1] : null;
};

const vendorChunkGroups = [
  { name: "vendor-core", match: (pkg: string) => pkg === "react" || pkg === "react-dom" || pkg.startsWith("@inertiajs/") },
  { name: "ui-radix", match: (pkg: string) => pkg.startsWith("@radix-ui/") },
  { name: "ui-icons", match: (pkg: string) => pkg === "lucide-react" },
  { name: "dates", match: (pkg: string) => pkg === "date-fns" },
  { name: "forms", match: (pkg: string) => pkg === "zod" },
  {
    name: "ui-widgets",
    match: (pkg: string) =>
      pkg === "sonner" ||
      pkg === "cmdk" ||
      pkg === "vaul" ||
      pkg === "embla-carousel-react" ||
      pkg === "input-otp" ||
      pkg === "react-day-picker",
  },
  {
    name: "ui-utils",
    match: (pkg: string) =>
      pkg === "clsx" ||
      pkg === "class-variance-authority" ||
      pkg === "tailwind-merge" ||
      pkg === "tailwindcss-animate",
  },
  { name: "charts-vendor", match: (pkg: string) => pkg === "recharts" || pkg.startsWith("d3-") },
];

const manualChunks = (id: string): string | undefined => {
  if (!id.includes("node_modules")) return;
  const pkg = getPackageName(id);
  if (!pkg) return;
  for (const group of vendorChunkGroups) {
    if (group.match(pkg)) return group.name;
  }
  return;
};

// https://vitejs.dev/config/
export default defineConfig(({ mode }) => {
  const isProd = mode === "production";
  return {
    plugins: [
      laravel({
        input: ['resources/js/app.tsx'],
        ssr: 'resources/js/ssr.tsx',
        refresh: true,
      }),
      react(),
      tailwindcss(),
      process.env.ANALYZE === "true" &&
        (visualizer({
          filename: "stats.html",
          template: "treemap",
          gzipSize: true,
          brotliSize: true,
          open: !process.env.CI,
        }) as PluginOption),
    ].filter(Boolean),
    resolve: {
      alias: {
        "@": path.resolve(__dirname, "./resources/js"),
      },
    },
    build: {
      target: "es2022",
      rollupOptions: {
        output: {
          manualChunks,
        },
      },
      chunkSizeWarningLimit: 500,
    },
    esbuild: {
      target: "es2022",
      drop: isProd ? ["console", "debugger"] : [],
    },
  };
});
