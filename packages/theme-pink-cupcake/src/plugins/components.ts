import type { App } from 'vue'
import {
    UF2FaCode,
    AppLink,
    CardBox,
    CardBoxHalf,
    CardBoxLarge,
    UFFooter,
    HeaderPage,
    InfoBox,
    MainContent,
    UFFormValidationError,
    UFModal,
    UFModalAlert,
    UFModalConfirmation,
    UFModalPrompt,
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
    SideBarUserCard,
    SprunjeColumn,
    SprunjeFilters,
    SprunjeHeader,
    SprunjePaginator,
    SprunjeSearch,
    SprunjeTable,
    UFAlert,
    UFLabel
} from '../components'

/**
 * Register every component globally
 * See : https://vuejs.org/guide/components/registration
 */
export default {
    install: (app: App) => {
        app.component('UF2FaCode', UF2FaCode)
            .component('UFAppLink', AppLink)
            .component('UFCardBox', CardBox)
            .component('UFCardBoxHalf', CardBoxHalf)
            .component('UFCardBoxLarge', CardBoxLarge)
            .component('UFFooter', UFFooter)
            .component('UFHeaderPage', HeaderPage)
            .component('UFInfoBox', InfoBox)
            .component('UFMainContent', MainContent)
            .component('UFFormValidationError', UFFormValidationError)
            .component('UFModal', UFModal)
            .component('UFModalAlert', UFModalAlert)
            .component('UFModalConfirmation', UFModalConfirmation)
            .component('UFModalPrompt', UFModalPrompt)
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
            .component('UFSideBarUserCard', SideBarUserCard)
            .component('UFSprunjeColumn', SprunjeColumn)
            .component('UFSprunjeFilters', SprunjeFilters)
            .component('UFSprunjeHeader', SprunjeHeader)
            .component('UFSprunjePaginator', SprunjePaginator)
            .component('UFSprunjeSearch', SprunjeSearch)
            .component('UFSprunjeTable', SprunjeTable)
            .component('UFAlert', UFAlert)
            .component('UFLabel', UFLabel)
    }
}

declare module 'vue' {
    export interface GlobalComponents {
        UF2FaCode: typeof UF2FaCode
        UFAppLink: typeof AppLink
        UFCardBox: typeof CardBox
        UFCardBoxHalf: typeof CardBoxHalf
        UFCardBoxLarge: typeof CardBoxLarge
        UFFooter: typeof UFFooter
        UFHeaderPage: typeof HeaderPage
        UFInfoBox: typeof InfoBox
        UFMainContent: typeof MainContent
        UFFormValidationError: typeof UFFormValidationError
        UFModal: typeof UFModal
        UFModalAlert: typeof UFModalAlert
        UFModalConfirmation: typeof UFModalConfirmation
        UFModalPrompt: typeof UFModalPrompt
        UFNavBar: typeof NavBar
        UFNavBarDropdown: typeof NavBarDropdown
        UFNavBarDropdownSeparator: typeof NavBarDropdownSeparator
        UFNavBarItem: typeof NavBarItem
        UFNavBarLogin: typeof NavBarLogin
        UFNavBarUserCard: typeof NavBarUserCard
        UFNavBarUserCardButton: typeof NavBarUserCardButton
        UFSideBar: typeof SideBar
        UFSideBarDropdown: typeof SideBarDropdown
        UFSideBarItem: typeof SideBarItem
        UFSideBarLabel: typeof SideBarLabel
        UFSideBarUserCard: typeof SideBarUserCard
        UFSprunjeColumn: typeof SprunjeColumn
        UFSprunjeFilters: typeof SprunjeFilters
        UFSprunjeHeader: typeof SprunjeHeader
        UFSprunjePaginator: typeof SprunjePaginator
        UFSprunjeSearch: typeof SprunjeSearch
        UFSprunjeTable: typeof SprunjeTable
        UFAlert: typeof UFAlert
        UFLabel: typeof UFLabel
    }
}
