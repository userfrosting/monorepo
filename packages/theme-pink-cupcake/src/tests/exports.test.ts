import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { describe, expect, test } from 'vitest'
import ThemePlugin from '../index'
import * as Components from '../components'
import * as Views from '../views'
import * as Plugins from '../plugins'
import * as AdminPageComponents from '../components/Pages/Admin'

describe('theme exports', () => {
    test('exports root plugin and plugin barrels', () => {
        expect(ThemePlugin.install).toEqual(expect.any(Function))
        expect(Plugins.AccountSprinkle.install).toEqual(expect.any(Function))
        expect(Plugins.AdminSprinkle.install).toEqual(expect.any(Function))
        expect(Plugins.PinkCupcakeComponents.install).toEqual(expect.any(Function))
        expect(Plugins.CoreSprinkle.install).toEqual(expect.any(Function))
        expect(Plugins.FontAwesome.install).toEqual(expect.any(Function))
        expect(Plugins.UiKitNotifications.install).toEqual(expect.any(Function))
    })

    test('exports key component and view barrels', () => {
        expect(Components.UFAlert).toBeDefined()
        expect(Components.UFLabel).toBeDefined()
        expect(Components.SideBarUserCard).toBeDefined()
        expect(Views.PageUserSettings).toBeDefined()
        expect(Views.PageUserSettingsEmail).toBeDefined()
        expect(Views.PageUserSettingsPassword).toBeDefined()
        expect(Views.PageUserSettingsProfile).toBeDefined()
        expect(Views.UFAdminConfigCachePage).toBeDefined()
        expect(Views.UFAdminConfigInfoPage).toBeDefined()
        expect(Views.ErrorPage).toBeDefined()
        expect(AdminPageComponents.GroupForm).toBeDefined()
        expect(AdminPageComponents.RoleForm).toBeDefined()
        expect(AdminPageComponents.UserForm).toBeDefined()
    })

    test('theme plugin installs without throwing', () => {
        const app = createApp({})
        app.use(createPinia())
        expect(() => ThemePlugin.install(app)).not.toThrow()
    })
})
