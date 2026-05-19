import { Form, Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import { FormField } from '@/components/form-field';
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
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Textarea } from '@/components/ui/textarea';

type Gallery = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    visibility: 'public' | 'private';
    max_size_mb: number;
    allowed_mimes_raw: string;
    comments_enabled: boolean;
};

export default function GalleryEdit({ gallery }: { gallery: Gallery | null }) {
    const isEdit = !!gallery;
    const action = isEdit
        ? `/admin/galleries/${gallery.id}`
        : '/admin/galleries';
    const method = isEdit ? 'put' : 'post';

    const [visibility, setVisibility] = useState<Gallery['visibility']>(
        gallery?.visibility ?? 'private',
    );

    return (
        <>
            <Head title={isEdit ? `Edit ${gallery.name}` : 'New gallery'} />

            <div className="mx-auto max-w-3xl space-y-6">
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">
                        {isEdit ? `Edit "${gallery.name}"` : 'New gallery'}
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        Galleries hold uploaded items behind their own API token
                        and visibility setting.
                    </p>
                </div>

                <Tabs defaultValue="settings">
                    <TabsList>
                        <TabsTrigger value="settings">Settings</TabsTrigger>
                        {isEdit && (
                            <TabsTrigger value="danger">
                                Danger zone
                            </TabsTrigger>
                        )}
                    </TabsList>
                    <TabsContent value="settings" className="mt-4">
                        <Card>
                            <CardContent className="pt-6">
                                <Form
                                    action={action}
                                    method={method}
                                    className="space-y-6"
                                >
                                    {({ processing, errors }) => (
                                        <>
                                            <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                                <FormField
                                                    label="Name"
                                                    htmlFor="name"
                                                    error={errors.name}
                                                >
                                                    <Input
                                                        id="name"
                                                        name="name"
                                                        required
                                                        defaultValue={
                                                            gallery?.name ?? ''
                                                        }
                                                    />
                                                </FormField>
                                                <FormField
                                                    label="Slug"
                                                    htmlFor="slug"
                                                    hint="optional — auto from name"
                                                    error={errors.slug}
                                                >
                                                    <Input
                                                        id="slug"
                                                        name="slug"
                                                        pattern="[a-z0-9-]+"
                                                        defaultValue={
                                                            gallery?.slug ?? ''
                                                        }
                                                    />
                                                </FormField>
                                            </div>

                                            <FormField
                                                label="Description"
                                                htmlFor="description"
                                                error={errors.description}
                                            >
                                                <Textarea
                                                    id="description"
                                                    name="description"
                                                    rows={3}
                                                    defaultValue={
                                                        gallery?.description ??
                                                        ''
                                                    }
                                                />
                                            </FormField>

                                            <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                                <FormField
                                                    label="Visibility"
                                                    error={errors.visibility}
                                                >
                                                    <input
                                                        type="hidden"
                                                        name="visibility"
                                                        value={visibility}
                                                    />
                                                    <Select
                                                        value={visibility}
                                                        onValueChange={(v) =>
                                                            setVisibility(
                                                                v as Gallery['visibility'],
                                                            )
                                                        }
                                                    >
                                                        <SelectTrigger>
                                                            <SelectValue />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            <SelectItem value="private">
                                                                Private —
                                                                sign-in required
                                                            </SelectItem>
                                                            <SelectItem value="public">
                                                                Public — anyone
                                                                with the link
                                                            </SelectItem>
                                                        </SelectContent>
                                                    </Select>
                                                </FormField>
                                                <FormField
                                                    label="Max upload size (MB)"
                                                    htmlFor="max_size_mb"
                                                    error={errors.max_size_mb}
                                                >
                                                    <Input
                                                        id="max_size_mb"
                                                        name="max_size_mb"
                                                        type="number"
                                                        min={1}
                                                        max={10240}
                                                        required
                                                        defaultValue={
                                                            gallery?.max_size_mb ??
                                                            100
                                                        }
                                                    />
                                                </FormField>
                                            </div>

                                            <FormField
                                                label="Allowed MIME types"
                                                htmlFor="allowed_mimes_raw"
                                                hint="comma-separated. Empty = all images + videos. Wildcards like image/* supported."
                                                error={errors.allowed_mimes_raw}
                                            >
                                                <Input
                                                    id="allowed_mimes_raw"
                                                    name="allowed_mimes_raw"
                                                    placeholder="image/*, video/mp4"
                                                    defaultValue={
                                                        gallery?.allowed_mimes_raw ??
                                                        ''
                                                    }
                                                />
                                            </FormField>

                                            <FormField
                                                label=""
                                                error={errors.comments_enabled}
                                            >
                                                <label className="flex items-center gap-3 text-sm">
                                                    <input
                                                        type="hidden"
                                                        name="comments_enabled"
                                                        value="0"
                                                    />
                                                    <input
                                                        type="checkbox"
                                                        name="comments_enabled"
                                                        value="1"
                                                        defaultChecked={
                                                            gallery?.comments_enabled ??
                                                            true
                                                        }
                                                        className="size-4 rounded border-input"
                                                    />
                                                    <span>
                                                        Allow signed-in users to
                                                        comment on items
                                                    </span>
                                                </label>
                                            </FormField>

                                            <div className="flex items-center gap-3 border-t pt-6">
                                                <Button
                                                    type="submit"
                                                    disabled={processing}
                                                >
                                                    {processing && <Spinner />}
                                                    {isEdit
                                                        ? 'Save changes'
                                                        : 'Create gallery'}
                                                </Button>
                                                <Button
                                                    asChild
                                                    variant="outline"
                                                >
                                                    <Link
                                                        href={
                                                            isEdit
                                                                ? `/admin/galleries/${gallery.id}`
                                                                : '/admin/galleries'
                                                        }
                                                    >
                                                        Cancel
                                                    </Link>
                                                </Button>
                                            </div>
                                        </>
                                    )}
                                </Form>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    {isEdit && (
                        <TabsContent value="danger" className="mt-4">
                            <Card>
                                <CardHeader>
                                    <CardTitle>Delete gallery</CardTitle>
                                    <CardDescription>
                                        This permanently removes the gallery and
                                        all of its items. This action cannot be
                                        undone.
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <AlertDialog>
                                        <AlertDialogTrigger asChild>
                                            <Button variant="destructive">
                                                Delete gallery
                                            </Button>
                                        </AlertDialogTrigger>
                                        <AlertDialogContent>
                                            <AlertDialogHeader>
                                                <AlertDialogTitle>
                                                    Delete "{gallery.name}"?
                                                </AlertDialogTitle>
                                                <AlertDialogDescription>
                                                    This permanently deletes the
                                                    gallery and all of its
                                                    items.
                                                </AlertDialogDescription>
                                            </AlertDialogHeader>
                                            <AlertDialogFooter>
                                                <AlertDialogCancel>
                                                    Cancel
                                                </AlertDialogCancel>
                                                <AlertDialogAction
                                                    onClick={() =>
                                                        router.delete(
                                                            `/admin/galleries/${gallery.id}`,
                                                        )
                                                    }
                                                    className="bg-destructive text-destructive-foreground hover:bg-destructive/90"
                                                >
                                                    Delete gallery
                                                </AlertDialogAction>
                                            </AlertDialogFooter>
                                        </AlertDialogContent>
                                    </AlertDialog>
                                </CardContent>
                            </Card>
                        </TabsContent>
                    )}
                </Tabs>
            </div>
        </>
    );
}
