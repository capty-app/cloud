import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    ChevronLeft,
    ChevronRight,
    ExternalLink,
    PlayCircle,
} from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

type Item = {
    id: number;
    short_code: string;
    kind: 'image' | 'video';
    mime: string;
    thumb_url: string | null;
    viewer_url: string;
    original_name: string | null;
};

type Gallery = {
    id: number;
    name: string;
    slug: string;
};

type Totals = {
    all_time: number;
    last_7d: number;
};

type TimelinePoint = {
    date: string;
    count: number;
};

type CountryRow = {
    country_code: string | null;
    country_name: string | null;
    count: number;
};

type UaRow = {
    label: string;
    count: number;
};

type RecentView = {
    id: number;
    created_at: string | null;
    country_code: string | null;
    country_name: string | null;
    city: string | null;
    region: string | null;
    user_agent_summary: string;
    user_agent: string | null;
    referer: string | null;
};

type RecentViewsPage = {
    data: RecentView[];
    prev_page_url: string | null;
    next_page_url: string | null;
    current_page: number;
    from: number | null;
    to: number | null;
};

export default function ItemStats({
    item,
    gallery,
    totals,
    timeline,
    top_countries,
    top_user_agents,
    recent_views,
}: {
    item: Item;
    gallery: Gallery;
    totals: Totals;
    timeline: TimelinePoint[];
    top_countries: CountryRow[];
    top_user_agents: UaRow[];
    recent_views: RecentViewsPage;
}) {
    const maxTimeline = Math.max(1, ...timeline.map((t) => t.count));

    return (
        <>
            <Head title={`Stats · ${item.short_code}`} />

            <div className="space-y-6">
                <div className="flex flex-wrap items-start justify-between gap-3">
                    <div className="flex min-w-0 items-center gap-3">
                        <Button asChild variant="ghost" size="icon">
                            <Link href={`/admin/galleries/${gallery.id}`}>
                                <ArrowLeft className="size-4" />
                            </Link>
                        </Button>
                        <div className="size-14 shrink-0 overflow-hidden rounded-md border bg-muted">
                            {item.kind === 'image' && item.thumb_url ? (
                                <img
                                    src={item.thumb_url}
                                    alt={item.original_name ?? ''}
                                    className="h-full w-full object-cover"
                                />
                            ) : (
                                <div className="flex h-full w-full items-center justify-center text-muted-foreground">
                                    <PlayCircle className="size-6" />
                                </div>
                            )}
                        </div>
                        <div className="min-w-0">
                            <h1 className="truncate text-2xl font-semibold tracking-tight">
                                {item.original_name ?? item.short_code}
                            </h1>
                            <div className="mt-1 flex items-center gap-2 text-sm text-muted-foreground">
                                <code className="rounded bg-muted px-1.5 py-0.5 text-xs">
                                    /s/{item.short_code}
                                </code>
                                <span>·</span>
                                <Link
                                    href={`/admin/galleries/${gallery.id}`}
                                    className="hover:underline"
                                >
                                    {gallery.name}
                                </Link>
                            </div>
                        </div>
                    </div>
                    <Button asChild variant="outline">
                        <a
                            href={item.viewer_url}
                            target="_blank"
                            rel="noreferrer"
                        >
                            <ExternalLink className="size-4" />
                            Open link
                        </a>
                    </Button>
                </div>

                <div className="grid grid-cols-2 gap-4 md:grid-cols-4">
                    <StatCard label="Total views" value={totals.all_time} />
                    <StatCard label="Views (last 7d)" value={totals.last_7d} />
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Views over the last 30 days</CardTitle>
                        <CardDescription>
                            Daily unique view-record counts. Admin views are not
                            tracked.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="flex h-40 items-end gap-1">
                            {timeline.map((t) => {
                                const h = Math.max(
                                    2,
                                    Math.round((t.count / maxTimeline) * 100),
                                );

                                return (
                                    <div
                                        key={t.date}
                                        className="group relative flex-1"
                                        title={`${t.date}: ${t.count}`}
                                    >
                                        <div
                                            className="w-full rounded-sm bg-primary/70 transition group-hover:bg-primary"
                                            style={{ height: `${h}%` }}
                                        />
                                    </div>
                                );
                            })}
                        </div>
                        <div className="mt-2 flex justify-between text-xs text-muted-foreground">
                            <span>{timeline[0]?.date}</span>
                            <span>{timeline[timeline.length - 1]?.date}</span>
                        </div>
                    </CardContent>
                </Card>

                <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Top countries</CardTitle>
                            <CardDescription>
                                Where viewers came from (resolved via IP).
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            {top_countries.length === 0 ? (
                                <p className="py-6 text-center text-sm text-muted-foreground">
                                    No geo data yet.
                                </p>
                            ) : (
                                <ul className="divide-y">
                                    {top_countries.map((c) => (
                                        <li
                                            key={c.country_code ?? 'unknown'}
                                            className="flex items-center justify-between py-2 text-sm first:pt-0 last:pb-0"
                                        >
                                            <span className="truncate">
                                                {c.country_name ??
                                                    c.country_code}
                                            </span>
                                            <Badge variant="secondary">
                                                {c.count}
                                            </Badge>
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Top user agents</CardTitle>
                            <CardDescription>
                                Browser and OS combinations.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            {top_user_agents.length === 0 ? (
                                <p className="py-6 text-center text-sm text-muted-foreground">
                                    No views yet.
                                </p>
                            ) : (
                                <ul className="divide-y">
                                    {top_user_agents.map((u) => (
                                        <li
                                            key={u.label}
                                            className="flex items-center justify-between py-2 text-sm first:pt-0 last:pb-0"
                                        >
                                            <span className="truncate">
                                                {u.label}
                                            </span>
                                            <Badge variant="secondary">
                                                {u.count}
                                            </Badge>
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Recent views</CardTitle>
                        <CardDescription>
                            All recorded views, newest first.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {recent_views.data.length === 0 ? (
                            <p className="py-6 text-center text-sm text-muted-foreground">
                                No views yet.
                            </p>
                        ) : (
                            <>
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>When</TableHead>
                                            <TableHead>Location</TableHead>
                                            <TableHead>Client</TableHead>
                                            <TableHead>Referer</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {recent_views.data.map((v) => (
                                            <TableRow key={v.id}>
                                                <TableCell className="text-xs">
                                                    {formatDate(v.created_at)}
                                                </TableCell>
                                                <TableCell className="text-xs">
                                                    {formatLocation(v)}
                                                </TableCell>
                                                <TableCell
                                                    className="max-w-[260px] truncate text-xs"
                                                    title={v.user_agent ?? ''}
                                                >
                                                    {v.user_agent_summary}
                                                </TableCell>
                                                <TableCell
                                                    className="max-w-[260px] truncate text-xs"
                                                    title={v.referer ?? ''}
                                                >
                                                    {v.referer ?? '—'}
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                                <div className="mt-4 flex items-center justify-between text-sm text-muted-foreground">
                                    <span>
                                        {recent_views.from ?? 0}–
                                        {recent_views.to ?? 0} · page{' '}
                                        {recent_views.current_page}
                                    </span>
                                    <div className="flex gap-2">
                                        <Button
                                            asChild={
                                                !!recent_views.prev_page_url
                                            }
                                            variant="outline"
                                            size="sm"
                                            disabled={
                                                !recent_views.prev_page_url
                                            }
                                        >
                                            {recent_views.prev_page_url ? (
                                                <Link
                                                    href={
                                                        recent_views.prev_page_url
                                                    }
                                                    preserveScroll
                                                    only={['recent_views']}
                                                >
                                                    <ChevronLeft className="size-4" />
                                                    Previous
                                                </Link>
                                            ) : (
                                                <span>
                                                    <ChevronLeft className="size-4" />
                                                    Previous
                                                </span>
                                            )}
                                        </Button>
                                        <Button
                                            asChild={
                                                !!recent_views.next_page_url
                                            }
                                            variant="outline"
                                            size="sm"
                                            disabled={
                                                !recent_views.next_page_url
                                            }
                                        >
                                            {recent_views.next_page_url ? (
                                                <Link
                                                    href={
                                                        recent_views.next_page_url
                                                    }
                                                    preserveScroll
                                                    only={['recent_views']}
                                                >
                                                    Next
                                                    <ChevronRight className="size-4" />
                                                </Link>
                                            ) : (
                                                <span>
                                                    Next
                                                    <ChevronRight className="size-4" />
                                                </span>
                                            )}
                                        </Button>
                                    </div>
                                </div>
                            </>
                        )}
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

function StatCard({ label, value }: { label: string; value: number }) {
    return (
        <Card>
            <CardContent className="pt-6">
                <div className="text-sm text-muted-foreground">{label}</div>
                <div className="mt-1 text-3xl font-bold tracking-tight">
                    {value}
                </div>
            </CardContent>
        </Card>
    );
}

function formatDate(iso: string | null): string {
    if (!iso) {
        return '—';
    }

    try {
        return new Date(iso).toLocaleString();
    } catch {
        return iso;
    }
}

function formatLocation(v: RecentView): string {
    const parts = [v.city, v.region, v.country_name ?? v.country_code].filter(
        Boolean,
    );

    return parts.length ? parts.join(', ') : '—';
}
