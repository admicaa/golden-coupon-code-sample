export default {
    install(Vue, { store }) {
        const hasPermissionTo = (permissions, method = 'every') => {
            let methods = {
                'every': (permissions) => {
                    return permissions.every(permission => {
                        let user = store.getters['user']
                        if (!user || !user.all_permissions) {
                            return false;
                        }
                        return user.all_permissions.includes(permission)
                    })
                },
                'any': (permissions) => {
                    return permissions.some(permission => {
                        let user = store.getters['user']
                        if (!user || !user.all_permissions) {
                            return false;
                        }
                        return user.all_permissions.includes(permission)
                    })
                }

            }
            return methods[method](permissions);

        }
        Vue.can = hasPermissionTo;
        Vue.prototype.$can = hasPermissionTo;

    }


}