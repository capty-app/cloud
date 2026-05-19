import { Search } from 'lucide-react';
import { Input } from '@/components/ui/input';

export function TableSearch({
    searchTerm,
    onSearch,
    placeholder,
}: {
    searchTerm: string;
    onSearch: (term: string) => void;
    placeholder: string;
}) {
    return (
        <div className="relative w-full max-w-xs">
            <Search className="pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-muted-foreground" />
            <Input
                value={searchTerm}
                onChange={(e) => onSearch(e.target.value)}
                placeholder={placeholder}
                className="pl-8"
            />
        </div>
    );
}
