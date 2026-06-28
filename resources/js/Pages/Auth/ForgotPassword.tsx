import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

export default function ForgotPassword({ status, email, question }: { status?: string; email?: string; question?: string }) {
    const { data, setData, post, processing, errors } = useForm({
        email: email || '',
        security_answer: '',
        password: '',
        password_confirmation: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(question ? route('password.store') : route('password.email'));
    };

    return (
        <GuestLayout>
            <Head title="Recuperar contrasena" />

            <div className="mb-4 text-sm text-gray-600">
                {question ? 'Responde tu pregunta de seguridad y crea una contrasena nueva.' : 'Escribe tu correo para recuperar tu contrasena con tu pregunta de seguridad.'}
            </div>

            {status && (
                <div className="mb-4 text-sm font-medium text-green-600">
                    {status}
                </div>
            )}

            <form onSubmit={submit}>
                <TextInput
                    id="email"
                    type="email"
                    name="email"
                    value={data.email}
                    className="mt-1 block w-full"
                    isFocused={true}
                    onChange={(e) => setData('email', e.target.value)}
                    readOnly={Boolean(question)}
                />

                <InputError message={errors.email} className="mt-2" />

                {question && <>
                    <div className="mt-4 rounded-lg bg-gray-50 p-3 text-sm text-gray-700">{question}</div>
                    <TextInput type="password" value={data.security_answer} className="mt-4 block w-full" placeholder="Respuesta" onChange={(e) => setData('security_answer', e.target.value)} />
                    <InputError message={errors.security_answer} className="mt-2" />
                    <TextInput type="password" value={data.password} className="mt-4 block w-full" placeholder="Nueva contrasena" onChange={(e) => setData('password', e.target.value)} />
                    <InputError message={errors.password} className="mt-2" />
                    <TextInput type="password" value={data.password_confirmation} className="mt-4 block w-full" placeholder="Confirmar contrasena" onChange={(e) => setData('password_confirmation', e.target.value)} />
                </>}

                <div className="mt-4 flex items-center justify-end">
                    <PrimaryButton className="ms-4" disabled={processing}>
                        {question ? 'Cambiar contrasena' : 'Continuar'}
                    </PrimaryButton>
                </div>
            </form>
        </GuestLayout>
    );
}
