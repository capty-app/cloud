import { Link, usePage } from '@inertiajs/react';
import { BookOpen, ImageIcon, LayoutGrid, Users } from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import type { NavItem, SharedData } from '@/types';

export function AppSidebar() {
    const { auth } = usePage<SharedData>().props;
    const isAdmin = auth.user?.role === 'admin';

    const items: NavItem[] = isAdmin
        ? [
              { title: 'Overview', href: '/admin', icon: LayoutGrid },
              { title: 'Galleries', href: '/admin/galleries', icon: ImageIcon },
              { title: 'Users', href: '/admin/users', icon: Users },
              { title: 'Documentation', href: '/docs', icon: BookOpen },
          ]
        : [
              { title: 'Galleries', href: '/dashboard', icon: ImageIcon },
              { title: 'Documentation', href: '/docs', icon: BookOpen },
          ];

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link
                                href={isAdmin ? '/admin' : '/dashboard'}
                                prefetch
                            >
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={items} />
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
