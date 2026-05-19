import { Form, Head, router, usePage } from '@inertiajs/react';
import { MoreHorizontal, Pencil, Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';
import { FormField } from '@/components/form-field';
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
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetFooter,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import { Spinner } from '@/components/ui/spinner';
import type { SharedData, UserRole } from '@/types';
import { InertiaTable } from 'inertia-table-react';
import type { Row } from 'inertia-table-react';

type EditingUser = {
    id: number;
    name: string;
    email: string;
    role: UserRole;
} | null;

export default function UsersIndex({ users }: { users: unknown }) {
    const { auth } = usePage<SharedData>().props;
    const [editing, setEditing] = useState<EditingUser>(null);
    const [creating, setCreating] = useState(false);
    const [deleting, setDeleting] = useState<{
        id: number;
        name: string;
    } | null>(null);

    return (
        <>
            <Head title="Users" />

            <div className="space-y-6">
                <div className="flex items-center justify-between gap-3">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight">
                            Users
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Manage who can sign in. Only admins can create
                            users.
                        </p>
                    </div>
                    <Button onClick={() => setCreating(true)}>
                        <Plus className="size-4" />
                        New user
                    </Button>
                </div>

                <div className="rounded-lg border bg-card p-4">
                    <InertiaTable
                        tableData={users as never}
                        classNames={tableClassNames}
                        renderSearch={(p) => <TableSearch {...p} />}
                        actions={(row: Row) => {
                            const isMe = (row.id as number) === auth.user?.id;

                            return (
                                <div className="flex items-center justify-end gap-1">
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        aria-label="Edit"
                                        onClick={() =>
                                            setEditing({
                                                id: row.id as number,
                                                name: row.name as string,
                                                email: row.email as string,
                                                role: row.role as UserRole,
                                            })
                                        }
                                    >
                                        <Pencil className="size-4" />
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
                                            <DropdownMenuItem
                                                disabled={isMe}
                                                className="text-destructive"
                                                onSelect={(e) => {
                                                    e.preventDefault();

                                                    if (isMe) {
                                                        return;
                                                    }

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
                            );
                        }}
                    />
                </div>
            </div>

            <UserSheet
                open={creating}
                onOpenChange={setCreating}
                title="Create user"
                description="Set a name, email, password, and role."
                action="/admin/users"
                method="post"
                submitLabel="Create user"
            />

            <UserSheet
                open={editing !== null}
                onOpenChange={(open) => !open && setEditing(null)}
                title={`Edit ${editing?.name ?? ''}`}
                description="Update profile details. Leave password blank to keep the current one."
                action={editing ? `/admin/users/${editing.id}` : ''}
                method="put"
                submitLabel="Save changes"
                user={editing}
            />

            <AlertDialog
                open={deleting !== null}
                onOpenChange={(open) => !open && setDeleting(null)}
            >
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Delete user?</AlertDialogTitle>
                        <AlertDialogDescription>
                            This permanently removes{' '}
                            <span className="font-medium">
                                {deleting?.name}
                            </span>{' '}
                            from the system.
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel>Cancel</AlertDialogCancel>
                        <AlertDialogAction
                            className="bg-destructive text-destructive-foreground hover:bg-destructive/90"
                            onClick={() =>
                                deleting &&
                                router.delete(`/admin/users/${deleting.id}`)
                            }
                        >
                            Delete user
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </>
    );
}

function UserSheet({
    open,
    onOpenChange,
    title,
    description,
    action,
    method,
    submitLabel,
    user,
}: {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    title: string;
    description: string;
    action: string;
    method: 'post' | 'put';
    submitLabel: string;
    user?: EditingUser;
}) {
    const [role, setRole] = useState<UserRole>(user?.role ?? 'user');

    return (
        <Sheet open={open} onOpenChange={onOpenChange}>
            <SheetContent className="w-full sm:max-w-md">
                <SheetHeader>
                    <SheetTitle>{title}</SheetTitle>
                    <SheetDescription>{description}</SheetDescription>
                </SheetHeader>

                {action && (
                    <Form
                        action={action}
                        method={method}
                        className="flex h-full flex-col px-4"
                        onSuccess={() => onOpenChange(false)}
                    >
                        {({ processing, errors }) => (
                            <>
                                <div className="flex-1 space-y-6 py-2">
                                    <FormField
                                        label="Name"
                                        htmlFor="name"
                                        error={errors.name}
                                    >
                                        <Input
                                            id="name"
                                            name="name"
                                            required
                                            defaultValue={user?.name ?? ''}
                                        />
                                    </FormField>
                                    <FormField
                                        label="Email"
                                        htmlFor="email"
                                        error={errors.email}
                                    >
                                        <Input
                                            id="email"
                                            name="email"
                                            type="email"
                                            required
                                            autoComplete="off"
                                            defaultValue={user?.email ?? ''}
                                        />
                                    </FormField>
                                    <FormField
                                        label="Password"
                                        htmlFor="password"
                                        hint={
                                            user
                                                ? '(leave blank to keep current)'
                                                : undefined
                                        }
                                        error={errors.password}
                                    >
                                        <Input
                                            id="password"
                                            name="password"
                                            type="password"
                                            autoComplete="new-password"
                                            minLength={8}
                                            required={!user}
                                        />
                                    </FormField>
                                    <FormField label="Role" error={errors.role}>
                                        <input
                                            type="hidden"
                                            name="role"
                                            value={role}
                                        />
                                        <Select
                                            value={role}
                                            onValueChange={(v) =>
                                                setRole(v as UserRole)
                                            }
                                        >
                                            <SelectTrigger>
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="user">
                                                    User — viewer + comment
                                                </SelectItem>
                                                <SelectItem value="admin">
                                                    Admin — full access
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </FormField>
                                </div>
                                <SheetFooter className="border-t pt-4">
                                    <Button
                                        type="button"
                                        variant="outline"
                                        onClick={() => onOpenChange(false)}
                                    >
                                        Cancel
                                    </Button>
                                    <Button type="submit" disabled={processing}>
                                        {processing && <Spinner />}
                                        {submitLabel}
                                    </Button>
                                </SheetFooter>
                            </>
                        )}
                    </Form>
                )}
            </SheetContent>
        </Sheet>
    );
}
