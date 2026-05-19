import { Head, Link } from '@inertiajs/react';
import { Eye, ImageIcon, PlayCircle } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

type Stats = {
    galleries: number;
    items: number;
    users: number;
    views_total: number;
    views_last_7d: number;
};
type RecentGallery = {
    id: number;
    name: string;
    slug: string;
    visibility: 'public' | 'private';
    created_at: string;
};
type RecentItem = {
    id: number;
    short_code: string;
    kind: 'image' | 'video';
    thumb_url: string | null;
    viewer_url: string;
    gallery_name?: string;
    gallery_slug?: string;
};

type TopViewedItem = {
    id: number;
    short_code: string;
    kind: 'image' | 'video';
    thumb_url: string | null;
    stats_url: string;
    gallery_name?: string;
    gallery_id?: number;
    views_count: number;
};

export default function AdminDashboard({
    stats,
    recentGalleries,
    recentItems,
    topViewedItems,
}: {
    stats: Stats;
    recentGalleries: RecentGallery[];
    recentItems: RecentItem[];
    topViewedItems: TopViewedItem[];
}) {
    return (
        <>
            <Head title="Overview" />
            <div className="space-y-6">
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">
                        Overview
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        Stats and recent activity for your installation.
                    </p>
                </div>

                <div className="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-5">
                    {[
                        { label: 'Galleries', value: stats.galleries },
                        { label: 'Uploaded items', value: stats.items },
                        { label: 'Users', value: stats.users },
                        { label: 'Total views', value: stats.views_total },
                        {
                            label: 'Views (last 7d)',
                            value: stats.views_last_7d,
                        },
                    ].map((s) => (
                        <Card key={s.label}>
                            <CardContent className="pt-6">
                                <div className="text-sm text-muted-foreground">
                                    {s.label}
                                </div>
                                <div className="mt-1 text-3xl font-bold tracking-tight">
                                    {s.value}
                                </div>
                            </CardContent>
                        </Card>
                    ))}
                </div>

                <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0">
                            <div>
                                <CardTitle>Recent galleries</CardTitle>
                                <CardDescription>
                                    The latest galleries you created.
                                </CardDescription>
                            </div>
                            <Button asChild variant="outline" size="sm">
                                <Link href="/admin/galleries">View all</Link>
                            </Button>
                        </CardHeader>
                        <CardContent>
                            {recentGalleries.length === 0 ? (
                                <p className="py-6 text-center text-sm text-muted-foreground">
                                    No galleries yet.{' '}
                                    <Link
                                        href="/admin/galleries/create"
                                        className="underline"
                                    >
                                        Create one
                                    </Link>
                                </p>
                            ) : (
                                <ul className="divide-y">
                                    {recentGalleries.map((g) => (
                                        <li
                                            key={g.id}
                                            className="flex items-center justify-between py-3 first:pt-0 last:pb-0"
                                        >
                                            <div className="min-w-0">
                                                <Link
                                                    href={`/admin/galleries/${g.id}`}
                                                    className="block truncate font-medium hover:underline"
                                                >
                                                    {g.name}
                                                </Link>
                                                <div className="truncate text-xs text-muted-foreground">
                                                    /g/{g.slug}
                                                </div>
                                            </div>
                                            <Badge
                                                variant="secondary"
                                                className="capitalize"
                                            >
                                                {g.visibility}
                                            </Badge>
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Most viewed items</CardTitle>
                            <CardDescription>
                                Top items by recorded views (admin views
                                excluded).
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            {topViewedItems.length === 0 ? (
                                <p className="py-6 text-center text-sm text-muted-foreground">
                                    No views recorded yet.
                                </p>
                            ) : (
                                <ul className="divide-y">
                                    {topViewedItems.map((i) => (
                                        <li
                                            key={i.id}
                                            className="flex items-center gap-3 py-2 first:pt-0 last:pb-0"
                                        >
                                            <Link
                                                href={i.stats_url}
                                                className="size-10 shrink-0 overflow-hidden rounded-md border bg-muted"
                                            >
                                                {i.kind === 'image' &&
                                                i.thumb_url ? (
                                                    <img
                                                        src={i.thumb_url}
                                                        alt=""
                                                        className="h-full w-full object-cover"
                                                    />
                                                ) : (
                                                    <div className="flex h-full w-full items-center justify-center text-muted-foreground">
                                                        {i.kind === 'video' ? (
                                                            <PlayCircle className="size-5" />
                                                        ) : (
                                                            <ImageIcon className="size-5" />
                                                        )}
                                                    </div>
                                                )}
                                            </Link>
                                            <div className="min-w-0 flex-1">
                                                <Link
                                                    href={i.stats_url}
                                                    className="block truncate text-sm font-medium hover:underline"
                                                >
                                                    {i.short_code}
                                                </Link>
                                                <div className="truncate text-xs text-muted-foreground">
                                                    {i.gallery_name ?? '—'}
                                                </div>
                                            </div>
                                            <Link
                                                href={i.stats_url}
                                                className="flex shrink-0 items-center gap-1 text-sm text-muted-foreground hover:underline"
                                            >
                                                <Eye className="size-3.5" />
                                                {i.views_count}
                                            </Link>
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Recent uploads</CardTitle>
                        <CardDescription>
                            The last few items added to any gallery.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {recentItems.length === 0 ? (
                            <p className="py-6 text-center text-sm text-muted-foreground">
                                No uploads yet.
                            </p>
                        ) : (
                            <div className="grid grid-cols-4 gap-3 sm:grid-cols-6 lg:grid-cols-8">
                                {recentItems.map((i) => (
                                    <Link
                                        key={i.id}
                                        href={i.viewer_url}
                                        target="_blank"
                                        className="block aspect-square overflow-hidden rounded-md border bg-muted"
                                    >
                                        {i.kind === 'image' && i.thumb_url ? (
                                            <img
                                                src={i.thumb_url}
                                                alt=""
                                                className="h-full w-full object-cover"
                                            />
                                        ) : (
                                            <div className="flex h-full w-full items-center justify-center text-muted-foreground">
                                                {i.kind === 'video' ? (
                                                    <PlayCircle className="size-8" />
                                                ) : (
                                                    <ImageIcon className="size-8" />
                                                )}
                                            </div>
                                        )}
                                    </Link>
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </>
    );
}
