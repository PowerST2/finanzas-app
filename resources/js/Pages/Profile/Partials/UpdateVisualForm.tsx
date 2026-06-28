import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { useForm, usePage } from '@inertiajs/react';
import { FormEventHandler } from 'react';

export default function UpdateVisualForm({ className = '' }: { className?: string }) {
    const user = usePage().props.auth.user as any;
    const form = useForm({ app_label: user.app_label || 'Finanzas PWA', theme_color: user.theme_color || '#0f766e', logo: null as File | null });
    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        form.post(route('profile.visual'), { forceFormData: true });
    };

    return (
        <section className={className}>
            <header>
                <h2 className="text-lg font-medium text-gray-900">Apariencia</h2>
                <p className="mt-1 text-sm text-gray-600">Nombre, color y logo local de la app.</p>
            </header>
            <form onSubmit={submit} className="mt-6 space-y-4">
                <label className="block text-sm font-medium text-gray-700">Nombre visible<TextInput value={form.data.app_label} onChange={(e) => form.setData('app_label', e.target.value)} className="mt-1 block w-full" /></label>
                <label className="block text-sm font-medium text-gray-700">Color principal<input type="color" value={form.data.theme_color} onChange={(e) => form.setData('theme_color', e.target.value)} className="mt-1 h-10 w-20 rounded border border-gray-300" /></label>
                <label className="block text-sm font-medium text-gray-700">Logo<input type="file" accept="image/*" onChange={(e) => form.setData('logo', e.target.files?.[0] || null)} className="mt-1 block w-full rounded-lg border border-gray-300 p-2" /></label>
                <InputError message={form.errors.app_label || form.errors.theme_color || form.errors.logo} />
                <PrimaryButton disabled={form.processing}>Guardar apariencia</PrimaryButton>
            </form>
        </section>
    );
}
