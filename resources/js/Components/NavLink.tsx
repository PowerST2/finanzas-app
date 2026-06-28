import { InertiaLinkProps, Link } from '@inertiajs/react';

export default function NavLink({
    active = false,
    className = '',
    children,
    ...props
}: InertiaLinkProps & { active: boolean }) {
    return (
        <Link
            {...props}
            className={
                'inline-flex items-center rounded-full px-3 py-2 text-sm font-semibold transition duration-150 ease-in-out focus:outline-none ' +
                (active
                    ? 'bg-white text-slate-950 shadow-sm'
                    : 'text-slate-300 hover:bg-white/10 hover:text-white') +
                className
            }
        >
            {children}
        </Link>
    );
}
