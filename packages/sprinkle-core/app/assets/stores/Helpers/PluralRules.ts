/**
 * Families: Asian (Chinese, Japanese, Korean, Vietnamese), Persian, Turkic/Altaic (Turkish), Thai, Lao
 * 1 - everything: 0, 1, 2, ...
 */
// eslint-disable-next-line @typescript-eslint/no-unused-vars
export const rule0 = (number: number): number => {
    return 1
}

/**
 * Families: Germanic (Danish, Dutch, English, Faroese, Frisian, German, Norwegian, Swedish),
 * Finno-Ugric (Estonian, Finnish, Hungarian), Language isolate (Basque),
 * Latin/Greek (Greek), Semitic (Hebrew), Romanic (Italian, Portuguese, Spanish, Catalan)
 * 1 - 1
 * 2 - everything else: 0, 2, 3, ...
 */
export const rule1 = (number: number): number => {
    return number === 1 ? 1 : 2
}

/**
 * Families: Romanic (French, Brazilian Portuguese)
 * 1 - 0, 1
 * 2 - everything else: 2, 3, ...
 */
export const rule2 = (number: number): number => {
    return number === 0 || number === 1 ? 1 : 2
}

/**
 * Families: Baltic (Latvian)
 * 1 - 0
 * 2 - ends in 1, not 11: 1, 21, ... 101, 121, ...
 * 3 - everything else: 2, 3, ... 10, 11, 12, ... 20, 22, ...
 */
export const rule3 = (number: number): number => {
    return number === 0 ? 1 : number % 10 == 1 && number % 100 != 11 ? 2 : 3
}

/**
 * Families: Celtic (Scottish Gaelic)
 * 1 - is 1 or 11: 1, 11
 * 2 - is 2 or 12: 2, 12
 * 3 - others between 3 and 19: 3, 4, ... 10, 13, ... 18, 19
 * 4 - everything else: 0, 20, 21, ...
 */
export const rule4 = (number: number): number => {
    return number === 1 || number == 11
        ? 1
        : number === 2 || number === 12
          ? 2
          : number >= 3 && number <= 19
            ? 3
            : 4
}

/**
 * Families: Romanic (Romanian)
 * 1 - 1
 * 2 - is 0 or ends in 01-19: 0, 2, 3, ... 19, 101, 102, ... 119, 201, ...
 * 3 - everything else: 20, 21, ...
 */
export const rule5 = (number: number): number => {
    return number === 1 ? 1 : number === 0 || (number % 100 > 0 && number % 100 < 20) ? 2 : 3
}

/**
 * Families: Baltic (Lithuanian)
 * 1 - ends in 1, not 11: 1, 21, 31, ... 101, 121, ...
 * 2 - ends in 0 or ends in 10-20: 0, 10, 11, 12, ... 19, 20, 30, 40, ...
 * 3 - everything else: 2, 3, ... 8, 9, 22, 23, ... 29, 32, 33, ...
 */
export const rule6 = (number: number): number => {
    return number % 10 === 1 && number % 100 !== 11
        ? 1
        : number % 10 < 2 || (number % 100 >= 10 && number % 100 < 20)
          ? 2
          : 3
}

/**
 * Families: Slavic (Croatian, Serbian, Russian, Ukrainian)
 * 1 - ends in 1, not 11: 1, 21, 31, ... 101, 121, ...
 * 2 - ends in 2-4, not 12-14: 2, 3, 4, 22, 23, 24, 32, ...
 * 3 - everything else: 0, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 25, 26, ...
 */
export const rule7 = (number: number): number => {
    return number % 10 === 1 && number % 100 !== 11
        ? 1
        : number % 10 >= 2 && number % 10 <= 4 && (number % 100 < 10 || number % 100 >= 20)
          ? 2
          : 3
}

/**
 * Families: Slavic (Slovak, Czech)
 * 1 - 1
 * 2 - 2, 3, 4
 * 3 - everything else: 0, 5, 6, 7, ...
 */
