import { cn } from '@/lib/utils';

interface FeatureScreenshotProps {
    src: string;
    alt: string;
    caption?: string;
    className?: string;
    width?: number;
    height?: number;
}

export function FeatureScreenshot({ src, alt, caption, className, width = 1200, height = 700 }: FeatureScreenshotProps) {
    return (
        <figure className={cn('my-8', className)}>
            <div className="overflow-hidden rounded-2xl border border-border/70 shadow-2xl shadow-black/20">
                {/* Browser chrome bar */}
                <div className="flex items-center gap-1.5 bg-zinc-100 px-4 py-2.5 dark:bg-zinc-900" aria-hidden="true">
                    <div className="h-2.5 w-2.5 rounded-full bg-red-400/80 dark:bg-red-500/70" />
                    <div className="h-2.5 w-2.5 rounded-full bg-yellow-400/80 dark:bg-yellow-500/70" />
                    <div className="h-2.5 w-2.5 rounded-full bg-green-400/80 dark:bg-green-500/70" />
                    <div className="ml-3 h-4 max-w-xs flex-1 rounded bg-zinc-300/60 dark:bg-zinc-700/60" />
                </div>
                <img
                    src={src}
                    alt={alt}
                    loading="lazy"
                    width={width}
                    height={height}
                    className="w-full"
                />
            </div>
            {caption && (
                <figcaption className="mt-3 text-center text-sm text-muted-foreground">
                    {caption}
                </figcaption>
            )}
        </figure>
    );
}
