import { Form, Head, Link, router, usePage } from '@inertiajs/react';
import { ArrowLeft, Download, Trash2 } from 'lucide-react';
import { FormField } from '@/components/form-field';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import { ViewerHeader } from '@/components/viewer-header';
import type { SharedData } from '@/types';

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
    visibility: 'public' | 'private';
    comments_enabled: boolean;
};

type Comment = {
    id: number;
    body: string;
    created_at: string;
    user: { id: number; name: string };
};

export default function ItemView({
    gallery,
    item,
    comments,
}: {
    gallery: Gallery;
    item: Item;
    comments: Comment[];
}) {
    const { auth } = usePage<SharedData>().props;

    return (
        <>
            <Head title={item.original_name ?? item.short_code} />

            <div className="flex min-h-svh flex-col bg-background">
                <ViewerHeader />

                <main className="mx-auto w-full max-w-4xl flex-1 px-6 py-6">
                    <Button asChild variant="ghost" size="sm" className="mb-4">
                        <Link href={`/g/${gallery.slug}`}>
                            <ArrowLeft className="size-4" />
                            {gallery.name}
                        </Link>
                    </Button>

                    <Card className="overflow-hidden p-0">
                        <div className="flex min-h-[300px] items-center justify-center bg-black">
                            {item.kind === 'image' ? (
                                <img
                                    src={item.file_url}
                                    alt={item.original_name ?? ''}
                                    className="max-h-[70vh] max-w-full object-contain"
                                />
                            ) : (
                                <video
                                    src={item.file_url}
                                    controls
                                    className="max-h-[70vh] max-w-full"
                                />
                            )}
                        </div>
                        <div className="flex flex-wrap items-center justify-between gap-3 px-6 py-4">
                            <div className="min-w-0">
                                <div className="truncate font-medium">
                                    {item.original_name ?? item.short_code}
                                </div>
                                <div className="text-xs text-muted-foreground">
                                    {item.mime} ·{' '}
                                    {Math.round((item.size / 1024) * 10) / 10}{' '}
                                    KB
                                </div>
                            </div>
                            <Button asChild variant="outline">
                                <a href={item.file_url} download>
                                    <Download className="size-4" />
                                    Download
                                </a>
                            </Button>
                        </div>
                    </Card>

                    <Card className="mt-6">
                        <CardHeader>
                            <CardTitle>
                                Comments
                                <span className="ml-2 text-sm font-normal text-muted-foreground">
                                    ({comments.length})
                                </span>
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            {gallery.comments_enabled && auth.user ? (
                                <Form
                                    action={`/s/${item.short_code}/comments`}
                                    method="post"
                                    className="space-y-3"
                                    resetOnSuccess
                                >
                                    {({ processing, errors }) => (
                                        <>
                                            <FormField error={errors.body}>
                                                <Textarea
                                                    name="body"
                                                    required
                                                    rows={3}
                                                    placeholder="Write a comment..."
                                                />
                                            </FormField>
                                            <div className="flex justify-end">
                                                <Button
                                                    type="submit"
                                                    size="sm"
                                                    disabled={processing}
                                                >
                                                    {processing && <Spinner />}
                                                    Post comment
                                                </Button>
                                            </div>
                                        </>
                                    )}
                                </Form>
                            ) : !gallery.comments_enabled ? (
                                <p className="text-sm text-muted-foreground">
                                    Comments are disabled for this gallery.
                                </p>
                            ) : (
                                <p className="text-sm text-muted-foreground">
                                    <Link
                                        href="/login"
                                        className="underline underline-offset-2"
                                    >
                                        Sign in
                                    </Link>{' '}
                                    to leave a comment.
                                </p>
                            )}

                            {comments.length > 0 && (
                                <div className="space-y-3 border-t pt-4">
                                    {comments.map((c) => (
                                        <div
                                            key={c.id}
                                            className="rounded-md border bg-muted/40 p-3"
                                        >
                                            <div className="mb-1 flex items-center justify-between gap-2">
                                                <div className="text-sm font-medium">
                                                    {c.user.name}
                                                </div>
                                                <div className="flex items-center gap-2 text-xs text-muted-foreground">
                                                    <time>
                                                        {new Date(
                                                            c.created_at,
                                                        ).toLocaleString()}
                                                    </time>
                                                    {auth.user &&
                                                        (auth.user.role ===
                                                            'admin' ||
                                                            auth.user.id ===
                                                                c.user.id) && (
                                                            <Button
                                                                variant="ghost"
                                                                size="icon"
                                                                className="size-6 text-destructive"
                                                                onClick={() =>
                                                                    router.delete(
                                                                        `/comments/${c.id}`,
                                                                    )
                                                                }
                                                                aria-label="Delete comment"
                                                            >
                                                                <Trash2 className="size-3.5" />
                                                            </Button>
                                                        )}
                                                </div>
                                            </div>
                                            <p className="text-sm whitespace-pre-wrap">
                                                {c.body}
                                            </p>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </main>
            </div>
        </>
    );
}
