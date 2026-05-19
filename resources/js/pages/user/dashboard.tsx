import { Head, Link } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { ViewerHeader } from '@/components/viewer-header';

type Gallery = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    visibility: 'public' | 'private';
    items_count: number;
    url: string;
};

export default function UserDashboard({ galleries }: { galleries: Gallery[] }) {
    return (
        <>
            <Head title="Galleries" />

            <div className="flex min-h-svh flex-col bg-background">
                <ViewerHeader />
                <main className="mx-auto w-full max-w-5xl flex-1 px-6 py-6">
                    <div className="mb-6">
                        <h1 className="text-2xl font-semibold tracking-tight">
                            Galleries
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            All galleries you have access to.
                        </p>
                    </div>

                    {galleries.length === 0 ? (
                        <div className="rounded-lg border border-dashed py-16 text-center text-sm text-muted-foreground">
                            No galleries available yet.
                        </div>
                    ) : (
                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            {galleries.map((g) => (
                                <Link key={g.id} href={g.url} className="group">
                                    <Card className="h-full transition-shadow group-hover:shadow-md">
                                        <CardContent className="pt-6">
                                            <div className="mb-2 flex items-center justify-between gap-2">
                                                <div className="truncate font-medium">
                                                    {g.name}
                                                </div>
                                                <Badge
                                                    variant="secondary"
                                                    className="capitalize"
                                                >
                                                    {g.visibility}
                                                </Badge>
                                            </div>
                                            <div className="text-xs text-muted-foreground">
                                                {g.items_count} item
                                                {g.items_count === 1 ? '' : 's'}
                                            </div>
                                            {g.description && (
                                                <p className="mt-3 line-clamp-2 text-sm text-muted-foreground">
                                                    {g.description}
                                                </p>
                                            )}
                                        </CardContent>
                                    </Card>
                                </Link>
                            ))}
                        </div>
                    )}
                </main>
            </div>
        </>
    );
}
