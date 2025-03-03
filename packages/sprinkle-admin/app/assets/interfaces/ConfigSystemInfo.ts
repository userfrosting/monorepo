export interface SprinkleList {
    [name: string]: string
}

export interface DatabaseInfo {
    connection: string
    name: string
    type: string
    version: string
}

export interface ConfigSystemInfoResponse {
    frameworkVersion: string
    phpVersion: string
    database: DatabaseInfo
    server: string
    projectPath: string
    sprinkles: SprinkleList
}
