import { describe, expect, test } from 'vitest'
import AdminSprinkle from '../index'

describe('app/assets/index.ts', () => {
    test('exposes install function', () => {
        expect(AdminSprinkle).toEqual({
            install: expect.any(Function)
        })
    })

    test('install executes without throwing', () => {
        expect(() => AdminSprinkle.install()).not.toThrow()
    })
})
