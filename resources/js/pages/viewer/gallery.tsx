import { Head, Link } from '@inertiajs/react';
import {
    ChevronLeft,
    ChevronRight,
    ExternalLink,
    PlayCircle,
    X,
} from 'lucide-react';
import { useCallback, useEffect, useState } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogTitle } from '@/components/ui/dialog';
import { ViewerHeader } from '@/components/viewer-header';

type Item = {
    id: number;
    short_code: string;
    kind: 'image' | 'video';
    file_url: string;
    thumb_url: string | null;
    viewer_url: string;
    original_name: string | null;
    mime: string;
    size: number;
};

type Gallery = {
    name: string;
    slug: string;
    description: string | null;
    visibility: 'public' | 'private';
    comments_enabled: boolean;
};

export default function GalleryView({
    gallery,
    items,
}: {
    gallery: Gallery;
    items: Item[];
}) {
    const [active, setActive] = useState<number | null>(null);
    const current = active === null ? null : items[active];

    const close = useCallback(() => setActive(null), []);
    const prev = useCallback(
        () => setActive((i) => (i !== null && i > 0 ? i - 1 : i)),
        [],
    );
    const next = useCallback(
        () =>
            setActive((i) => (i !== null && i < items.length - 1 ? i + 1 : i)),
        [items.length],
    );

    useEffect(() => {
        if (active === null) {
            return;
        }

        const handler = (e: KeyboardEvent) => {
            if (e.key === 'ArrowLeft') {
                prev();
            } else if (e.key === 'ArrowRight') {
                next();
            }
        };
        window.addEventListener('keydown', handler);

        return () => window.removeEventListener('keydown', handler);
    }, [active, prev, next]);

    return (
        <>
            <Head title={gallery.name} />

            <div className="flex min-h-svh flex-col bg-background">
                <ViewerHeader />

                <main className="mx-auto w-full max-w-6xl flex-1 px-6 py-6">
                    <div className="mb-6 flex items-start justify-between gap-3">
                        <div className="min-w-0">
                            <h1 className="text-2xl font-semibold tracking-tight">
                                {gallery.name}
                            </h1>
                            {gallery.description && (
                                <p className="mt-1 text-sm text-muted-foreground">
                                    {gallery.description}
                                </p>
                            )}
                        </div>
                        <Badge variant="secondary" className="capitalize">
                            {gallery.visibility}
                        </Badge>
                    </div>

                    {items.length === 0 ? (
                        <div className="rounded-lg border border-dashed py-16 text-center text-sm text-muted-foreground">
                            No items yet.
                        </div>
                    ) : (
                        <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
                            {items.map((item, i) => (
                                <button
                                    key={item.id}
                                    type="button"
                                    onClick={() => setActive(i)}
                                    className="group relative block aspect-square overflow-hidden rounded-md border bg-muted"
                                >
                                    {item.kind === 'image' && item.thumb_url ? (
                                        <img
                                            src={item.thumb_url}
                                            alt={item.original_name ?? ''}
                                            loading="lazy"
                                            className="h-full w-full object-cover transition-transform group-hover:scale-105"
                                        />
                                    ) : (
                                        <div className="flex h-full w-full items-center justify-center text-muted-foreground">
                                            <PlayCircle className="size-10" />
                                        </div>
                                    )}
                                </button>
                            ))}
                        </div>
                    )}
                </main>
            </div>

            <Dialog
                open={current !== null}
                onOpenChange={(open) => !open && close()}
            >
                <DialogContent
                    className="grid h-[90vh] max-w-5xl gap-0 overflow-hidden p-0 sm:max-w-5xl"
                    showCloseButton={false}
                >
                    <DialogTitle className="sr-only">
                        {current?.original_name ?? 'Item'}
                    </DialogTitle>
                    <div className="relative flex flex-1 items-center justify-center bg-black">
                        {current?.kind === 'image' && (
                            <img
                                src={current.file_url}
                                alt={current.original_name ?? ''}
                                className="max-h-full max-w-full object-contain"
                            />
                        )}
                        {current?.kind === 'video' && (
                            <video
                                src={current.file_url}
                                controls
                                autoPlay
                                className="max-h-full max-w-full"
                            />
                        )}

                        <Button
                            variant="ghost"
                            size="icon"
                            className="absolute top-3 right-3 size-9 rounded-full bg-black/50 text-white hover:bg-black/70"
                            onClick={close}
                        >
                            <X className="size-5" />
                        </Button>

                        {active !== null && active > 0 && (
                            <Button
                                variant="ghost"
                                size="icon"
                                className="absolute top-1/2 left-3 size-10 -translate-y-1/2 rounded-full bg-black/50 text-white hover:bg-black/70"
                                onClick={prev}
                            >
                                <ChevronLeft className="size-6" />
                            </Button>
                        )}
                        {active !== null && active < items.length - 1 && (
                            <Button
                                variant="ghost"
                                size="icon"
                                className="absolute top-1/2 right-3 size-10 -translate-y-1/2 rounded-full bg-black/50 text-white hover:bg-black/70"
                                onClick={next}
                            >
                                <ChevronRight className="size-6" />
                            </Button>
                        )}
                    </div>
                    <div className="flex items-center justify-between border-t bg-card px-4 py-3 text-sm">
                        <div className="min-w-0">
                            <div className="truncate font-medium">
                                {current?.original_name ?? current?.short_code}
                            </div>
                            <div className="text-xs text-muted-foreground">
                                {(active ?? 0) + 1} of {items.length} ·{' '}
                                {current &&
                                    Math.round((current.size / 1024) * 10) /
                                        10}{' '}
                                KB
                            </div>
                        </div>
                        {current && (
                            <Button asChild variant="outline" size="sm">
                                <Link href={current.viewer_url}>
                                    <ExternalLink className="size-3.5" />
                                    Permalink
                                </Link>
                            </Button>
                        )}
                    </div>
                </DialogContent>
            </Dialog>
        </>
    );
}
