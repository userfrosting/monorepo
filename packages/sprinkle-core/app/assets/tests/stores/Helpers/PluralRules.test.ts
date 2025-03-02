import { describe, test, expect } from 'vitest'
import {
    rule0,
    rule1,
    rule2,
    rule3,
    rule4,
    rule5,
    rule6,
    rule7,
    rule8,
    rule9,
    rule10,
    rule11,
    rule12,
    rule13,
    rule14,
    rule15
} from '../../../stores/Helpers/PluralRules'

describe('PluralRules Tests', () => {
    test('rule0', () => {
        const testCases = [
            [0, 1],
            [1, 1],
            [2, 1],
            [-2, 1],
            [128, 1]
        ]

        testCases.forEach(([input, expected]) => {
            expect(rule0(input)).toBe(expected)
        })
    })

    test('rule1', () => {
        const testCases = [
            [0, 2],
            [1, 1],
            [2, 2],
            [-2, 2],
            [128, 2]
        ]

        testCases.forEach(([input, expected]) => {
            expect(rule1(input)).toBe(expected)
        })
    })

    test('rule2', () => {
        const testCases = [
            [0, 1],
            [1, 1],
            [2, 2],
            [-2, 2],
            [128, 2]
        ]

        testCases.forEach(([input, expected]) => {
            expect(rule2(input)).toBe(expected)
        })
    })

    test('rule3', () => {
        const testCases = [
            [0, 1],
            [1, 2],
            [2, 3],
            [11, 3],
            [21, 2],
            [141, 2],
            [128, 3]
        ]

        testCases.forEach(([input, expected]) => {
            expect(rule3(input)).toBe(expected)
        })
    })

    test('rule4', () => {
        const testCases = [
            [0, 4],
            [1, 1],
            [2, 2],
            [3, 3],
            [11, 1],
            [12, 2],
            [13, 3],
            [19, 3],
            [20, 4],
            [21, 4],
            [128, 4]
        ]

        testCases.forEach(([input, expected]) => {
            expect(rule4(input)).toBe(expected)
        })
    })

    test('rule5', () => {
        const testCases = [
            [0, 2],
            [1, 1],
            [2, 2],
            [3, 2],
            [11, 2],
            [12, 2],
            [13, 2],
            [19, 2],
            [20, 3],
            [21, 3],
            [100, 3],
            [101, 2],
            [110, 2],
            [111, 2],
            [128, 3]
        ]

        testCases.forEach(([input, expected]) => {
            expect(rule5(input)).toBe(expected)
        })
    })

    test('rule6', () => {
        const testCases = [
            [0, 2],
            [1, 1],
            [2, 3],
            [3, 3],
            [11, 2],
            [12, 2],
            [13, 2],
            [19, 2],
            [20, 2],
            [21, 1],
            [40, 2],
            [100, 2],
            [101, 1],
            [110, 2],
            [111, 2],
            [128, 3]
        ]

        testCases.forEach(([input, expected]) => {
            expect(rule6(input)).toBe(expected)
        })
    })

    test('rule7', () => {
        const testCases = [
            [0, 3],
            [1, 1],
            [2, 2],
            [3, 2],
            [11, 3],
            [12, 3],
            [13, 3],
            [19, 3],
            [20, 3],
            [21, 1],
            [40, 3],
            [100, 3],
            [101, 1],
            [110, 3],
            [111, 3],
            [120, 3],
            [121, 1],
            [122, 2],
            [123, 2],
            [124, 2],
            [125, 3]
        ]

        testCases.forEach(([input, expected]) => {
            expect(rule7(input)).toBe(expected)
        })
    })

    test('rule8', () => {
        const testCases = [
            [0, 3],
            [1, 1],
            [2, 2],
            [3, 2],
            [4, 2],
            [5, 3],
            [11, 3],
            [12, 3],
            [13, 3],
            [19, 3],
            [20, 3],
            [21, 3],
            [40, 3],
            [100, 3],
            [101, 3],
            [110, 3],
            [111, 3],
            [128, 3]
        ]

        testCases.forEach(([input, expected]) => {
            expect(rule8(input)).toBe(expected)
        })
    })

    test('rule9', () => {
        const testCases = [
            [0, 3],
            [1, 1],
            [2, 2],
            [3, 2],
            [11, 3],
            [12, 3],
            [13, 3],
            [19, 3],
            [20, 3],
            [21, 3],
            [40, 3],
            [100, 3],
            [101, 3],
            [120, 3],
            [121, 3],
            [122, 2],
            [123, 2],
            [124, 2],
            [125, 3]
        ]

        testCases.forEach(([input, expected]) => {
            expect(rule9(input)).toBe(expected)
        })
    })

    test('rule10', () => {
        const testCases = [
            [0, 4],
            [1, 1],
            [2, 2],
            [3, 3],
            [11, 4],
            [12, 4],
            [13, 4],
            [19, 4],
            [20, 4],
            [21, 4],
            [40, 4],
            [100, 4],
            [101, 1],
            [102, 2],
            [120, 4],
            [121, 4],
            [122, 4],
            [123, 4],
            [124, 4],
            [125, 4],
            [201, 1],
            [202, 2],
            [203, 3],
            [204, 3],
            [205, 4]
        ]

        testCases.forEach(([input, expected]) => {
            expect(rule10(input)).toBe(expected)
        })
    })

    test('rule11', () => {
        const testCases = [
            [0, 5],
            [1, 1],
            [2, 2],
            [3, 3],
            [4, 3],
            [5, 3],
            [6, 3],
            [7, 4],
            [8, 4],
            [9, 4],
            [10, 4],
            [11, 5],
            [12, 5],
            [21, 5],
            [100, 5],
            [101, 5],
            [120, 5],
            [121, 5],
            [122, 5],
            [123, 5],
            [124, 5],
            [125, 5]
        ]

        testCases.forEach(([input, expected]) => {
            expect(rule11(input)).toBe(expected)
        })
    })

    test('rule12', () => {
        const testCases = [
            [0, 6],
            [1, 1],
            [2, 2],
            [3, 3],
            [11, 4],
            [12, 4],
            [13, 4],
            [19, 4],
            [20, 4],
            [21, 4],
            [40, 4],
            [100, 5],
            [101, 5],
            [102, 5],
            [103, 3],
            [109, 3],
            [110, 3],
            [111, 4],
            [112, 4],
            [120, 4],
            [121, 4],
            [122, 4],
            [123, 4],
            [124, 4],
            [125, 4],
            [200, 5]
        ]

        testCases.forEach(([input, expected]) => {
            expect(rule12(input)).toBe(expected)
        })
    })

    test('rule13', () => {
        const testCases = [
            [0, 2],
            [1, 1],
            [2, 2],
            [3, 2],
            [11, 3],
            [12, 3],
            [13, 3],
            [19, 3],
            [20, 4],
            [21, 4],
            [40, 4],
            [100, 4],
            [101, 2],
            [102, 2],
            [103, 2],
            [109, 2],
            [110, 2],
            [111, 3],
            [112, 3],
            [120, 4],
            [121, 4],
            [122, 4],
            [123, 4],
            [124, 4],
            [125, 4],
            [200, 4],
            [201, 2],
            [202, 2]
        ]

        testCases.forEach(([input, expected]) => {
            expect(rule13(input)).toBe(expected)
        })
    })

    test('rule14', () => {
        const testCases = [
            [0, 3],
            [1, 1],
            [2, 2],
            [3, 3],
            [11, 1],
            [12, 2],
            [13, 3],
            [19, 3],
            [20, 3],
            [21, 1],
            [40, 3],
            [100, 3],
            [101, 1],
            [102, 2],
            [103, 3],
            [109, 3],
            [110, 3],
            [111, 1],
            [112, 2],
            [120, 3],
            [121, 1],
            [122, 2],
            [123, 3],
            [124, 3],
            [125, 3],
            [200, 3]
        ]

        testCases.forEach(([input, expected]) => {
            expect(rule14(input)).toBe(expected)
        })
    })

    test('rule15', () => {
        const testCases = [
            [0, 2],
            [1, 1],
            [2, 2],
            [3, 2],
            [11, 2],
            [12, 2],
            [13, 2],
            [19, 2],
            [20, 2],
            [21, 1],
            [40, 2],
            [100, 2],
            [101, 1],
            [102, 2],
            [103, 2],
            [109, 2],
            [110, 2],
            [111, 2],
            [112, 2],
            [120, 2],
            [121, 1],
            [122, 2],
            [123, 2],
            [124, 2],
            [125, 2],
            [200, 2]
        ]

        testCases.forEach(([input, expected]) => {
            expect(rule15(input)).toBe(expected)
        })
    })
})
