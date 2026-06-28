import { ImgHTMLAttributes } from 'react';
import { usePage } from '@inertiajs/react';

export default function ApplicationLogo(props: ImgHTMLAttributes<HTMLImageElement>) {
    const user = usePage().props.auth?.user as any;
    return <img {...props} src={user?.logo_path || '/brand/logo.png'} alt={user?.app_label || 'Finanzas'} />;
}
