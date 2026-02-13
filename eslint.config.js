import js from "@eslint/js";
import typescript from "@typescript-eslint/eslint-plugin";
import typescriptParser from "@typescript-eslint/parser";
import reactHooks from "eslint-plugin-react-hooks";
import reactRefresh from "eslint-plugin-react-refresh";
import importPlugin from "eslint-plugin-import";

export default [
    {
        ignores: [
            "vendor/**",
            "node_modules/**",
            "public/**",
            "bootstrap/ssr/**",
            "storage/**",
            "coverage/**",
            "coverage-report/**",
            "*.config.js",
            "*.config.ts",
            "resources/js/ziggy.js",
        ],
    },
    js.configs.recommended,
    {
        files: ["resources/js/**/*.{ts,tsx}"],
        languageOptions: {
            parser: typescriptParser,
            parserOptions: {
                ecmaVersion: "latest",
                sourceType: "module",
                ecmaFeatures: {
                    jsx: true,
                },
            },
            globals: {
                document: "readonly",
                window: "readonly",
                console: "readonly",
                setTimeout: "readonly",
                clearTimeout: "readonly",
                setInterval: "readonly",
                clearInterval: "readonly",
                fetch: "readonly",
                URL: "readonly",
                URLSearchParams: "readonly",
                FormData: "readonly",
                Intl: "readonly",
                localStorage: "readonly",
                sessionStorage: "readonly",
                navigator: "readonly",
                location: "readonly",
                history: "readonly",
                alert: "readonly",
                confirm: "readonly",
                prompt: "readonly",
                requestAnimationFrame: "readonly",
                cancelAnimationFrame: "readonly",
                ResizeObserver: "readonly",
                IntersectionObserver: "readonly",
                MutationObserver: "readonly",
                CustomEvent: "readonly",
                Event: "readonly",
                MouseEvent: "readonly",
                KeyboardEvent: "readonly",
                HTMLElement: "readonly",
                HTMLInputElement: "readonly",
                HTMLTextAreaElement: "readonly",
                HTMLSelectElement: "readonly",
                HTMLFormElement: "readonly",
                HTMLButtonElement: "readonly",
                Element: "readonly",
                Node: "readonly",
                NodeList: "readonly",
                DOMRect: "readonly",
                SVGElement: "readonly",
                File: "readonly",
                FileList: "readonly",
                Blob: "readonly",
                AbortController: "readonly",
                AbortSignal: "readonly",
                Headers: "readonly",
                Request: "readonly",
                Response: "readonly",
                crypto: "readonly",
                performance: "readonly",
                queueMicrotask: "readonly",
                structuredClone: "readonly",
                atob: "readonly",
                btoa: "readonly",
                process: "readonly",
                __dirname: "readonly",
                module: "readonly",
                require: "readonly",
                exports: "readonly",
                global: "readonly",
                Buffer: "readonly",
            },
        },
        plugins: {
            "@typescript-eslint": typescript,
            "react-hooks": reactHooks,
            "react-refresh": reactRefresh,
            import: importPlugin,
        },
        rules: {
            // TypeScript rules
            "@typescript-eslint/no-unused-vars": [
                "warn",
                {
                    argsIgnorePattern: "^_",
                    varsIgnorePattern: "^_",
                },
            ],
            "@typescript-eslint/no-explicit-any": "warn",

            // React hooks rules
            "react-hooks/rules-of-hooks": "error",
            "react-hooks/exhaustive-deps": "warn",

            // React refresh rules
            "react-refresh/only-export-components": [
                "warn",
                { allowConstantExport: true },
            ],

            // Import rules
            "import/order": [
                "warn",
                {
                    groups: [
                        "builtin",
                        "external",
                        "internal",
                        "parent",
                        "sibling",
                        "index",
                    ],
                    "newlines-between": "always",
                    pathGroups: [
                        {
                            pattern: "react",
                            group: "external",
                            position: "after",
                        },
                        {
                            pattern: "@inertiajs/**",
                            group: "external",
                            position: "after",
                        },
                        {
                            pattern: "@/**",
                            group: "internal",
                            position: "after",
                        },
                    ],
                    pathGroupsExcludedImportTypes: ["react"],
                    alphabetize: {
                        order: "asc",
                        caseInsensitive: true,
                    },
                },
            ],

            // General rules
            "no-console": ["warn", { allow: ["warn", "error"] }],
            "no-unused-vars": "off", // Use TypeScript's version
            "no-undef": "off", // TypeScript handles this
        },
        settings: {
            "import/resolver": {
                typescript: {
                    alwaysTryTypes: true,
                },
            },
        },
    },
];