export const rule8 = (number: number): number => {
    return number === 1 ? 1 : number >= 2 && number <= 4 ? 2 : 3
}

/**
 * Families: Slavic (Polish)
 * 1 - 1
 * 2 - ends in 2-4, not 12-14: 2, 3, 4, 22, 23, 24, 32, ... 104, 122, ...
 * 3 - everything else: 0, 5, 6, ... 11, 12, 13, 14, 15, ... 20, 21, 25, ...
 */
export const rule9 = (number: number): number => {
    return number === 1
        ? 1
        : number % 10 >= 2 && number % 10 <= 4 && (number % 100 < 12 || number % 100 > 14)
          ? 2
          : 3
}

/**
 * Families: Slavic (Slovenian, Sorbian)
 * 1 - ends in 01: 1, 101, 201, ...
 * 2 - ends in 02: 2, 102, 202, ...
 * 3 - ends in 03-04: 3, 4, 103, 104, 203, 204, ...
 * 4 - everything else: 0, 5, 6, 7, 8, 9, 10, 11, ...
 */
export const rule10 = (number: number): number => {
    return number % 100 === 1
        ? 1
        : number % 100 === 2
          ? 2
          : number % 100 === 3 || number % 100 === 4
            ? 3
            : 4
}

/**
 * Families: Celtic (Irish Gaeilge)
 * 1 - 1
 * 2 - 2
 * 3 - is 3-6: 3, 4, 5, 6
 * 4 - is 7-10: 7, 8, 9, 10
 * 5 - everything else: 0, 11, 12, ...
 */
export const rule11 = (number: number): number => {
    return number === 1
        ? 1
        : number === 2
          ? 2
          : number >= 3 && number <= 6
            ? 3
            : number >= 7 && number <= 10
              ? 4
              : 5
}

/**
 * Families: Semitic (Arabic).
 *
 * 1 - 1
 * 2 - 2
 * 3 - ends in 03-10: 3, 4, ... 10, 103, 104, ... 110, 203, 204, ...
 * 4 - ends in 11-99: 11, ... 99, 111, 112, ...
 * 5 - everything else: 100, 101, 102, 200, 201, 202, ...
 * 6 - 0
 */
export const rule12 = (number: number): number => {
    return number === 1
        ? 1
        : number === 2
          ? 2
          : number % 100 >= 3 && number % 100 <= 10
            ? 3
            : number % 100 >= 11
              ? 4
              : number != 0
                ? 5
                : 6
}

/**
 * Families: Semitic (Maltese)
 * 1 - 1
 * 2 - is 0 or ends in 01-10: 0, 2, 3, ... 9, 10, 101, 102, ...
 * 3 - ends in 11-19: 11, 12, ... 18, 19, 111, 112, ...
 * 4 - everything else: 20, 21, ...
 */
export const rule13 = (number: number): number => {
    return number === 1
        ? 1
        : number === 0 || (number % 100 >= 1 && number % 100 < 11)
          ? 2
          : number % 100 > 10 && number % 100 < 20
            ? 3
            : 4
}

/**
 * Families: Slavic (Macedonian)
 * 1 - ends in 1: 1, 11, 21, ...
 * 2 - ends in 2: 2, 12, 22, ...
 * 3 - everything else: 0, 3, 4, ... 10, 13, 14, ... 20, 23, ...
 */
export const rule14 = (number: number): number => {
    return number % 10 === 1 ? 1 : number % 10 === 2 ? 2 : 3
}

/**
 * Families: Icelandic
 * 1 - ends in 1, not 11: 1, 21, 31, ... 101, 121, 131, ...
 * 2 - everything else: 0, 2, 3, ... 10, 11, 12, ... 20, 22, ...
 */
export const rule15 = (number: number): number => {
    return number % 10 === 1 && number % 100 != 11 ? 1 : 2
}

export interface PluralRules {
    [key: number]: (pluralValue: number) => number
}
