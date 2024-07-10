import { AlertInterface } from '../interfaces';

declare const authStore: import('pinia').Store<"auth", {
    user: import('../interfaces').UserInterface | null;
}, {
    isAuthenticated: (state: {
        user: {
            id: number;
            user_name: string;
            first_name: string;
            last_name: string;
            full_name: string;
            email: string;
            avatar: string;
            flag_enabled: boolean;
            flag_verified: boolean;
            group_id: number | null;
            locale: string;
            created_at: Date | string;
            updated_at: Date | string;
            deleted_at: Date | string | null;
        } | null;
    } & import('pinia').PiniaCustomStateProperties<{
        user: import('../interfaces').UserInterface | null;
    }>) => boolean;
}, {
    setUser(user: import('../interfaces').UserInterface): void;
    unsetUser(): void;
}>;
/**
 * Composable used to communicate with the `/auth/logout` api. Calling "logout"
 * will send the request to logout the user server side and delete the frontend
 * user object.
 */
export declare function useLogoutApi(auth: typeof authStore): {
    loading: import('vue').Ref<boolean>;
    error: import('vue').Ref<AlertInterface | undefined>;
    logout: () => void;
};
export {};
