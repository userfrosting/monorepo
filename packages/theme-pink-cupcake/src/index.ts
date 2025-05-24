import type { App } from 'vue'

/**
 * Import sub-plugins.
 */
import PinkCupcakeComponents from './plugins/components'
import AdminSprinkle from './plugins/admin'
import AccountSprinkle from './plugins/account'
import CoreSprinkle from './plugins/core'
import FontAwesome from './plugins/font-awesome'
import UiKitNotifications from './plugins/notifications'

/**
 * Import UIkit and its icons.
 */
import UIkit from 'uikit'
import Icons from 'uikit/dist/js/uikit-icons'
UIkit.use(Icons)

/**
 * Pink Cupcake Theme initialization recipe.
 *
 * This recipe is responsible for loading the Pink Cupcake, Admin, Account and
 * Core sprinkle plugins. It also load the FontAwesome icons.
 */
export default {
    install: (app: App) => {
        app.use(PinkCupcakeComponents)
        app.use(AdminSprinkle) // TODO : Add option to disable this or load on demand
        app.use(AccountSprinkle) // TODO : Add option to disable this or load on demand
        app.use(CoreSprinkle)
        app.use(FontAwesome)
        app.use(UiKitNotifications)
    }
}
