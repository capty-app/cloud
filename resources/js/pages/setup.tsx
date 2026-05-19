import { Form, Head } from '@inertiajs/react';
import { FormField } from '@/components/form-field';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';

export default function Setup() {
    return (
        <>
            <Head title="First-run setup" />
            <div className="flex min-h-svh items-center justify-center bg-background p-6">
                <Card className="w-full max-w-md">
                    <CardHeader className="space-y-2">
                        <CardTitle>Welcome — create the first admin</CardTitle>
                        <CardDescription>
                            This page is shown only on first run. Once an admin
                            exists, it auto-redirects to the login page.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Form
                            action="/setup"
                            method="post"
                            className="space-y-6"
                            resetOnSuccess={[
                                'password',
                                'password_confirmation',
                            ]}
                        >
                            {({ processing, errors }) => (
                                <>
                                    <FormField
                                        label="Name"
                                        htmlFor="name"
                                        error={errors.name}
                                    >
                                        <Input
                                            id="name"
                                            name="name"
                                            required
                                            autoFocus
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
                                            autoComplete="email"
                                        />
                                    </FormField>
                                    <FormField
                                        label="Password"
                                        htmlFor="password"
                                        error={errors.password}
                                    >
                                        <Input
                                            id="password"
                                            name="password"
                                            type="password"
                                            required
                                            minLength={8}
                                            autoComplete="new-password"
                                        />
                                    </FormField>
                                    <FormField
                                        label="Confirm password"
                                        htmlFor="password_confirmation"
                                    >
                                        <Input
                                            id="password_confirmation"
                                            name="password_confirmation"
                                            type="password"
                                            required
                                            minLength={8}
                                            autoComplete="new-password"
                                        />
                                    </FormField>
                                    <Button
                                        type="submit"
                                        className="w-full"
                                        disabled={processing}
                                    >
                                        {processing && <Spinner />}
                                        Create admin & sign in
                                    </Button>
                                </>
                            )}
                        </Form>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}
