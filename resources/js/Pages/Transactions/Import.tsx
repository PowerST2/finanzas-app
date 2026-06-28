import InputError from '@/Components/InputError';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, useForm } from '@inertiajs/react';
import { useMemo, useState } from 'react';

export default function Import({ wallets, categories }: any) {
    const [headers, setHeaders] = useState<string[]>([]);
    const [rawRows, setRawRows] = useState<string[][]>([]);
    const [map, setMap] = useState<any>({ wallet_id: '', type: '', amount: '', date: '', description: '', category_id: '', status: '' });
    const form = useForm({ file: null as File | null, rows: [] as any[] });
    const fields = [['wallet_id', 'Billetera'], ['type', 'Tipo'], ['amount', 'Monto'], ['currency', 'Moneda'], ['date', 'Fecha'], ['description', 'Descripcion'], ['category_id', 'Categoria'], ['status', 'Estado']];
    const preview = useMemo(() => rawRows.slice(0, 5).map((row) => toMapped(row, headers, map)), [rawRows, headers, map]);
    const read = (file: File) => {
        const reader = new FileReader();
        reader.onload = () => {
            const lines = String(reader.result || '').trim().split(/\r?\n/).map(parseCsvLine);
            setHeaders(lines.shift() || []);
            setRawRows(lines);
        };
        reader.readAsText(file);
        form.setData('file', file);
    };
    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        router.post(route('transactions.import.store'), { rows: rawRows.map((row) => toMapped(row, headers, map)) });
    };

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800">Importar CSV</h2>}>
            <Head title="Importar CSV" />
            <form onSubmit={submit} className="mx-auto max-w-5xl space-y-4 px-4 py-6">
                <div className="rounded-lg bg-white p-4 text-sm text-gray-600 shadow-sm">
                    Carga el archivo, mapea columnas y revisa la previsualizacion antes de guardar. <a href={route('transactions.import.template')} className="font-medium text-teal-700">Descargar plantilla CSV</a>.
                </div>
                <label className="block text-sm font-medium text-gray-700">Archivo CSV<input type="file" accept=".csv,text/csv" onChange={(e) => e.target.files?.[0] && read(e.target.files[0])} className="mt-1 w-full rounded-lg border border-gray-300 p-2" /></label>
                <InputError message={form.errors.file} />
                {headers.length > 0 && <section className="grid gap-3 rounded-lg bg-white p-4 shadow-sm sm:grid-cols-3">
                    {fields.map(([field, label]) => <label key={field} className="text-sm font-medium text-gray-700">{label}<select value={map[field]} onChange={(e) => setMap({ ...map, [field]: e.target.value })} className="mt-1 w-full rounded-lg border-gray-300"><option value="">Sin mapear</option>{headers.map((header) => <option key={header} value={header}>{header}</option>)}</select></label>)}
                </section>}
                {preview.length > 0 && <section className="overflow-x-auto rounded-lg bg-white p-4 shadow-sm">
                    <h3 className="font-semibold">Previsualizacion</h3>
                    <table className="mt-3 w-full text-sm"><thead><tr>{fields.map(([field, label]) => <th key={field} className="border-b p-2 text-left">{label}</th>)}</tr></thead><tbody>{preview.map((row, i) => <tr key={i}>{fields.map(([field]) => <td key={field} className="border-b p-2">{row[field] || ''}</td>)}</tr>)}</tbody></table>
                    <div className="mt-3 grid gap-2 text-sm sm:grid-cols-2">
                        <p>Billeteras: {wallets.map((w: any) => `${w.name}=${w.id}`).join(', ')}</p>
                        <p>Categorias: {categories.map((c: any) => `${c.name}=${c.id}`).join(', ')}</p>
                    </div>
                </section>}
                <button className="w-full rounded-lg bg-teal-700 px-4 py-3 font-medium text-white">Importar</button>
            </form>
        </AuthenticatedLayout>
    );
}

function parseCsvLine(line: string) {
    return line.match(/("([^"]|"")*"|[^,;]+)/g)?.map((x) => x.replace(/^"|"$/g, '').replace(/""/g, '"').trim()) || [];
}

function toMapped(row: string[], headers: string[], map: any) {
    return Object.fromEntries(Object.entries(map).map(([field, header]) => [field, header ? row[headers.indexOf(header as string)] : '']));
}
