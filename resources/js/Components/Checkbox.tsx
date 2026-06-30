import { InputHTMLAttributes } from 'react';

export default function Checkbox({
    className = '',
    ...props
}: InputHTMLAttributes<HTMLInputElement>) {
    return (
        <input
            {...props}
            type="checkbox"
            className={
                'finance-checkbox h-4 w-4 shadow-sm focus:ring-emerald-500 ' +
                className
            }
        />
    );
}
