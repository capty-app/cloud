import { Head, Link, router } from '@inertiajs/react';
import {
    BarChart3,
    Copy,
    Eye,
    EyeOff,
    ExternalLink,
    Pencil,
    PlayCircle,
    RotateCw,
    Trash2,
} from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

type Gallery = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    visibility: 'public' | 'private';
    max_size_mb: number;
    allowed_mimes: string[] | null;
    comments_enabled: boolean;
    public_url: string;
    api_token: string;
    views_total: number | null;
};

type Item = {
    id: number;
    short_code: string;
    kind: 'image' | 'video';
    mime: string;
    size: number;
    thumb_url: string | null;
    file_url: string;
    viewer_url: string;
    original_name: string | null;
    created_at: string;
    views_count: number;
    stats_url: string;
};

export default function GalleryShow({
    gallery,
    items,
    upload_endpoint,
}: {
    gallery: Gallery;
    items: Item[];
    upload_endpoint: string;
}) {
    const [showToken, setShowToken] = useState(false);

    const copy = (value: string, label: string) => {
        navigator.clipboard.writeText(value);
        toast.success(`${label} copied`);
    };

    const curlExample = `curl -X POST "${upload_endpoint}" \\
  -H "Authorization: Bearer ${showToken ? gallery.api_token : '<TOKEN>'}" \\
  -F "file=@/path/to/photo.jpg"`;

    return (
        <>
            <Head title={gallery.name} />

            <div className="space-y-6">
                <div className="flex flex-wrap items-start justify-between gap-3">
                    <div className="min-w-0">
                        <div className="flex items-center gap-2">
                            <h1 className="truncate text-2xl font-semibold tracking-tight">
                                {gallery.name}
                            </h1>
                            <Badge variant="secondary" className="capitalize">
                                {gallery.visibility}
                            </Badge>
                        </div>
                        <p className="mt-1 truncate text-sm text-muted-foreground">
                            <code className="rounded bg-muted px-1.5 py-0.5 text-xs">
                                /g/{gallery.slug}
                            </code>
                        </p>
                    </div>
                    <div className="flex gap-2">
                        <Button asChild variant="outline">
                            <a
                                href={gallery.public_url}
                                target="_blank"
                                rel="noreferrer"
                            >
                                <ExternalLink className="size-4" />
                                Open
                            </a>
                        </Button>
                        <Button asChild variant="outline">
                            <Link href={`/admin/galleries/${gallery.id}/edit`}>
                                <Pencil className="size-4" />
                                Edit
                            </Link>
                        </Button>
                    </div>
                </div>

                <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Upload API</CardTitle>
                            <CardDescription>
                                Push media into this gallery from any external
                                system.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="space-y-2">
                                <div className="text-sm font-medium">
                                    Endpoint
                                </div>
                                <div className="flex items-center gap-2">
                                    <code className="flex-1 truncate rounded-md bg-muted px-3 py-2 text-xs">
                                        POST {upload_endpoint}
                                    </code>
                                    <Button
                                        size="icon"
                                        variant="outline"
                                        onClick={() =>
                                            copy(
                                                upload_endpoint,
                                                'Endpoint URL',
                                            )
                                        }
                                    >
                                        <Copy className="size-4" />
                                    </Button>
                                </div>
                            </div>

                            <div className="space-y-2">
                                <div className="flex items-center justify-between">
                                    <div className="text-sm font-medium">
                                        API token
                                    </div>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        onClick={() => setShowToken((v) => !v)}
                                    >
                                        {showToken ? (
                                            <EyeOff className="size-3.5" />
                                        ) : (
                                            <Eye className="size-3.5" />
                                        )}
                                        {showToken ? 'Hide' : 'Reveal'}
                                    </Button>
                                </div>
                                <div className="flex items-center gap-2">
                                    <code className="flex-1 truncate rounded-md bg-muted px-3 py-2 text-xs">
                                        {showToken
                                            ? gallery.api_token
                                            : '•'.repeat(24)}
                                    </code>
                                    <Button
                                        size="icon"
                                        variant="outline"
                                        onClick={() =>
                                            copy(gallery.api_token, 'API token')
                                        }
                                    >
                                        <Copy className="size-4" />
                                    </Button>
                                </div>
                            </div>

                            <div className="space-y-2">
                                <div className="text-sm font-medium">
                                    curl example
                                </div>
                                <pre className="overflow-x-auto rounded-md bg-muted px-3 py-2 text-xs leading-relaxed">
                                    {curlExample}
                                </pre>
                            </div>

                            <div className="border-t pt-4">
                                <AlertDialog>
                                    <AlertDialogTrigger asChild>
                                        <Button variant="outline" size="sm">
                                            <RotateCw className="size-3.5" />
                                            Rotate token
                                        </Button>
                                    </AlertDialogTrigger>
                                    <AlertDialogContent>
                                        <AlertDialogHeader>
                                            <AlertDialogTitle>
                                                Rotate API token?
                                            </AlertDialogTitle>
                                            <AlertDialogDescription>
                                                Any existing integration using
                                                the current token will stop
                                                working until you update it.
                                            </AlertDialogDescription>
                                        </AlertDialogHeader>
                                        <AlertDialogFooter>
                                            <AlertDialogCancel>
                                                Cancel
                                            </AlertDialogCancel>
                                            <AlertDialogAction
                                                onClick={() =>
                                                    router.post(
                                                        `/admin/galleries/${gallery.id}/rotate-token`,
                                                    )
                                                }
                                            >
                                                Rotate
                                            </AlertDialogAction>
                                        </AlertDialogFooter>
                                    </AlertDialogContent>
                                </AlertDialog>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Configuration</CardTitle>
                            <CardDescription>
                                Current limits and behavior for this gallery.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <dl className="space-y-3 text-sm">
                                <div className="flex items-center justify-between gap-3">
                                    <dt className="text-muted-foreground">
                                        Max upload size
                                    </dt>
                                    <dd>{gallery.max_size_mb} MB</dd>
                                </div>
                                <div className="flex items-center justify-between gap-3">
                                    <dt className="text-muted-foreground">
                                        Allowed types
                                    </dt>
                                    <dd className="text-right">
                                        {gallery.allowed_mimes &&
                                        gallery.allowed_mimes.length
                                            ? gallery.allowed_mimes.join(', ')
                                            : 'images + videos'}
                                    </dd>
                                </div>
                                <div className="flex items-center justify-between gap-3">
                                    <dt className="text-muted-foreground">
                                        Comments
                                    </dt>
                                    <dd>
                                        {gallery.comments_enabled
                                            ? 'Enabled'
                                            : 'Disabled'}
                                    </dd>
                                </div>
                                <div className="flex items-center justify-between gap-3">
                                    <dt className="text-muted-foreground">
                                        Items
                                    </dt>
                                    <dd>{items.length}</dd>
                                </div>
                                <div className="flex items-center justify-between gap-3">
                                    <dt className="text-muted-foreground">
                                        Total views
                                    </dt>
                                    <dd>{gallery.views_total ?? 0}</dd>
                                </div>
                            </dl>
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Items</CardTitle>
                        <CardDescription>
                            Uploaded media in this gallery.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {items.length === 0 ? (
                            <p className="py-8 text-center text-sm text-muted-foreground">
                                No uploads yet. Use the upload API to add items.
                            </p>
                        ) : (
                            <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
                                {items.map((item) => (
                                    <ItemTile key={item.id} item={item} />
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

function ItemTile({ item }: { item: Item }) {
    const [deleting, setDeleting] = useState(false);

    return (
        <div className="group relative">
            <a
                href={item.viewer_url}
                target="_blank"
                rel="noreferrer"
                className="block aspect-square overflow-hidden rounded-md border bg-muted"
            >
                {item.kind === 'image' && item.thumb_url ? (
                    <img
                        src={item.thumb_url}
                        alt={item.original_name ?? ''}
                        className="h-full w-full object-cover transition-transform group-hover:scale-105"
                    />
                ) : (
                    <div className="flex h-full w-full items-center justify-center text-muted-foreground">
                        <PlayCircle className="size-8" />
                    </div>
                )}
            </a>
            <div className="absolute top-2 right-2 flex gap-1 opacity-0 transition-opacity group-hover:opacity-100">
                <Button
                    asChild
                    variant="secondary"
                    size="icon"
                    className="size-7"
                    aria-label="Stats"
                >
                    <Link href={item.stats_url}>
                        <BarChart3 className="size-3.5" />
                    </Link>
                </Button>
                <AlertDialog open={deleting} onOpenChange={setDeleting}>
                    <AlertDialogTrigger asChild>
                        <Button
                            variant="destructive"
                            size="icon"
                            className="size-7"
                            aria-label="Delete"
                        >
                            <Trash2 className="size-3.5" />
                        </Button>
                    </AlertDialogTrigger>
                    <AlertDialogContent>
                        <AlertDialogHeader>
                            <AlertDialogTitle>Delete item?</AlertDialogTitle>
                            <AlertDialogDescription>
                                This permanently deletes the file from storage.
                            </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter>
                            <AlertDialogCancel>Cancel</AlertDialogCancel>
                            <AlertDialogAction
                                onClick={() =>
                                    router.delete(`/admin/items/${item.id}`)
                                }
                                className="bg-destructive text-destructive-foreground hover:bg-destructive/90"
                            >
                                Delete
                            </AlertDialogAction>
                        </AlertDialogFooter>
                    </AlertDialogContent>
                </AlertDialog>
            </div>
            <div className="mt-1.5 flex items-center justify-between gap-2 text-xs text-muted-foreground">
                <code className="truncate">{item.short_code}</code>
                <Link
                    href={item.stats_url}
                    className="flex shrink-0 items-center gap-1 hover:underline"
                >
                    <Eye className="size-3" />
                    {item.views_count}
                </Link>
            </div>
        </div>
    );
}
