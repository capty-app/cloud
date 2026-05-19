import { Head, Link } from '@inertiajs/react';
import { cn } from '@/lib/utils';

type DocPage = {
    slug: string;
    title: string;
    url: string;
};

export default function DocsShow({
    title,
    html,
    pages,
    current,
}: {
    title: string;
    html: string;
    pages: DocPage[];
    current: string;
}) {
    return (
        <>
            <Head title={`Docs — ${title}`} />

            <div className="flex gap-8">
                <aside className="hidden w-56 shrink-0 md:block">
                    <div className="sticky top-6">
                        <div className="mb-3 px-3 text-xs font-medium tracking-wider text-muted-foreground uppercase">
                            Documentation
                        </div>
                        <nav className="flex flex-col gap-0.5">
                            {pages.map((p) => (
                                <Link
                                    key={p.slug}
                                    href={p.url}
                                    className={cn(
                                        'rounded-md px-3 py-2 text-sm font-medium transition-colors',
                                        current === p.slug
                                            ? 'bg-accent text-accent-foreground'
                                            : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground',
                                    )}
                                >
                                    {p.title}
                                </Link>
                            ))}
                        </nav>
                    </div>
                </aside>

                <main className="min-w-0 flex-1">
                    <article
                        className={cn(
                            'prose max-w-none prose-slate dark:prose-invert',
                            'prose-headings:scroll-mt-20 prose-headings:tracking-tight',
                            'prose-code:rounded prose-code:bg-muted prose-code:px-1.5 prose-code:py-0.5 prose-code:text-[0.875em] prose-code:font-medium prose-code:before:content-none prose-code:after:content-none',
                            'prose-pre:rounded-md prose-pre:border prose-pre:bg-muted prose-pre:text-foreground',
                            'prose-a:font-medium prose-a:underline-offset-2',
                            'prose-blockquote:border-l-primary prose-blockquote:font-normal prose-blockquote:not-italic',
                            'prose-table:overflow-hidden prose-th:bg-muted prose-th:px-3 prose-th:py-2 prose-td:px-3 prose-td:py-2',
                        )}
                        dangerouslySetInnerHTML={{ __html: html }}
                    />
                </main>
            </div>
        </>
    );
}
