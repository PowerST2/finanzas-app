export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
    currency?: string;
    is_superuser?: boolean;
    is_active?: boolean;
    security_question?: string;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
};
