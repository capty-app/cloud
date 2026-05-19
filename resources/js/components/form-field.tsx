import type { ReactNode } from 'react';
import InputError from '@/components/input-error';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';

export function FormField({
    label,
    htmlFor,
    error,
    hint,
    className,
    children,
}: {
    label?: string;
    htmlFor?: string;
    error?: string;
    hint?: ReactNode;
    className?: string;
    children: ReactNode;
}) {
    return (
        <div className={cn('space-y-2', className)}>
            {label && (
                <Label htmlFor={htmlFor}>
                    {label}
                    {hint && (
                        <span className="ml-2 text-xs font-normal text-muted-foreground">
                            {hint}
                        </span>
                    )}
                </Label>
            )}
            {children}
            <InputError message={error} />
        </div>
    );
}
