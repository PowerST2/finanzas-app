import { useEffect, useState } from 'react';

type Step = {
    selector: string;
    title: string;
    text: string;
};

export default function GuidedTour({
    steps,
    storageKey,
    start,
    onFinish,
}: {
    steps: Step[];
    storageKey: string;
    start: boolean;
    onFinish: () => void;
}) {
    const [index, setIndex] = useState(0);
    const [rect, setRect] = useState<DOMRect | null>(null);
    const step = steps[index];

    useEffect(() => {
        if (!start || !step) return;

        const update = () => {
            const element = document.querySelector(step.selector);
            if (!element) return setRect(null);
            element.scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(() => setRect(element.getBoundingClientRect()), 180);
        };

        update();
        window.addEventListener('resize', update);
        window.addEventListener('scroll', update, true);

        return () => {
            window.removeEventListener('resize', update);
            window.removeEventListener('scroll', update, true);
        };
    }, [start, step]);

    if (!start || !step) return null;

    const finish = () => {
        localStorage.setItem(storageKey, '1');
        onFinish();
    };

    return (
        <div className="fixed inset-0 z-[80]">
            <div className="absolute inset-0 bg-slate-950/55 backdrop-blur-[2px]" />
            {rect && (
                <div
                    className="absolute rounded-[1.5rem] border-2 border-teal-300 shadow-[0_0_0_9999px_rgba(15,23,42,0.55)]"
                    style={{
                        left: rect.left - 8,
                        top: rect.top - 8,
                        width: rect.width + 16,
                        height: rect.height + 16,
                    }}
                />
            )}
            <section className="fixed bottom-6 left-4 right-4 z-[81] rounded-[1.75rem] bg-white p-5 shadow-2xl shadow-slate-950/30 sm:left-auto sm:right-6 sm:max-w-md">
                <div className="text-xs font-black uppercase text-teal-700">Paso {index + 1} de {steps.length}</div>
                <h3 className="mt-2 text-xl font-black text-slate-950">{step.title}</h3>
                <p className="mt-2 text-sm font-semibold text-slate-600">{step.text}</p>
                <div className="mt-5 flex items-center justify-between gap-2">
                    <button type="button" onClick={finish} className="rounded-2xl px-4 py-2 text-sm font-black text-slate-500">Saltar</button>
                    <div className="flex gap-2">
                        <button type="button" disabled={index === 0} onClick={() => setIndex(index - 1)} className="rounded-2xl bg-slate-100 px-4 py-2 text-sm font-black text-slate-700 disabled:opacity-40">Atras</button>
                        {index < steps.length - 1 ? (
                            <button type="button" onClick={() => setIndex(index + 1)} className="rounded-2xl bg-slate-950 px-4 py-2 text-sm font-black text-white">Siguiente</button>
                        ) : (
                            <button type="button" onClick={finish} className="rounded-2xl bg-teal-600 px-4 py-2 text-sm font-black text-white">Terminar</button>
                        )}
                    </div>
                </div>
            </section>
        </div>
    );
}
