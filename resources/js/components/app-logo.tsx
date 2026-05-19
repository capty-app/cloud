import { usePage } from '@inertiajs/react';
import type { SharedData } from '@/types';

export default function AppLogo() {
    const { name } = usePage<SharedData>().props;

    return (
        <>
            <div className="flex aspect-square size-9 items-center justify-center">
                <img
                    src="/logo.svg"
                    alt={name}
                    className="size-9 rounded-sm border border-transparent dark:border-border"
                />
            </div>
            <div className="ml-1 grid flex-1 text-left text-sm">
                <span className="truncate leading-tight font-semibold">
                    {name}
                </span>
            </div>
        </>
    );
}
