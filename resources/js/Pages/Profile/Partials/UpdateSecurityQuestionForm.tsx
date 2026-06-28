import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { useForm, usePage } from '@inertiajs/react';
import { FormEventHandler } from 'react';

export default function UpdateSecurityQuestionForm({ className = '' }: { className?: string }) {
    const user = usePage().props.auth.user as any;
    const form = useForm({ security_question: user.security_question || 'Cual fue tu primer colegio?', security_answer: '' });
    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        form.post(route('profile.security-question'), { onSuccess: () => form.reset('security_answer') });
    };

    return (
        <section className={className}>
            <header>
                <h2 className="text-lg font-medium text-gray-900">Pregunta de seguridad</h2>
                <p className="mt-1 text-sm text-gray-600">La respuesta se guarda protegida y sirve para recuperar la contrasena sin correo.</p>
            </header>
            <form onSubmit={submit} className="mt-6 space-y-4">
                <select value={form.data.security_question} onChange={(e) => form.setData('security_question', e.target.value)} className="block w-full rounded-md border-gray-300">
                    <option>Cual fue tu primer colegio?</option>
                    <option>Como se llama tu mejor amigo de infancia?</option>
                    <option>En que ciudad naciste?</option>
                    <option>Cual fue tu primer trabajo?</option>
                </select>
                <TextInput type="password" value={form.data.security_answer} onChange={(e) => form.setData('security_answer', e.target.value)} className="block w-full" placeholder="Respuesta nueva" />
                <InputError message={form.errors.security_question || form.errors.security_answer} />
                <PrimaryButton disabled={form.processing}>Guardar pregunta</PrimaryButton>
            </form>
        </section>
    );
}
