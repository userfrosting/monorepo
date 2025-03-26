import type { App } from 'vue'

// Import sub-plugins
import PinkCupcakeComponents from './plugins/components'
import AdminSprinkle from './plugins/admin'
import AccountSprinkle from './plugins/account'
import CoreSprinkle from './plugins/core'
import FontAwesome from './plugins/font-awesome'

// Init UIkit
import UIkit from 'uikit'
import Icons from 'uikit/dist/js/uikit-icons'
UIkit.use(Icons)

/* Install plugins */
export default {
    install: (app: App) => {
        app.use(PinkCupcakeComponents)
        app.use(AdminSprinkle) // TODO : Add option to disable this or load on demand
        app.use(AccountSprinkle) // TODO : Add option to disable this or load on demand
        app.use(CoreSprinkle) 
        app.use(FontAwesome)
    }
}
