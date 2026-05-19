// shadcn-styled class overrides for inertia-table-react.
// The library's defaults hard-code gray-50/white/blue-500 utility classes
// that ignore our CSS variables, so the table looks unthemed in dark mode.
// These class strings map every slot onto our theme tokens.

export const tableClassNames = {
    wrapper: 'space-y-3',
    toolbar: 'flex flex-wrap items-center justify-between gap-3',
    table: 'w-full caption-bottom text-sm',
    thead: 'border-b bg-muted/50',
    th: 'h-10 px-3 text-left align-middle text-xs font-medium uppercase tracking-wider text-muted-foreground whitespace-nowrap',
    thSortable: 'cursor-pointer select-none hover:text-foreground',
    thSorted: 'text-foreground',
    tbody: '[&_tr:last-child]:border-0',
    tr: 'border-b transition-colors hover:bg-muted/50',
    trClickable: 'cursor-pointer',
    td: 'px-3 py-3 align-middle whitespace-nowrap text-sm',
    search: 'flex h-9 w-full max-w-xs rounded-md border border-input bg-background px-3 py-1 text-sm shadow-xs transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring',
    pagination: 'flex items-center justify-between gap-3 pt-2',
    paginationButton:
        'inline-flex h-8 items-center justify-center rounded-md border border-input bg-background px-3 text-xs font-medium transition-colors hover:bg-accent hover:text-accent-foreground disabled:pointer-events-none disabled:opacity-50',
    paginationInfo: 'text-xs text-muted-foreground',
    empty: 'py-12 text-center text-sm text-muted-foreground',
};
