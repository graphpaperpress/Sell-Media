export const addToArrayImmutable = (arr, value) => [...arr, value]
export const updateArrayImmutable = (arr, i, value) => Object.assign([...arr], {[i]: value})
export const removeFromArrayImmutable = (arr, value) => arr.filter(i => i !== value)
