import { useEffect, useState, ReactNode } from "react";
import { usePage } from "@inertiajs/react";
import axios from "axios";
import { ThemeContext } from "./theme-context";

type Theme = "light" | "dark" | "system";

function getSystemTheme(): "light" | "dark" {
  if (typeof window === "undefined") return "light";
  return window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
}

interface ThemeProviderProps {
  children: ReactNode;
  defaultTheme?: Theme;
}

export function ThemeProvider({ children, defaultTheme = "system" }: ThemeProviderProps) {
  const pageProps = usePage().props as { auth?: { user?: { id: number } | null; theme?: Theme } };
  const isAuthenticated = !!pageProps.auth?.user;
  const serverTheme = pageProps.auth?.theme;

  // Initialize from server (for logged-in users) or localStorage (for guests)
  const [theme, setThemeState] = useState<Theme>(() => {
    if (serverTheme) return serverTheme;
    if (typeof window !== "undefined") {
      const stored = localStorage.getItem("theme") as Theme | null;
      if (stored && ["light", "dark", "system"].includes(stored)) {
        return stored;
      }
    }
    return defaultTheme;
  });

  const [resolvedTheme, setResolvedTheme] = useState<"light" | "dark">(() => {
    if (theme === "system") return getSystemTheme();
    return theme;
  });

  // Update resolved theme when theme changes or system preference changes
  useEffect(() => {
    const updateResolvedTheme = () => {
      const resolved = theme === "system" ? getSystemTheme() : theme;
      setResolvedTheme(resolved);

      // Apply to document
      const root = document.documentElement;
      root.classList.remove("light", "dark");
      root.classList.add(resolved);

      // Update theme-color meta tag for browser chrome
      const computedStyle = getComputedStyle(document.documentElement);
      const themeColor = resolved === "dark"
        ? computedStyle.getPropertyValue('--background').trim() || "#0f1318"
        : computedStyle.getPropertyValue('--primary').trim() || "#1D4ED8";
      document.querySelector('meta[name="theme-color"]')?.setAttribute("content", themeColor);
    };

    updateResolvedTheme();

    // Listen for system theme changes
    const mediaQuery = window.matchMedia("(prefers-color-scheme: dark)");
    const handleChange = () => {
      if (theme === "system") {
        updateResolvedTheme();
      }
    };

    mediaQuery.addEventListener("change", handleChange);
    return () => mediaQuery.removeEventListener("change", handleChange);
  }, [theme]);

  const setTheme = async (newTheme: Theme) => {
    setThemeState(newTheme);

    // Persist to localStorage for guests
    localStorage.setItem("theme", newTheme);

    // Persist to server for logged-in users (when user_settings feature is enabled)
    if (isAuthenticated) {
      try {
        await axios.post("/api/settings", { key: "theme", value: newTheme });
      } catch (error: unknown) {
        // Silent fail - theme will still be saved to localStorage
        console.error("Failed to save theme to server:", error);
      }
    }
  };

  return (
    <ThemeContext.Provider value={{ theme, setTheme, resolvedTheme }}>
      {children}
    </ThemeContext.Provider>
  );
}
