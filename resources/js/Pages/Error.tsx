import { useEffect, useRef } from "react";

import { Head } from "@inertiajs/react";

import { Button } from "@/Components/ui/button";

const statusMessages: Record<number, { title: string; description: string }> = {
    403: {
        title: "Forbidden",
        description: "You don't have permission to access this resource.",
    },
    404: {
        title: "Page Not Found",
        description: "The page you're looking for doesn't exist or has been moved.",
    },
    419: {
        title: "Page Expired",
        description: "Your session has expired. Please refresh the page and try again.",
    },
    429: {
        title: "Too Many Requests",
        description: "You've made too many requests. Please wait a moment and try again.",
    },
    500: {
        title: "Server Error",
        description: "Something went wrong on our end. Please try again later.",
    },
    503: {
        title: "Service Unavailable",
        description: "We're currently performing maintenance. Please check back shortly.",
    },
};

export default function Error({ status }: { status: number }) {
    const { title, description } = statusMessages[status] ?? {
        title: "Error",
        description: "An unexpected error occurred.",
    };

    const headingRef = useRef<HTMLHeadingElement>(null);

    useEffect(() => {
        headingRef.current?.focus();
    }, []);

    return (
        <>
            <Head title={`${status} - ${title}`} />
            <div className="flex min-h-screen items-center justify-center bg-background px-4">
                <div className="text-center">
                    <p className="text-7xl font-bold text-muted-foreground">{status}</p>
                    <h1
                        ref={headingRef}
                        tabIndex={-1}
                        className="mt-4 text-2xl font-semibold text-foreground outline-none"
                    >
                        {title}
                    </h1>
                    <p className="mt-2 text-muted-foreground">{description}</p>
                    <div className="mt-8 flex items-center justify-center gap-4">
                        <Button variant="outline" onClick={() => window.history.back()}>
                            Go Back
                        </Button>
                        <Button asChild>
                            <a href="/">Go Home</a>
                        </Button>
                    </div>
                </div>
            </div>
        </>
    );
}
