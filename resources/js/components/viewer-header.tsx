import { Link, router, usePage } from '@inertiajs/react';
import { LogOut, Shield } from 'lucide-react';
import { ThemeSwitcher } from '@/components/theme-switcher';
import { Button } from '@/components/ui/button';
import type { SharedData } from '@/types';

export function ViewerHeader() {
    const { auth, name } = usePage<SharedData>().props;

    return (
        <header className="border-b bg-card">
            <div className="mx-auto flex max-w-6xl items-center justify-between gap-3 px-6 py-3">
                <Link
                    href={auth.user ? '/dashboard' : '/'}
                    className="font-semibold tracking-tight"
                >
                    {name}
                </Link>
                <div className="flex items-center gap-2">
                    <ThemeSwitcher />
                    {auth.user ? (
                        <>
                            <span className="hidden text-sm text-muted-foreground sm:inline">
                                {auth.user.name}
                            </span>
                            {auth.user.role === 'admin' && (
                                <Button asChild variant="outline" size="sm">
                                    <Link href="/admin">
                                        <Shield className="size-4" />
                                        Admin
                                    </Link>
                                </Button>
                            )}
                            <Button
                                variant="outline"
                                size="sm"
                                onClick={() => router.post('/logout')}
                            >
                                <LogOut className="size-4" />
                                Sign out
                            </Button>
                        </>
                    ) : (
                        <Button asChild variant="outline" size="sm">
                            <Link href="/login">Sign in</Link>
                        </Button>
                    )}
                </div>
            </div>
        </header>
    );
}
