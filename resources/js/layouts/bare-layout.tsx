import { useFlashToast } from '@/hooks/use-flash-toast';

export default function BareLayout({
    children,
}: {
    children: React.ReactNode;
}) {
    useFlashToast();

    return <>{children}</>;
}
