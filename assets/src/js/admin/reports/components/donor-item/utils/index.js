export function getBGColor (name) {
    const palette = [
        '#D75A4B',
        '#F49420',
        '#69B868',
        '#556E79',
        '#9EA3A8',
    ]
    return palette[Math.floor(Math.random() * (palette.length))]
}