import type { App } from 'vue'
import {
    UFAlert,
    UFLabel,
    UFModal,
    UFModalAlert,
    UFModalConfirmation,
    UFModalPrompt,
    AppLink,
    InfoBox,
    CardBox,
    CardBoxLarge,
    CardBoxHalf,
    MainContent,
    HeaderPage,
    FooterContent,
    NavBar,
    NavBarDropdown,
    NavBarDropdownSeparator,
    NavBarItem,
    NavBarLogin,
    NavBarUserCard,
    NavBarUserCardButton,
    SideBar,
    SideBarDropdown,
    SideBarItem,
    SideBarLabel,
    SprunjeColumn,
    SprunjeHeader,
    SprunjePaginator,
    SprunjeTable
} from '../components'

/**
 * Register every component globally
 * See : https://vuejs.org/guide/components/registration
 */
export default {
    install: (app: App) => {
        app.component('UFAlert', UFAlert)
            .component('UFLabel', UFLabel)
            .component('UFModal', UFModal)
            .component('UFModalAlert', UFModalAlert)
            .component('UFModalConfirmation', UFModalConfirmation)
            .component('UFModalPrompt', UFModalPrompt)
            .component('UFAppLink', AppLink)
            .component('UFInfoBox', InfoBox)
            .component('UFCardBox', CardBox)
            .component('UFCardBoxLarge', CardBoxLarge)
            .component('UFCardBoxHalf', CardBoxHalf)
            .component('UFMainContent', MainContent)
            .component('UFHeaderPage', HeaderPage)
            .component('UFFooterContent', FooterContent)
            .component('UFNavBar', NavBar)
            .component('UFNavBarDropdown', NavBarDropdown)
            .component('UFNavBarDropdownSeparator', NavBarDropdownSeparator)
            .component('UFNavBarItem', NavBarItem)
            .component('UFNavBarLogin', NavBarLogin)
            .component('UFNavBarUserCard', NavBarUserCard)
            .component('UFNavBarUserCardButton', NavBarUserCardButton)
            .component('UFSideBar', SideBar)
            .component('UFSideBarDropdown', SideBarDropdown)
            .component('UFSideBarItem', SideBarItem)
            .component('UFSideBarLabel', SideBarLabel)
            .component('UFSprunjeColumn', SprunjeColumn)
            .component('UFSprunjeHeader', SprunjeHeader)
            .component('UFSprunjePaginator', SprunjePaginator)
            .component('UFSprunjeTable', SprunjeTable)
    }
}

declare module 'vue' {
    export interface GlobalComponents {
        UFAlert: typeof UFAlert
        UFLabel: typeof UFLabel
        UFModal: typeof UFModal
        UFModalAlert: typeof UFModalAlert
        UFModalConfirmation: typeof UFModalConfirmation
        UFModalPrompt: typeof UFModalPrompt
        UFAppLink: typeof AppLink
        UFInfoBox: typeof InfoBox
        UFCardBox: typeof CardBox
        UFCardBoxLarge: typeof CardBoxLarge
        UFMainContent: typeof MainContent
        UFHeaderPage: typeof HeaderPage
        UFFooterContent: typeof FooterContent
        UFNavBar: typeof NavBar
        UFNavBarDropdown: typeof NavBarDropdown
        UFNavBarDropdownSeparator: typeof NavBarDropdownSeparator
        UFNavBarLogin: typeof NavBarLogin
        UFNavBarItem: typeof NavBarItem
        UFNavBarUserCard: typeof NavBarUserCard
        UFNavBarUserCardButton: typeof NavBarUserCardButton
        UFSideBar: typeof SideBar
        UFSideBarDropdown: typeof SideBarDropdown
        UFSideBarItem: typeof SideBarItem
        UFSideBarLabel: typeof SideBarLabel
        UFSprunjeColumn: typeof SprunjeColumn
        UFSprunjeHeader: typeof SprunjeHeader
        UFSprunjePaginator: typeof SprunjePaginator
        UFSprunjeTable: typeof SprunjeTable
    }
}
