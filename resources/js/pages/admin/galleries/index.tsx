import { Head, Link, router } from '@inertiajs/react';
import { Eye, Pencil, Plus, Trash2 } from 'lucide-react';
import { MoreHorizontal } from 'lucide-react';
import { useState } from 'react';
import { TableSearch } from '@/components/inertia-table-search';
import { tableClassNames } from '@/components/inertia-table-theme';
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
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { InertiaTable } from 'inertia-table-react';
import type { Row } from 'inertia-table-react';

export default function GalleriesIndex({ galleries }: { galleries: unknown }) {
    const [deleting, setDeleting] = useState<{
        id: number;
        name: string;
    } | null>(null);

    const handleDelete = () => {
        if (!deleting) {
            return;
        }

        router.delete(`/admin/galleries/${deleting.id}`, {
            onFinish: () => setDeleting(null),
        });
    };

    return (
        <>
            <Head title="Galleries" />
            <div className="space-y-6">
                <div className="flex items-center justify-between gap-3">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight">
                            Galleries
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Create, configure, and share galleries.
                        </p>
                    </div>
                    <Button asChild>
                        <Link href="/admin/galleries/create">
                            <Plus className="size-4" />
                            New gallery
                        </Link>
                    </Button>
                </div>

                <div className="rounded-lg border bg-card p-4">
                    <InertiaTable
                        tableData={galleries as never}
                        classNames={tableClassNames}
                        renderSearch={(p) => <TableSearch {...p} />}
                        actions={(row: Row) => (
                            <div className="flex items-center justify-end gap-1">
                                <Button
                                    asChild
                                    variant="ghost"
                                    size="icon"
                                    aria-label="View"
                                >
                                    <a
                                        href={`/g/${row.slug as string}`}
                                        target="_blank"
                                        rel="noreferrer"
                                    >
                                        <Eye className="size-4" />
                                    </a>
                                </Button>
                                <Button
                                    asChild
                                    variant="ghost"
                                    size="icon"
                                    aria-label="Edit"
                                >
                                    <Link
                                        href={`/admin/galleries/${row.id}/edit`}
                                    >
                                        <Pencil className="size-4" />
                                    </Link>
                                </Button>
                                <DropdownMenu>
                                    <DropdownMenuTrigger asChild>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            aria-label="More"
                                        >
                                            <MoreHorizontal className="size-4" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuItem asChild>
                                            <Link
                                                href={`/admin/galleries/${row.id}`}
                                            >
                                                Open admin page
                                            </Link>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            className="text-destructive"
                                            onSelect={(e) => {
                                                e.preventDefault();
                                                setDeleting({
                                                    id: row.id as number,
                                                    name: row.name as string,
                                                });
                                            }}
                                        >
                                            <Trash2 className="size-4" />
                                            Delete
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>
                        )}
                    />
                </div>
            </div>

            <AlertDialog
                open={deleting !== null}
                onOpenChange={(open) => !open && setDeleting(null)}
            >
                <AlertDialogTrigger className="hidden" />
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Delete gallery?</AlertDialogTitle>
                        <AlertDialogDescription>
                            This will permanently delete{' '}
                            <span className="font-medium">
                                {deleting?.name}
                            </span>{' '}
                            and all of its items. This cannot be undone.
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel>Cancel</AlertDialogCancel>
                        <AlertDialogAction
                            onClick={handleDelete}
                            className="bg-destructive text-destructive-foreground hover:bg-destructive/90"
                        >
                            Delete gallery
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </>
    );
}
