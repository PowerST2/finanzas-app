export const money = (value: number | string | null | undefined, currency = 'PEN') =>
    `${currency === 'USD' ? 'US$' : 'S/'} ${Number(value ?? 0).toLocaleString('es-PE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}`;

export const today = () => new Date().toISOString().slice(0, 10);
export const nowLocal = () => {
    const date = new Date();
    date.setMinutes(date.getMinutes() - date.getTimezoneOffset());
    return date.toISOString().slice(0, 16);
};
export const month = () => new Date().toISOString().slice(0, 7);

export const dateTime = (value: string | null | undefined) => {
    if (!value) return '';
    const date = new Date(value);
    return `${String(date.getDate()).padStart(2, '0')}/${String(date.getMonth() + 1).padStart(2, '0')}/${String(date.getFullYear()).slice(-2)} - ${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}`;
};

export const transactionType = (type: string) => ({
    income: 'Ingreso',
    expense: 'Egreso',
    transfer: 'Transferencia',
    loan_received: 'Prestamo recibido',
    loan_payment: 'Pago de prestamo recibido',
    loan_given: 'Prestamo otorgado',
    loan_collection: 'Cobro de prestamo otorgado',
    adjustment: 'Ajuste',
}[type] || type);

export const loanKind = (kind: string) => kind === 'lent' ? 'Prestamo que otorgue' : 'Prestamo que recibi';

export const walletType = (type: string) => ({
    cash: 'Efectivo',
    bank: 'Banco',
    digital_wallet: 'Billetera digital',
    credit_card: 'Tarjeta de credito',
    savings: 'Ahorros',
    other: 'Otro',
}[type] || type);
