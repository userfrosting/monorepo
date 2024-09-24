declare const routes: {
    path: string;
    name: string;
    meta: {
        guest: {
            redirect: {
                name: string;
            };
        };
    };
    component: () => Promise<typeof import("../views/RegisterView.vue")>;
}[];
export default routes;
