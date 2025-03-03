import type { UserInterface } from '@userfrosting/sprinkle-account/interfaces'

export interface DashboardResponse {
    counter: {
        users: number
        roles: number
        groups: number
    }
    users: UserInterface[]
}
