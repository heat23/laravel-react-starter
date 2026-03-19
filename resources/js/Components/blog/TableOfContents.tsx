import { useEffect, useRef, useState } from 'react';

export interface TocSection {
    id: string;
    title: string;
    level: 2 | 3;
}

interface TableOfContentsProps {
    sections: TocSection[];
}

export function TableOfContents({ sections }: TableOfContentsProps) {
    const [activeId, setActiveId] = useState<string>('');
    const observerRef = useRef<IntersectionObserver | null>(null);

    useEffect(() => {
        observerRef.current = new IntersectionObserver(
            (entries) => {
                // Find the first visible entry
                const visible = entries.find((e) => e.isIntersecting);
                if (visible?.target.id) {
                    setActiveId(visible.target.id);
                }
            },
            { rootMargin: '-80px 0px -60% 0px', threshold: 0.1 },
        );

        const elements = sections
            .map((s) => document.getElementById(s.id))
            .filter(Boolean) as HTMLElement[];

        elements.forEach((el) => observerRef.current?.observe(el));

        return () => observerRef.current?.disconnect();
    }, [sections]);

    const list = (
        <ul className="space-y-1 text-sm">
            {sections.map((section) => (
                <li key={section.id} className={section.level === 3 ? 'ml-4' : ''}>
                    <a
                        href={`#${section.id}`}
                        className={`block rounded-md px-3 py-1.5 transition-colors ${
                            activeId === section.id
                                ? 'bg-primary/10 font-medium text-primary'
                                : 'text-muted-foreground hover:text-foreground'
                        }`}
                    >
                        {section.title}
                    </a>
                </li>
            ))}
        </ul>
    );

    return (
        <>
            {/* Desktop: sticky sidebar */}
            <nav
                aria-label="Table of contents"
                className="hidden lg:block"
            >
                <div className="sticky top-24">
                    <p className="mb-3 px-3 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                        On this page
                    </p>
                    {list}
                </div>
            </nav>

            {/* Mobile: collapsible details */}
            <nav aria-label="Table of contents" className="mb-8 lg:hidden">
                <details className="rounded-lg border border-border bg-card p-4">
                    <summary className="cursor-pointer text-sm font-semibold">
                        Table of Contents
                    </summary>
                    <div className="mt-3">{list}</div>
                </details>
            </nav>
        </>
    );
}
