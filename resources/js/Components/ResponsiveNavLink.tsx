import { InertiaLinkProps, Link } from '@inertiajs/react';

export default function ResponsiveNavLink({
    active = false,
    className = '',
    children,
    ...props
}: InertiaLinkProps & { active?: boolean }) {
    return (
        <Link
            {...props}
            className={`mx-3 flex items-start rounded-xl px-4 py-3 ${
                active
                    ? 'bg-slate-900 text-white'
                    : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'
            } text-base font-medium transition duration-150 ease-in-out focus:outline-none ${className}`}
        >
            {children}
        </Link>
    );
}
